<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$sessionManagerPath = __DIR__ . '/../app/Core/SessionManager.php';
$contents = readFileOrFail($sessionManagerPath, 'SessionManager.php');

$requiredFragments = [
    'resolveCookiePath',
    'SCRIPT_NAME',
    'session_name($this->name)',
    "DA_SYSTEMS_INTERNAL_SESSION",
    "'httponly' => \$this->httponly",
    "private bool \$httponly = true",
    "private string \$sameSite = 'Lax'",
    "'samesite' => \$this->sameSite",
    'session_regenerate_id',
    'session_destroy',
    'session_set_cookie_params',
];

foreach ($requiredFragments as $fragment) {
    assertContains($contents, $fragment, "SessionManager.php no contiene {$fragment}");
}

if (str_contains($contents, "private string \$path = '/sistema'")
    || str_contains($contents, 'private string $path = "/sistema"')) {
    outputError('SessionManager.php mantiene la ruta fija antigua /sistema.');
    exit(1);
}

if (substr_count($contents, 'resolveCookiePath()') < 2) {
    outputError('start() y destroy() deben usar resolveCookiePath().');
    exit(1);
}

if (!str_contains($contents, "private string \$fallbackPath = '/'")) {
    outputError('SessionManager.php debe mantener fallback seguro en /.');
    exit(1);
}

outputOk('El contrato de ruta dinámica de cookie de sesión está completo.');
outputOk('No se inició sesión real ni se modificaron archivos.');
exit(0);

function readFileOrFail(string $path, string $label): string
{
    if (!is_file($path)) {
        outputError("No existe {$label}.");
        exit(1);
    }

    $contents = file_get_contents($path);

    if (!is_string($contents) || $contents === '') {
        outputError("No fue posible leer {$label}.");
        exit(1);
    }

    return $contents;
}

function assertContains(string $contents, string $fragment, string $message): void
{
    if (!str_contains($contents, $fragment)) {
        outputError($message);
        exit(1);
    }
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
