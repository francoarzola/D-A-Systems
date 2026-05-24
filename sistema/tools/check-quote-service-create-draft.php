<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Validation/QuoteDraftValidator.php';
require_once __DIR__ . '/../app/Services/QuoteTotalsCalculator.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Services\QuoteTotalsCalculator;
use DAndASystems\Internal\Validation\QuoteDraftValidator;

try {
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    createInMemorySchema($pdo);

    $repository = new QuoteRepository($pdo);
    $service = new QuoteService($repository, new QuoteDraftValidator(), new QuoteTotalsCalculator());
    $draft = buildDraftData();
    $result = $service->createDraft($draft);

    if ($result['success'] !== true || (int) $result['quote_id'] <= 0) {
        outputError('El servicio no creó el borrador válido.');
        exit(1);
    }

    assertQuoteWasCreated($pdo, (int) $result['quote_id']);
    assertDraftDetailsWereCreated($pdo, (int) $result['quote_id']);
    assertTotals($result['totals'] ?? []);
    $quoteCountBeforeInvalidCase = countQuotes($pdo);

    $invalidResult = $service->createDraft([
        'form_action' => 'guardar_borrador',
        'fecha_cotizacion' => '2026-05-24',
        'nombre_cliente' => '',
    ]);

    if ($invalidResult['success'] !== false || $invalidResult['errors'] === []) {
        outputError('El servicio no rechazó el borrador inválido.');
        exit(1);
    }

    if (countQuotes($pdo) !== $quoteCountBeforeInvalidCase) {
        outputError('El servicio persistió un borrador inválido.');
        exit(1);
    }

    outputOk('QuoteService creó un borrador válido usando validador, calculadora y repositorio.');
    outputOk('QuoteService rechazó un borrador inválido sin persistirlo.');
    exit(0);
} catch (\Throwable $exception) {
    outputError('No fue posible verificar la creación de borradores en QuoteService.');
    exit(1);
}

function createInMemorySchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE cotizaciones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            numero_cotizacion TEXT NULL,
            fecha_cotizacion TEXT NOT NULL,
            valido_hasta TEXT NULL,
            nombre_cliente TEXT NULL,
            rut_cliente TEXT NULL,
            nombre_contacto TEXT NULL,
            correo_contacto TEXT NULL,
            telefono_contacto TEXT NULL,
            descripcion TEXT NULL,
            estado TEXT NOT NULL,
            subtotal_neto NUMERIC NOT NULL DEFAULT 0,
            descuento_monto NUMERIC NOT NULL DEFAULT 0,
            iva_porcentaje NUMERIC NOT NULL DEFAULT 19,
            iva_monto NUMERIC NOT NULL DEFAULT 0,
            total NUMERIC NOT NULL DEFAULT 0,
            condiciones_comerciales TEXT NULL,
            observaciones TEXT NULL,
            creado_por INTEGER NULL,
            creado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );

    $pdo->exec(
        'CREATE TABLE cotizacion_detalles (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cotizacion_id INTEGER NOT NULL,
            numero_linea INTEGER NOT NULL,
            descripcion TEXT NULL,
            cantidad NUMERIC NOT NULL DEFAULT 1,
            unidad TEXT NULL,
            precio_unitario_neto NUMERIC NOT NULL DEFAULT 0,
            descuento_monto NUMERIC NOT NULL DEFAULT 0,
            subtotal_linea_neto NUMERIC NOT NULL DEFAULT 0,
            total_linea_neto NUMERIC NOT NULL DEFAULT 0,
            creado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
    );
}

function buildDraftData(): array
{
    return [
        'form_action' => 'guardar_borrador',
        'nombre_cliente' => 'Cliente Servicio D&A Systems',
        'rut_cliente' => '76.222.222-2',
        'nombre_contacto' => 'Camila Rojas',
        'correo_contacto' => 'camila@example.test',
        'telefono_contacto' => '+56 9 2222 2222',
        'descripcion' => 'Borrador creado para validar QuoteService',
        'fecha_cotizacion' => '2026-05-24',
        'valido_hasta' => '2026-06-23',
        'condiciones_comerciales' => 'Valores de prueba en memoria',
        'observaciones' => 'Registro temporal de verificación CLI',
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

function assertQuoteWasCreated(PDO $pdo, int $quoteId): void
{
    $statement = $pdo->prepare(
        'SELECT numero_cotizacion, estado, subtotal_neto, iva_monto, total
         FROM cotizaciones
         WHERE id = :id'
    );
    $statement->execute(['id' => $quoteId]);
    $quote = $statement->fetch();

    if (!is_array($quote) || $quote['numero_cotizacion'] !== null || $quote['estado'] !== 'borrador') {
        throw new RuntimeException('La cabecera creada no cumple las reglas de borrador.');
    }

    if (!sameMoney((float) $quote['subtotal_neto'], 1200000.00)
        || !sameMoney((float) $quote['iva_monto'], 228000.00)
        || !sameMoney((float) $quote['total'], 1428000.00)) {
        throw new RuntimeException('Los totales persistidos no son los esperados.');
    }
}

function assertDraftDetailsWereCreated(PDO $pdo, int $quoteId): void
{
    $statement = $pdo->prepare(
        'SELECT COUNT(*)
         FROM cotizacion_detalles
         WHERE cotizacion_id = :quote_id'
    );
    $statement->execute(['quote_id' => $quoteId]);

    if ((int) $statement->fetchColumn() !== 2) {
        throw new RuntimeException('No se crearon los detalles esperados.');
    }
}

function assertTotals(array $totals): void
{
    if (!sameMoney((float) ($totals['subtotal_neto'] ?? 0), 1200000.00)
        || !sameMoney((float) ($totals['iva_monto'] ?? 0), 228000.00)
        || !sameMoney((float) ($totals['total'] ?? 0), 1428000.00)) {
        throw new RuntimeException('Los totales devueltos por el servicio no son los esperados.');
    }
}

function countQuotes(PDO $pdo): int
{
    $statement = $pdo->query('SELECT COUNT(*) FROM cotizaciones');

    if ($statement === false) {
        return 0;
    }

    return (int) $statement->fetchColumn();
}

function sameMoney(float $actual, float $expected): bool
{
    return abs($actual - $expected) < 0.001;
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
