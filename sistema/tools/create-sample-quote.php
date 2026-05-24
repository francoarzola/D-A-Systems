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

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;

const SAMPLE_CLIENT_NAME = 'Cliente de Prueba D&A Systems';
const SAMPLE_DESCRIPTION = 'Cotización de prueba para validar listado interno';
const SAMPLE_LOCK_NAME = 'dasystems_create_sample_quote';

$pdo = null;
$lockAcquired = false;
$exitCode = 1;

try {
    $config = DatabaseConfig::fromDefaultPath()->load();

    if (!isAllowedSampleEnvironment($config)) {
        outputError('Entorno no permitido para insertar datos de prueba. Revise la configuración local.');
        exit(1);
    }

    $connection = new Connection($config);
    $pdo = $connection->pdo();

    if (!acquireSampleLock($pdo)) {
        outputError('No fue posible obtener bloqueo de seguridad para crear datos de prueba.');
        exit(1);
    }

    $lockAcquired = true;
    $pdo->beginTransaction();

    if (sampleQuoteExists($pdo)) {
        $pdo->commit();
        outputOk('Ya existe una cotización de prueba. No se insertó una nueva.');
        $exitCode = 0;
    } else {
        $items = [
            [
                'descripcion' => 'Servicio de soporte técnico mensual',
                'cantidad' => 1.00,
                'unidad' => 'mes',
                'precio_unitario_neto' => 650000.00,
                'descuento_monto' => 0.00,
            ],
            [
                'descripcion' => 'Implementación y configuración inicial',
                'cantidad' => 1.00,
                'unidad' => 'servicio',
                'precio_unitario_neto' => 550000.00,
                'descuento_monto' => 0.00,
            ],
        ];

        $calculatedItems = calculateItems($items);
        $subtotalNet = array_sum(array_column($calculatedItems, 'total_linea_neto'));
        $headerDiscount = 0.00;
        $taxRate = 19.00;
        $taxAmount = round(($subtotalNet - $headerDiscount) * ($taxRate / 100), 2);
        $total = round($subtotalNet - $headerDiscount + $taxAmount, 2);

        $today = new DateTimeImmutable('today');
        $validUntil = $today->modify('+30 days');

        $quoteId = insertSampleQuote(
            $pdo,
            $today,
            $validUntil,
            $subtotalNet,
            $headerDiscount,
            $taxRate,
            $taxAmount,
            $total
        );

        insertSampleDetails($pdo, $quoteId, $calculatedItems);

        $pdo->commit();

        outputOk("Cotización de prueba creada con ID {$quoteId}.");
        outputOk('Estado: borrador, sin número de cotización.');
        $exitCode = 0;
    }
} catch (\Throwable $exception) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    outputError('No fue posible crear la cotización de prueba.');
    $exitCode = 1;
} finally {
    if ($lockAcquired && $pdo instanceof PDO) {
        releaseSampleLock($pdo);
    }
}

exit($exitCode);

function isAllowedSampleEnvironment(array $config): bool
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

function acquireSampleLock(PDO $pdo): bool
{
    $statement = $pdo->prepare('SELECT GET_LOCK(:lock_name, 5)');
    $statement->execute(['lock_name' => SAMPLE_LOCK_NAME]);

    return (int) $statement->fetchColumn() === 1;
}

function releaseSampleLock(PDO $pdo): void
{
    $statement = $pdo->prepare('SELECT RELEASE_LOCK(:lock_name)');
    $statement->execute(['lock_name' => SAMPLE_LOCK_NAME]);
}

function sampleQuoteExists(PDO $pdo): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM cotizaciones
         WHERE nombre_cliente = :nombre_cliente
           AND estado = :estado
           AND descripcion = :descripcion'
    );
    $statement->execute([
        'nombre_cliente' => SAMPLE_CLIENT_NAME,
        'estado' => 'borrador',
        'descripcion' => SAMPLE_DESCRIPTION,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function calculateItems(array $items): array
{
    $calculatedItems = [];

    foreach ($items as $index => $item) {
        $quantity = (float) $item['cantidad'];
        $unitPriceNet = (float) $item['precio_unitario_neto'];
        $discountAmount = (float) $item['descuento_monto'];
        $lineSubtotalNet = round($quantity * $unitPriceNet, 2);
        $lineTotalNet = round($lineSubtotalNet - $discountAmount, 2);

        $calculatedItems[] = [
            'numero_linea' => $index + 1,
            'descripcion' => (string) $item['descripcion'],
            'cantidad' => $quantity,
            'unidad' => (string) $item['unidad'],
            'precio_unitario_neto' => $unitPriceNet,
            'descuento_monto' => $discountAmount,
            'subtotal_linea_neto' => $lineSubtotalNet,
            'total_linea_neto' => $lineTotalNet,
        ];
    }

    return $calculatedItems;
}

function insertSampleQuote(
    PDO $pdo,
    DateTimeImmutable $quoteDate,
    DateTimeImmutable $validUntil,
    float $subtotalNet,
    float $headerDiscount,
    float $taxRate,
    float $taxAmount,
    float $total
): int {
    $statement = $pdo->prepare(
        'INSERT INTO cotizaciones (
            numero_cotizacion,
            fecha_cotizacion,
            valido_hasta,
            nombre_cliente,
            rut_cliente,
            nombre_contacto,
            correo_contacto,
            telefono_contacto,
            descripcion,
            estado,
            subtotal_neto,
            descuento_monto,
            iva_porcentaje,
            iva_monto,
            total,
            condiciones_comerciales,
            observaciones,
            creado_por
        ) VALUES (
            NULL,
            :fecha_cotizacion,
            :valido_hasta,
            :nombre_cliente,
            :rut_cliente,
            :nombre_contacto,
            :correo_contacto,
            :telefono_contacto,
            :descripcion,
            :estado,
            :subtotal_neto,
            :descuento_monto,
            :iva_porcentaje,
            :iva_monto,
            :total,
            :condiciones_comerciales,
            :observaciones,
            NULL
        )'
    );
    $statement->execute([
        'fecha_cotizacion' => $quoteDate->format('Y-m-d'),
        'valido_hasta' => $validUntil->format('Y-m-d'),
        'nombre_cliente' => SAMPLE_CLIENT_NAME,
        'rut_cliente' => '76.000.000-0',
        'nombre_contacto' => 'Andrea Pérez',
        'correo_contacto' => 'andrea@example.test',
        'telefono_contacto' => '+56 9 0000 0000',
        'descripcion' => SAMPLE_DESCRIPTION,
        'estado' => 'borrador',
        'subtotal_neto' => formatDecimal($subtotalNet),
        'descuento_monto' => formatDecimal($headerDiscount),
        'iva_porcentaje' => formatDecimal($taxRate),
        'iva_monto' => formatDecimal($taxAmount),
        'total' => formatDecimal($total),
        'condiciones_comerciales' => 'Valores de prueba, no usar comercialmente',
        'observaciones' => 'Registro generado por herramienta CLI local',
    ]);

    return (int) $pdo->lastInsertId();
}

function insertSampleDetails(PDO $pdo, int $quoteId, array $items): void
{
    $statement = $pdo->prepare(
        'INSERT INTO cotizacion_detalles (
            cotizacion_id,
            numero_linea,
            descripcion,
            cantidad,
            unidad,
            precio_unitario_neto,
            descuento_monto,
            subtotal_linea_neto,
            total_linea_neto
        ) VALUES (
            :cotizacion_id,
            :numero_linea,
            :descripcion,
            :cantidad,
            :unidad,
            :precio_unitario_neto,
            :descuento_monto,
            :subtotal_linea_neto,
            :total_linea_neto
        )'
    );

    foreach ($items as $item) {
        $statement->execute([
            'cotizacion_id' => $quoteId,
            'numero_linea' => $item['numero_linea'],
            'descripcion' => $item['descripcion'],
            'cantidad' => formatDecimal((float) $item['cantidad']),
            'unidad' => $item['unidad'],
            'precio_unitario_neto' => formatDecimal((float) $item['precio_unitario_neto']),
            'descuento_monto' => formatDecimal((float) $item['descuento_monto']),
            'subtotal_linea_neto' => formatDecimal((float) $item['subtotal_linea_neto']),
            'total_linea_neto' => formatDecimal((float) $item['total_linea_neto']),
        ]);
    }
}

function formatDecimal(float $value): string
{
    return number_format($value, 2, '.', '');
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
