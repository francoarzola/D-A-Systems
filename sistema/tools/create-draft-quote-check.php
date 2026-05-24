<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

if (!in_array('--confirm-local', $argv, true)) {
    outputError('Esta herramienta solo debe ejecutarse en entorno local o de prueba. Usa --confirm-local para confirmar.');
    exit(1);
}

require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Validation/QuoteDraftValidator.php';
require_once __DIR__ . '/../app/Services/QuoteTotalsCalculator.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuoteTotalsCalculator;
use DAndASystems\Internal\Validation\QuoteDraftValidator;

const DRAFT_CLIENT_NAME = 'Cliente Borrador CLI D&A Systems';
const DRAFT_DESCRIPTION = 'Borrador creado por herramienta CLI controlada';
const DRAFT_LOCK_NAME = 'dasystems_create_draft_quote_check';

$pdo = null;
$lockAcquired = false;
$exitCode = 1;

try {
    $config = DatabaseConfig::fromDefaultPath()->load();

    if (!isAllowedLocalEnvironment($config)) {
        outputError('Entorno no permitido para insertar borradores de prueba. Revise la configuración local.');
        exit(1);
    }

    $connection = new Connection($config);
    $pdo = $connection->pdo();

    if (!acquireDraftLock($pdo)) {
        outputError('No fue posible obtener bloqueo de seguridad para crear el borrador de prueba.');
        exit(1);
    }

    $lockAcquired = true;
    $repository = new QuoteRepository($pdo);

    if ($repository->draftExistsByClientAndDescription(DRAFT_CLIENT_NAME, DRAFT_DESCRIPTION)) {
        outputOk('Ya existe un borrador de prueba creado por CLI. No se insertó uno nuevo.');
        $exitCode = 0;
    } else {
        $draft = buildDraftData();
        $validator = new QuoteDraftValidator();
        $validation = $validator->validateDraft($draft);

        if ($validation['valid'] !== true) {
            outputError('El borrador de prueba no pasó la validación controlada.');
            $exitCode = 1;
        } else {
            $calculator = new QuoteTotalsCalculator();
            $totals = $calculator->calculate($draft['detalles']);
            $quoteId = $repository->createDraft($draft, $totals);

            outputOk("Borrador de cotización creado con ID {$quoteId}.");
            outputOk('Estado: borrador, sin número de cotización.');
            outputOk('Totales calculados desde QuoteTotalsCalculator.');
            $exitCode = 0;
        }
    }
} catch (\Throwable $exception) {
    outputError('No fue posible crear el borrador de prueba.');
    $exitCode = 1;
} finally {
    if ($lockAcquired && $pdo instanceof PDO) {
        releaseDraftLock($pdo);
    }
}

exit($exitCode);

function buildDraftData(): array
{
    $today = new DateTimeImmutable('today');
    $validUntil = $today->modify('+30 days');

    return [
        'form_action' => 'guardar_borrador',
        'nombre_cliente' => DRAFT_CLIENT_NAME,
        'rut_cliente' => '76.111.111-1',
        'nombre_contacto' => 'Paula Méndez',
        'correo_contacto' => 'paula@example.test',
        'telefono_contacto' => '+56 9 1111 1111',
        'descripcion' => DRAFT_DESCRIPTION,
        'fecha_cotizacion' => $today->format('Y-m-d'),
        'valido_hasta' => $validUntil->format('Y-m-d'),
        'condiciones_comerciales' => 'Valores de prueba generados en entorno local',
        'observaciones' => 'Registro creado para validar persistencia de borradores',
        'detalles' => [
            [
                'descripcion' => 'Servicio de soporte técnico mensual',
                'cantidad' => '1',
                'unidad' => 'mes',
                'precio_unitario_neto' => '650000',
                'descuento_monto' => '0',
            ],
            [
                'descripcion' => 'Implementación y configuración inicial',
                'cantidad' => '1',
                'unidad' => 'servicio',
                'precio_unitario_neto' => '550000',
                'descuento_monto' => '0',
            ],
        ],
    ];
}

function isAllowedLocalEnvironment(array $config): bool
{
    $host = strtolower(trim((string) ($config['host'] ?? '')));
    $database = strtolower(trim((string) ($config['database'] ?? '')));
    $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true);
    $isLocalDatabase = $database === 'dasystems_internal_local'
        || str_contains($database, '_local')
        || str_contains($database, '_test')
        || str_contains($database, 'prueba');

    return $isLocalHost && $isLocalDatabase;
}

function acquireDraftLock(PDO $pdo): bool
{
    $statement = $pdo->prepare('SELECT GET_LOCK(:lock_name, 5)');
    $statement->execute(['lock_name' => DRAFT_LOCK_NAME]);

    return (int) $statement->fetchColumn() === 1;
}

function releaseDraftLock(PDO $pdo): void
{
    $statement = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
    $statement->execute(['lock_name' => DRAFT_LOCK_NAME]);
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
