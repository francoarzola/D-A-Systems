<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Validation/QuoteDraftValidator.php';

use DAndASystems\Internal\Validation\QuoteDraftValidator;

$validator = new QuoteDraftValidator();

$cases = [
    'válido mínimo' => [
        'data' => [
            'form_action' => 'guardar_borrador',
            'fecha_cotizacion' => '2026-05-24',
            'nombre_cliente' => 'Cliente de Prueba D&A Systems',
        ],
        'expected_valid' => true,
    ],
    'válido con detalles' => [
        'data' => [
            'form_action' => 'guardar_borrador',
            'fecha_cotizacion' => '2026-05-24',
            'valido_hasta' => '2026-06-23',
            'nombre_cliente' => 'Cliente de Prueba D&A Systems',
            'rut_cliente' => '76.000.000-0',
            'nombre_contacto' => 'Andrea Pérez',
            'correo_contacto' => 'andrea@example.test',
            'telefono_contacto' => '+56 9 0000 0000',
            'descripcion' => 'Cotización de prueba para validar borrador',
            'condiciones_comerciales' => 'Valores de prueba, no usar comercialmente',
            'observaciones' => 'Caso válido de herramienta CLI',
            'detalles' => [
                [
                    'descripcion' => 'Servicio de soporte técnico mensual',
                    'cantidad' => '1',
                    'unidad' => 'mes',
                    'precio_unitario_neto' => '650000',
                    'descuento_monto' => '0',
                ],
            ],
        ],
        'expected_valid' => true,
    ],
    'inválido sin nombre de cliente' => [
        'data' => [
            'form_action' => 'guardar_borrador',
            'fecha_cotizacion' => '2026-05-24',
            'nombre_cliente' => '',
        ],
        'expected_valid' => false,
    ],
    'inválido con correo incorrecto' => [
        'data' => [
            'form_action' => 'guardar_borrador',
            'fecha_cotizacion' => '2026-05-24',
            'nombre_cliente' => 'Cliente de Prueba D&A Systems',
            'correo_contacto' => 'correo-invalido',
        ],
        'expected_valid' => false,
    ],
    'inválido con descuento mayor al subtotal' => [
        'data' => [
            'form_action' => 'guardar_borrador',
            'fecha_cotizacion' => '2026-05-24',
            'nombre_cliente' => 'Cliente de Prueba D&A Systems',
            'detalles' => [
                [
                    'descripcion' => 'Servicio de prueba',
                    'cantidad' => '1',
                    'unidad' => 'servicio',
                    'precio_unitario_neto' => '100',
                    'descuento_monto' => '101',
                ],
            ],
        ],
        'expected_valid' => false,
    ],
    'válido con decimal sensible' => [
        'data' => [
            'form_action' => 'guardar_borrador',
            'fecha_cotizacion' => '2026-05-24',
            'nombre_cliente' => 'Cliente de Prueba D&A Systems',
            'detalles' => [
                [
                    'descripcion' => 'Validación decimal',
                    'cantidad' => '0.1',
                    'unidad' => 'servicio',
                    'precio_unitario_neto' => '0.7',
                    'descuento_monto' => '0.07',
                ],
            ],
        ],
        'expected_valid' => true,
    ],
];

foreach ($cases as $caseName => $case) {
    $result = $validator->validateDraft($case['data']);

    if ($result['valid'] !== $case['expected_valid']) {
        outputError("Falló el caso: {$caseName}.");
        exit(1);
    }

    outputOk("Caso {$caseName} validado.");
}

outputOk('QuoteDraftValidator disponible.');
exit(0);

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
