<?php
/**
 * Verifica la integridad del inventario de imágenes públicas y la presencia del documento de diagnóstico.
 * Uso: php sistema/tools/check-public-site-image-audit-contract.php
 */

$root = realpath(__DIR__ . '/../../');
if ($root === false) {
    fwrite(STDERR, "No se pudo resolver el directorio raíz del proyecto.\n");
    exit(1);
}

$indexFile = $root . '/index.html';
$docFile = $root . '/docs/sistema-interno/94-diagnostico-imagenes-sitio-publico.md';

$errors = [];

if (!is_file($indexFile)) {
    $errors[] = "Archivo no encontrado: index.html";
}

if (!is_file($docFile)) {
    $errors[] = "Documento de diagnóstico no encontrado: docs/sistema-interno/94-diagnostico-imagenes-sitio-publico.md";
}

$contents = '';
if (empty($errors)) {
    $contents = file_get_contents($indexFile);
    if ($contents === false) {
        $errors[] = "No se pudo leer index.html.";
    }
}

$expectedMarkers = [
    '# Diagnóstico e inventario de imágenes del sitio público',
    'Inventario de imágenes detectadas',
    'Mapa de sección y coherencia visual',
    'Recomendaciones de seguimiento',
];

$docContents = '';
if (empty($errors) && is_file($docFile)) {
    $docContents = file_get_contents($docFile);
    if ($docContents === false) {
        $errors[] = "No se pudo leer el documento de diagnóstico.";
    }
}

$documentMissingMarkers = [];
if ($docContents !== '') {
    foreach ($expectedMarkers as $marker) {
        if (strpos($docContents, $marker) === false) {
            $documentMissingMarkers[] = $marker;
        }
    }
    if (!empty($documentMissingMarkers)) {
        $errors[] = "El documento de diagnóstico no contiene los marcadores esperados: " . implode(', ', $documentMissingMarkers);
    }
}

$pattern = '/(?:src|href)\s*=\s*["\'](assets\/img\/[^"\'>]+)["\']/i';
$imageMatches = [];
if ($contents !== '') {
    preg_match_all($pattern, $contents, $matches);
    if (!empty($matches[1])) {
        $imageMatches = array_unique($matches[1]);
    }
}

$missingFiles = [];
foreach ($imageMatches as $path) {
    $decodedPath = html_entity_decode($path, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $localPath = $root . '/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $decodedPath);
    if (!is_file($localPath)) {
        $missingFiles[] = $path;
    }
}

if (empty($imageMatches)) {
    $errors[] = "No se encontraron referencias a imágenes en assets/img/ dentro de index.html.";
}

if (!empty($missingFiles)) {
    $errors[] = "Referencias de imagen ausentes en el repositorio: " . implode(', ', $missingFiles);
}

if (!empty($errors)) {
    fwrite(STDERR, "ERROR: Validación de auditoría de imágenes fallida.\n");
    foreach ($errors as $error) {
        fwrite(STDERR, " - $error\n");
    }
    exit(1);
}

fwrite(STDOUT, "OK: Auditoría de imágenes completada. Se encontraron " . count($imageMatches) . " referencias válidas en index.html.\n");
exit(0);
