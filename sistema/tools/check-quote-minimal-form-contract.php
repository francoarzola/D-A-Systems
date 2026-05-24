<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

$formPath = __DIR__ . '/../public/cotizaciones.php';

if (!is_file($formPath)) {
    outputError('No existe sistema/public/cotizaciones.php.');
    exit(1);
}

$contents = file_get_contents($formPath);

if (!is_string($contents) || $contents === '') {
    outputError('No fue posible leer cotizaciones.php.');
    exit(1);
}

$requiredFragments = [
    'CsrfToken',
    'FlashMessage',
    "inputField('quote_draft')",
    'method="post"',
    'action="cotizaciones-guardar.php"',
    'name="form_action"',
    'value="guardar_borrador"',
    'name="nombre_cliente"',
    'name="fecha_cotizacion"',
    'name="detalles[0][descripcion]"',
    'name="detalles[0][cantidad]"',
    'name="detalles[0][precio_unitario_neto]"',
];

foreach ($requiredFragments as $fragment) {
    if (!str_contains($contents, $fragment)) {
        outputError("Falta referencia esperada en el formulario: {$fragment}");
        exit(1);
    }
}

$forbiddenInputNames = [
    'numero_cotizacion',
    'estado',
    'subtotal_neto',
    'iva_monto',
    'total',
];

foreach ($forbiddenInputNames as $name) {
    if (str_contains($contents, 'name="' . $name . '"')
        || str_contains($contents, "name='{$name}'")) {
        outputError("El formulario contiene un campo no permitido: {$name}");
        exit(1);
    }
}

outputOk('El contrato del formulario mínimo de cotización está completo.');
outputOk('No se ejecutó POST real ni se usó base de datos.');
exit(0);

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
