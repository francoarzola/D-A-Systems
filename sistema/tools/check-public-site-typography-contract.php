<?php
/**
 * Comprueba el contrato de ajuste tipográfico responsive (etapa 7D.03).
 * Uso: php sistema/tools/check-public-site-typography-contract.php
 */

if (php_sapi_name() !== 'cli') {
    fwrite(STDERR, "Este script debe ejecutarse desde la línea de comandos (CLI).\n");
    exit(2);
}

$root = realpath(__DIR__ . '/../../');
if ($root === false) {
    fwrite(STDERR, "No se pudo resolver el directorio raíz del proyecto.\n");
    exit(1);
}

$errors = [];
$warnings = [];

$indexFile = $root . '/index.html';
$docFile = $root . '/docs/sistema-interno/96-ajuste-tipografia-responsive-sitio-publico.md';
$scssFile = $root . '/assets/scss/sections/_hero.scss';
$mainScss = $root . '/assets/scss/main.scss';
$cssFile = $root . '/assets/css/main.css';

if (!is_file($indexFile)) {
    $errors[] = "Archivo no encontrado: index.html";
}

if (!is_file($docFile)) {
    $errors[] = "Documento de etapa no encontrado: docs/sistema-interno/96-ajuste-tipografia-responsive-sitio-publico.md";
}

// Verificar si index.html fue modificado respecto a Git (si hay repo disponible)
$isGitRepo = false;
exec('git rev-parse --is-inside-work-tree 2>NUL', $out, $rc);
if ($rc === 0 && !empty($out) && trim($out[0]) === 'true') {
    $isGitRepo = true;
    exec('git status --porcelain -- index.html', $statusOut, $statusRc);
    $statusText = implode("\n", $statusOut);
    if (trim($statusText) !== '') {
        $errors[] = "index.html tiene cambios sin confirmar respecto a HEAD: git status muestra modificaciones.";
    }
} else {
    $warnings[] = "No se detectó un repositorio Git; se omite la comprobación de cambios en index.html.";
}

// Verificar presencia de selectores/estilos relevantes
$styleSources = [];
if (is_file($scssFile)) {
    $styleSources[] = $scssFile;
}
if (is_file($mainScss)) {
    $styleSources[] = $mainScss;
}
if (is_file($cssFile)) {
    $styleSources[] = $cssFile;
}

if (empty($styleSources)) {
    $errors[] = "No se encontraron archivos de estilos esperados en assets/scss o assets/css.";
}

$foundHero = false;
$foundH1 = false;
$foundSectionTitle = false;
$foundMedia = false;

foreach ($styleSources as $s) {
    $txt = file_get_contents($s);
    if ($txt === false) {
        continue;
    }
    if (strpos($txt, '.hero') !== false) {
        $foundHero = true;
    }
    if (preg_match('/(^|[^a-zA-Z0-9_-])h1(\s*\{|\s*\.|\s*\:)/i', $txt)) {
        $foundH1 = true;
    }
    if (strpos($txt, '.section-title') !== false || strpos($txt, 'section-title') !== false) {
        $foundSectionTitle = true;
    }
    if (strpos($txt, '@media') !== false) {
        $foundMedia = true;
    }
}

if (!$foundHero) {
    $errors[] = "No se detectó el selector '.hero' en los archivos de estilo revisados.";
}
if (!$foundH1) {
    $errors[] = "No se detectaron reglas para 'h1' en los archivos de estilo revisados.";
}
if (!$foundSectionTitle) {
    $errors[] = "No se detectó '.section-title' en los archivos de estilo revisados.";
}
if (!$foundMedia) {
    $errors[] = "No se detectaron media queries ('@media') en los archivos de estilo revisados.";
}

// Evitar dependencias externas, BD o redes: solo comprobaciones locales
$prohibited = [
    'curl ', 'file_get_contents(', 'fsockopen(', 'stream_socket_client(', 'pdo_connect', 'mysqli_connect'
];
foreach ($styleSources as $s) {
    $txt = file_get_contents($s);
    foreach ($prohibited as $p) {
        if (strpos($txt, $p) !== false) {
            $warnings[] = "Patrón potencialmente prohibido encontrado en $s: $p";
        }
    }
}

if (!empty($errors)) {
    fwrite(STDERR, "ERROR: El contrato tipográfico no se cumple:\n");
    foreach ($errors as $e) {
        fwrite(STDERR, " - $e\n");
    }
    if (!empty($warnings)) {
        fwrite(STDERR, "Advertencias:\n");
        foreach ($warnings as $w) {
            fwrite(STDERR, " - $w\n");
        }
    }
    exit(1);
}

fwrite(STDOUT, "[OK] Ajuste tipográfico responsive del sitio público cumple el contrato esperado.\n");
if (!empty($warnings)) {
    fwrite(STDOUT, "Advertencias:\n");
    foreach ($warnings as $w) {
        fwrite(STDOUT, " - $w\n");
    }
}

exit(0);
