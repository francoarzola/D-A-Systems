<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$companyConfigPath = $rootPath . '/config/company.php';
$companyProfilePath = $rootPath . '/app/Support/CompanyProfile.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];

if (!is_file($companyConfigPath)) {
    $errors[] = 'No existe sistema/config/company.php.';
} else {
    $companyConfig = require $companyConfigPath;

    if (!is_array($companyConfig)) {
        $errors[] = 'sistema/config/company.php no retorna un array.';
    }
}

$companyProfile = readFileContents($companyProfilePath, $errors);
$printView = readFileContents($printViewPath, $errors);

assertContains($companyProfile, [
    'all',
    'commercialName',
    'legalName',
    'taxId',
    'businessActivity',
    'defaultFooterNote',
    'quoteValidityNote',
], 'CompanyProfile', $errors);

assertContains($printView, [
    'CompanyProfile',
    'commercialName',
    'taxId',
    'businessActivity',
    'defaultFooterNote',
    'quoteValidityNote',
], 'cotizacion-imprimir.php', $errors);

assertDoesNotContain($companyProfile . "\n" . $printView, [
    'INSERT',
    'UPDATE',
    'DELETE',
    '$_POST',
    'PDF',
    'AJAX',
    'API JSON',
], $errors);

if (preg_match('/(?<![A-Za-z0-9_])mail\s*\(/', $companyProfile . "\n" . $printView) === 1) {
    $errors[] = 'Se encontró llamada no permitida a mail().';
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Configuración comercial y vista imprimible cumplen el contrato esperado.\n";
exit(0);

function readFileContents(string $path, array &$errors): string
{
    if (!is_file($path)) {
        $errors[] = 'No existe ' . displayPath($path) . '.';

        return '';
    }

    $contents = file_get_contents($path);

    if (!is_string($contents)) {
        $errors[] = 'No fue posible leer ' . displayPath($path) . '.';

        return '';
    }

    return $contents;
}

function assertContains(string $contents, array $needles, string $label, array &$errors): void
{
    foreach ($needles as $needle) {
        if (!str_contains($contents, $needle)) {
            $errors[] = $label . ' no contiene referencia requerida: ' . $needle . '.';
        }
    }
}

function assertDoesNotContain(string $contents, array $forbiddenNeedles, array &$errors): void
{
    foreach ($forbiddenNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            $errors[] = 'Se encontró fragmento no permitido: ' . $needle . '.';
        }
    }
}

function displayPath(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $marker = '/sistema/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + 1);
}
