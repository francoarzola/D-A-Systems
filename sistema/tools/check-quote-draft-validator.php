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

$validDraft = [
    'form_action' => 'guardar_borrador',
    'quote_date' => '2026-05-24',
    'valid_until' => '2026-06-23',
    'client_name' => 'Cliente de Prueba D&A Systems',
    'client_rut' => '76.000.000-0',
    'contact_name' => 'Andrea Pérez',
    'contact_email' => 'andrea@example.test',
    'contact_phone' => '+56 9 0000 0000',
    'description' => 'Cotización de prueba para validar borrador',
    'commercial_terms' => 'Valores de prueba, no usar comercialmente',
    'notes' => 'Caso válido de herramienta CLI',
    'items' => [
        [
            'description' => 'Servicio de soporte técnico mensual',
            'quantity' => '1',
            'unit' => 'mes',
            'unit_price_net' => '650000',
            'discount_amount' => '0',
        ],
    ],
];

$invalidDraft = [
    'form_action' => 'guardar_borrador',
    'quote_date' => '',
    'valid_until' => '2026-01-01',
    'client_name' => '',
    'contact_email' => 'correo-invalido',
    'items' => [
        [
            'description' => '',
            'quantity' => '0',
            'unit' => '',
            'unit_price_net' => '-10',
            'discount_amount' => '0',
        ],
    ],
];

$validResult = $validator->validateDraft($validDraft);
$invalidResult = $validator->validateDraft($invalidDraft);

if ($validResult['valid'] !== true) {
    outputError('El caso válido fue rechazado.');
    exit(1);
}

if ($invalidResult['valid'] !== false || $invalidResult['errors'] === []) {
    outputError('El caso inválido no generó errores.');
    exit(1);
}

outputOk('QuoteDraftValidator disponible.');
outputOk('Caso válido aceptado.');
outputOk('Caso inválido rechazado con errores controlados.');
exit(0);

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
