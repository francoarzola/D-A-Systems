<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Services/QuoteTotalsCalculator.php';

use DAndASystems\Internal\Services\QuoteTotalsCalculator;

$calculator = new QuoteTotalsCalculator();

$cases = [
    'cotización de prueba' => [
        'details' => [
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
        'header_discount' => 0.00,
        'tax_rate' => 19.00,
        'expected' => [
            'details_count' => 2,
            'subtotal_neto' => 1200000.00,
            'iva_monto' => 228000.00,
            'total' => 1428000.00,
        ],
    ],
    'descuento de cabecera' => [
        'details' => [
            [
                'descripcion' => 'Servicio de prueba',
                'cantidad' => '2',
                'unidad' => 'hora',
                'precio_unitario_neto' => '50000',
                'descuento_monto' => '0',
            ],
        ],
        'header_discount' => 10000.00,
        'tax_rate' => 19.00,
        'expected' => [
            'details_count' => 1,
            'subtotal_neto' => 100000.00,
            'iva_monto' => 17100.00,
            'total' => 107100.00,
        ],
    ],
    'líneas vacías ignoradas' => [
        'details' => [
            [],
            [
                'descripcion' => '',
                'cantidad' => '',
                'unidad' => '',
                'precio_unitario_neto' => '',
                'descuento_monto' => '',
            ],
        ],
        'header_discount' => 0.00,
        'tax_rate' => 19.00,
        'expected' => [
            'details_count' => 0,
            'subtotal_neto' => 0.00,
            'iva_monto' => 0.00,
            'total' => 0.00,
        ],
    ],
    'decimal sensible' => [
        'details' => [
            [
                'descripcion' => 'Validación decimal',
                'cantidad' => '0.1',
                'unidad' => 'servicio',
                'precio_unitario_neto' => '0.7',
                'descuento_monto' => '0.07',
            ],
        ],
        'header_discount' => 0.00,
        'tax_rate' => 19.00,
        'expected' => [
            'details_count' => 1,
            'subtotal_neto' => 0.00,
            'iva_monto' => 0.00,
            'total' => 0.00,
        ],
    ],
];

foreach ($cases as $caseName => $case) {
    $result = $calculator->calculate($case['details'], $case['header_discount'], $case['tax_rate']);

    if (count($result['details']) !== $case['expected']['details_count']) {
        outputError("Falló el caso {$caseName}: cantidad de detalles inesperada.");
        exit(1);
    }

    foreach (['subtotal_neto', 'iva_monto', 'total'] as $field) {
        if (!sameMoney($result[$field], $case['expected'][$field])) {
            outputError("Falló el caso {$caseName}: {$field} inesperado.");
            exit(1);
        }
    }

    outputOk("Caso {$caseName} calculado correctamente.");
}

outputOk('QuoteTotalsCalculator disponible.');
exit(0);

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
