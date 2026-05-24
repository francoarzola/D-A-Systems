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

const EXPECTED_TABLES = [
    'cotizaciones',
    'cotizacion_detalles',
    'cotizacion_correlativos',
];

const EXPECTED_COLUMNS = [
    'cotizaciones' => [
        'id',
        'numero_cotizacion',
        'fecha_cotizacion',
        'valido_hasta',
        'nombre_cliente',
        'rut_cliente',
        'nombre_contacto',
        'correo_contacto',
        'telefono_contacto',
        'descripcion',
        'estado',
        'subtotal_neto',
        'descuento_monto',
        'iva_porcentaje',
        'iva_monto',
        'total',
        'condiciones_comerciales',
        'observaciones',
        'creado_por',
        'creado_en',
        'actualizado_en',
    ],
    'cotizacion_detalles' => [
        'id',
        'cotizacion_id',
        'numero_linea',
        'descripcion',
        'cantidad',
        'unidad',
        'precio_unitario_neto',
        'descuento_monto',
        'subtotal_linea_neto',
        'total_linea_neto',
        'creado_en',
        'actualizado_en',
    ],
    'cotizacion_correlativos' => [
        'id',
        'tipo_documento',
        'anio',
        'ultimo_numero',
        'creado_en',
        'actualizado_en',
    ],
];

const EXPECTED_INDEXES = [
    'cotizaciones' => [
        'uq_cotizaciones_numero_cotizacion',
        'idx_cotizaciones_estado',
        'idx_cotizaciones_fecha_cotizacion',
        'idx_cotizaciones_nombre_cliente',
        'idx_cotizaciones_creado_por',
    ],
    'cotizacion_detalles' => [
        'uq_cotizacion_detalles_cotizacion_linea',
    ],
    'cotizacion_correlativos' => [
        'uq_cotizacion_correlativos_tipo_anio',
    ],
];

const EXPECTED_CHECK = 'chk_cotizaciones_numero_estado';

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $pdo = $connection->pdo();

    $hasCriticalError = false;

    foreach (EXPECTED_TABLES as $tableName) {
        if (tableExists($pdo, $tableName)) {
            outputOk("Tabla {$tableName} existe.");
            continue;
        }

        outputError("Falta la tabla {$tableName}.");
        $hasCriticalError = true;
    }

    foreach (EXPECTED_COLUMNS as $tableName => $columns) {
        foreach ($columns as $columnName) {
            if (columnExists($pdo, $tableName, $columnName)) {
                outputOk("Columna {$tableName}.{$columnName} existe.");
                continue;
            }

            outputError("Falta la columna {$tableName}.{$columnName}.");
            $hasCriticalError = true;
        }
    }

    foreach (EXPECTED_INDEXES as $tableName => $indexes) {
        foreach ($indexes as $indexName) {
            if (indexExists($pdo, $tableName, $indexName)) {
                outputOk("Indice {$indexName} existe.");
                continue;
            }

            outputError("Falta el indice {$indexName}.");
            $hasCriticalError = true;
        }
    }

    if (foreignKeyExists($pdo)) {
        outputOk('FK cotizacion_detalles.cotizacion_id -> cotizaciones.id existe.');
    } else {
        outputError('Falta la FK cotizacion_detalles.cotizacion_id -> cotizaciones.id.');
        $hasCriticalError = true;
    }

    $checkStatus = checkConstraintStatus($pdo);

    if ($checkStatus === true) {
        outputOk('CHECK chk_cotizaciones_numero_estado existe.');
    } elseif ($checkStatus === false) {
        outputWarning('No se pudo confirmar el CHECK chk_cotizaciones_numero_estado. Revisar compatibilidad MySQL/MariaDB.');
    } else {
        outputWarning('La consulta de CHECK no esta disponible en esta version. Validar tambien en backend futuro.');
    }

    if ($hasCriticalError) {
        outputError('Verificacion finalizada con errores criticos.');
        exit(1);
    }

    outputOk('Verificacion finalizada sin errores criticos.');
    exit(0);
} catch (\Throwable $exception) {
    outputError('No fue posible verificar la estructura de cotizaciones.');
    exit(1);
}

function tableExists(\PDO $pdo, string $tableName): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.tables
         WHERE table_schema = DATABASE()
           AND table_name = :table_name'
    );
    $statement->execute(['table_name' => $tableName]);

    return (int) $statement->fetchColumn() > 0;
}

function columnExists(\PDO $pdo, string $tableName, string $columnName): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.columns
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND column_name = :column_name'
    );
    $statement->execute([
        'table_name' => $tableName,
        'column_name' => $columnName,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function indexExists(\PDO $pdo, string $tableName, string $indexName): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.statistics
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND index_name = :index_name'
    );
    $statement->execute([
        'table_name' => $tableName,
        'index_name' => $indexName,
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function foreignKeyExists(\PDO $pdo): bool
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM information_schema.key_column_usage
         WHERE table_schema = DATABASE()
           AND table_name = :table_name
           AND column_name = :column_name
           AND referenced_table_name = :referenced_table_name
           AND referenced_column_name = :referenced_column_name'
    );
    $statement->execute([
        'table_name' => 'cotizacion_detalles',
        'column_name' => 'cotizacion_id',
        'referenced_table_name' => 'cotizaciones',
        'referenced_column_name' => 'id',
    ]);

    return (int) $statement->fetchColumn() > 0;
}

function checkConstraintStatus(\PDO $pdo): ?bool
{
    try {
        $statement = $pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.table_constraints
             WHERE constraint_schema = DATABASE()
               AND table_name = :table_name
               AND constraint_name = :constraint_name
               AND constraint_type = :constraint_type'
        );
        $statement->execute([
            'table_name' => 'cotizaciones',
            'constraint_name' => EXPECTED_CHECK,
            'constraint_type' => 'CHECK',
        ]);

        return (int) $statement->fetchColumn() > 0;
    } catch (\Throwable $exception) {
        return null;
    }
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputWarning(string $message): void
{
    echo '[WARNING] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
