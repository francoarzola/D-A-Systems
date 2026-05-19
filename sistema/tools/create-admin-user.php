<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;

function fail(string $message): void
{
    echo $message . PHP_EOL;
    exit(1);
}

function readPasswordHidden(string $prompt): string
{
    if (PHP_OS_FAMILY === 'Windows') {
        $script = <<<'POWERSHELL'
$promptText = $args[0]
$securePassword = Read-Host -Prompt $promptText -AsSecureString
$pointer = [Runtime.InteropServices.Marshal]::SecureStringToBSTR($securePassword)

try {
    [Runtime.InteropServices.Marshal]::PtrToStringBSTR($pointer)
}
finally {
    [Runtime.InteropServices.Marshal]::ZeroFreeBSTR($pointer)
}
POWERSHELL;

        $temporaryFile = tempnam(sys_get_temp_dir(), 'da_password_');

        if ($temporaryFile === false) {
            fail('ERROR: no fue posible solicitar la contraseña de forma segura.');
        }

        $temporaryScript = $temporaryFile . '.ps1';
        file_put_contents($temporaryScript, $script);
        @unlink($temporaryFile);

        $command = 'powershell -NoProfile -ExecutionPolicy Bypass -File '
            . escapeshellarg($temporaryScript)
            . ' '
            . escapeshellarg($prompt);

        $output = shell_exec($command);
        @unlink($temporaryScript);

        return rtrim((string) $output, "\r\n");
    }

    fwrite(STDOUT, $prompt . ': ');
    shell_exec('stty -echo');
    $password = fgets(STDIN);
    shell_exec('stty echo');
    fwrite(STDOUT, PHP_EOL);

    return rtrim((string) $password, "\r\n");
}

function validateName(string $name): void
{
    $length = strlen($name);

    if ($length < 2 || $length > 120) {
        fail('ERROR: el nombre debe tener entre 2 y 120 caracteres.');
    }
}

function validateEmail(string $email): void
{
    if (strlen($email) > 190 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        fail('ERROR: el correo no tiene un formato válido.');
    }
}

function validatePassword(string $password): void
{
    if (strlen($password) < 12) {
        fail('ERROR: la contraseña debe tener al menos 12 caracteres.');
    }

    if (!preg_match('/[a-z]/', $password)) {
        fail('ERROR: la contraseña debe incluir al menos una letra minúscula.');
    }

    if (!preg_match('/[A-Z]/', $password)) {
        fail('ERROR: la contraseña debe incluir al menos una letra mayúscula.');
    }

    if (!preg_match('/[0-9]/', $password)) {
        fail('ERROR: la contraseña debe incluir al menos un número.');
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        fail('ERROR: la contraseña debe incluir al menos un símbolo.');
    }
}

if ($argc !== 3) {
    fail('Uso: php sistema/tools/create-admin-user.php "Nombre Admin" "correo@dominio.cl"');
}

$name = trim((string) $argv[1]);
$email = strtolower(trim((string) $argv[2]));

validateName($name);
validateEmail($email);

$password = readPasswordHidden('Password');
$passwordConfirmation = readPasswordHidden('Confirmar password');

if ($password !== $passwordConfirmation) {
    fail('ERROR: las contraseñas no coinciden.');
}

validatePassword($password);

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $pdo = $connection->pdo();

    $existingUserStatement = $pdo->prepare(
        'SELECT id FROM users WHERE email = :email LIMIT 1'
    );

    $existingUserStatement->execute([
        'email' => $email,
    ]);

    if ($existingUserStatement->fetchColumn() !== false) {
        fail('ERROR: ya existe un usuario con ese correo.');
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $insertStatement = $pdo->prepare(
        "INSERT INTO users (name, email, password_hash, role, active)
         VALUES (:name, :email, :password_hash, 'admin', 1)"
    );

    $insertStatement->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => $passwordHash,
    ]);

    echo 'OK: usuario administrador creado correctamente.' . PHP_EOL;
    exit(0);
} catch (Throwable $exception) {
    echo 'ERROR: no fue posible crear el usuario administrador.' . PHP_EOL;
    exit(1);
}