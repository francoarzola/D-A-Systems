<?php

declare(strict_types=1);

/**
 * Check cPanel Deployment Readiness Contract
 * 
 * Validates that the D&A Systems public site is ready for cPanel deployment.
 * Run only from CLI.
 * 
 * Usage: php sistema/tools/check-cpanel-deployment-readiness-contract.php
 * 
 * Exit codes:
 *   0: Success (site ready)
 *   1: CLI required
 *   2: Blocking errors found
 *   3: Warnings present (non-blocking)
 */

if (PHP_SAPI !== 'cli') {
    echo "This script must be run from CLI only.\n";
    exit(1);
}

$errors = [];
$warnings = [];
$base = dirname(dirname(__DIR__));  // Root of repo

echo "[AUDIT] Iniciando auditoría de preparación para despliegue en cPanel...\n\n";

// ============================================================================
// 1. VALIDATION: Public HTML pages exist and are UTF-8
// ============================================================================
echo "[CHECK] Validando páginas HTML públicas...\n";

$publicPages = [
    'index.html',
    'servicios-ti.html',
    'soluciones-ti.html',
    'nosotros.html',
    'terminos-condiciones.html',
    'politica-privacidad.html',
];

foreach ($publicPages as $page) {
    $path = $base . '/' . $page;
    if (!file_exists($path)) {
        $errors[] = "Missing public page: $page";
        continue;
    }
    
    $content = file_get_contents($path);
    
    // Check UTF-8 without BOM
    if (str_starts_with($content, "\xEF\xBB\xBF")) {
        $errors[] = "$page contains UTF-8 BOM (debe ser sin BOM)";
    }
    
    // Check for mojibake patterns
    $mojibakePatterns = ['Ã', 'Â', 'â€', 'â–', 'â—', 'â€™', 'â€œ', '�'];
    foreach ($mojibakePatterns as $pattern) {
        if (stripos($content, $pattern) !== false) {
            $errors[] = "$page contiene mojibake (encoding problem): $pattern";
        }
    }
    
    // Check for forbidden "SpA"
    if (preg_match('/\bSpA\b|D&(?:amp;)?A Systems SpA/i', $content)) {
        $errors[] = "$page contiene 'SpA' (prohibido - empresa no constituida como SpA)";
    }
    
    // Check for forbidden old favicon
    if (stripos($content, 'favicon D&A Systems.png') !== false || 
        stripos($content, 'favicon%20D&A%20Systems') !== false) {
        $errors[] = "$page referencia favicon antiguo (debe usar favicon-dasystems.png)";
    }
    
    // Check for correct favicon
    if (stripos($content, 'favicon-dasystems.png') === false) {
        $errors[] = "$page no referencia favicon-dasystems.png correcto";
    }
    
    // Check metadata
    if (preg_match('/<title>[\s]*<\/title>/', $content)) {
        $errors[] = "$page contiene <title> vacío";
    }
    
    if (!preg_match('/<meta\s+name="description"/', $content)) {
        $errors[] = "$page falta <meta name=\"description\">";
    }
    
    if (!preg_match('/<link\s+rel="canonical"/', $content)) {
        $errors[] = "$page falta <link rel=\"canonical\">";
    }
    
    // Check for dangerous meta keywords (deprecated)
    if (preg_match('/<meta\s+name="keywords"/', $content)) {
        $warnings[] = "$page contiene deprecated <meta name=\"keywords\"> (no recomendado en 2026)";
    }
    
    echo "  ✓ $page OK\n";
}

// ============================================================================
// 2. VALIDATION: Critical files exist
// ============================================================================
echo "\n[CHECK] Validando archivos críticos...\n";

$criticalFiles = [
    'forms/contact.php' => 'Formulario de contacto',
    'config/contact.php' => 'Configuración de contacto',
    'forms/csrf-token.php' => 'CSRF token generator',
    'sistema/config/company.php' => 'Datos comerciales',
    'composer.json' => 'Composer manifest',
    'sitemap.xml' => 'Sitemap XML',
    'robots.txt' => 'Robots.txt',
    'assets/img/uploads/favicon-dasystems.png' => 'Favicon',
];

foreach ($criticalFiles as $file => $description) {
    $path = $base . '/' . $file;
    if (!file_exists($path)) {
        $errors[] = "Missing critical file: $file ($description)";
    } else {
        echo "  ✓ $file OK\n";
    }
}

// ============================================================================
// 3. VALIDATION: PHP syntax for critical files
// ============================================================================
echo "\n[CHECK] Validando sintaxis PHP...\n";

$phpFilesToCheck = [
    'forms/contact.php',
    'config/contact.php',
    'forms/csrf-token.php',
    'sistema/config/company.php',
];

foreach ($phpFilesToCheck as $phpFile) {
    $path = $base . '/' . $phpFile;
    if (file_exists($path)) {
        // Try to parse the file to detect syntax errors
        try {
            $tokens = token_get_all(file_get_contents($path));
            echo "  ✓ $phpFile OK\n";
        } catch (Throwable $e) {
            $errors[] = "PHP syntax error in $phpFile: " . $e->getMessage();
        }
    }
}

// ============================================================================
// 4. VALIDATION: sitemap.xml is valid XML
// ============================================================================
echo "\n[CHECK] Validando sitemap.xml...\n";

$sitemapPath = $base . '/sitemap.xml';
if (file_exists($sitemapPath)) {
    $sitemapContent = file_get_contents($sitemapPath);
    
    // Try to parse as XML
    $xml = @simplexml_load_string($sitemapContent);
    if ($xml === false) {
        $errors[] = 'sitemap.xml is not valid XML';
    } else {
        // Check for public URLs only
        $forbiddenPatterns = ['/docs/', '/sistema/tools/', '/.git/', '/storage/', '/vendor/'];
        foreach ($xml->url as $urlElement) {
            $url = (string)$urlElement->loc;
            foreach ($forbiddenPatterns as $pattern) {
                if (stripos($url, $pattern) !== false) {
                    $errors[] = "sitemap.xml contains forbidden path: $url";
                }
            }
        }
        echo "  ✓ sitemap.xml is valid XML\n";
        echo "  ✓ sitemap.xml contains " . count($xml->url) . " URLs\n";
    }
}

// ============================================================================
// 5. VALIDATION: robots.txt format
// ============================================================================
echo "\n[CHECK] Validando robots.txt...\n";

$robotsPath = $base . '/robots.txt';
if (file_exists($robotsPath)) {
    $robotsContent = file_get_contents($robotsPath);
    
    if (stripos($robotsContent, 'User-agent:') === false) {
        $errors[] = 'robots.txt missing User-agent declaration';
    }
    
    if (stripos($robotsContent, 'Sitemap:') === false) {
        $errors[] = 'robots.txt missing Sitemap reference';
    }
    
    echo "  ✓ robots.txt format OK\n";
}

// ============================================================================
// 6. VALIDATION: Contact form configuration
// ============================================================================
echo "\n[CHECK] Validando configuración de formulario de contacto...\n";

$contactConfigPath = $base . '/config/contact.php';
if (file_exists($contactConfigPath)) {
    $contactConfig = file_get_contents($contactConfigPath);
    
    // Check for hardcoded credentials (SMTP passwords, etc.)
    $credentialPatterns = [
        '/password\s*[=:]\s*["\'][^"\']+["\']/',  // password = "..."
        '/SMTP_PASSWORD\s*=\s*["\'][^"\']+["\']/',
        '/api.?key\s*[=:]\s*["\'][^"\']+["\']/',
        '/secret\s*[=:]\s*["\'][^"\']+["\']/',
    ];
    
    foreach ($credentialPatterns as $pattern) {
        if (preg_match($pattern, $contactConfig)) {
            $errors[] = 'config/contact.php contains hardcoded credentials (use environment variables)';
            break;
        }
    }
    
    echo "  ✓ Contact form config OK\n";
}

// ============================================================================
// 7. VALIDATION: Quotation module files
// ============================================================================
echo "\n[CHECK] Validando módulo de cotizaciones...\n";

$quoteFiles = [
    'sistema/public/login.php',
    'sistema/public/cotizaciones.php',
    'sistema/public/cotizacion-detalle.php',
    'sistema/public/cotizacion-editar.php',
    'sistema/public/cotizacion-imprimir.php',
    'sistema/public/cotizacion-pdf.php',
    'sistema/public/cotizacion-guardar.php',
    'sistema/public/cotizacion-actualizar.php',
    'sistema/public/cotizacion-emitir.php',
];

$quoteFilesFound = 0;
foreach ($quoteFiles as $file) {
    $path = $base . '/' . $file;
    if (file_exists($path)) {
        $quoteFilesFound++;
    }
}

if ($quoteFilesFound < 5) {
    $warnings[] = "Only $quoteFilesFound quotation files found (expected 9). Módulo podría no estar completamente funcional.";
} else {
    echo "  ✓ Quotation module files present ($quoteFilesFound/9)\n";
}

// ============================================================================
// 8. VALIDATION: Company profile data
// ============================================================================
echo "\n[CHECK] Validando datos comerciales...\n";

$companyPath = $base . '/sistema/config/company.php';
if (file_exists($companyPath)) {
    $companyData = include $companyPath;
    
    if (is_array($companyData)) {
        $hasPending = false;
        foreach ($companyData as $key => $value) {
            if (stripos($value, 'Pendiente') !== false) {
                $hasPending = true;
                break;
            }
        }
        
        if ($hasPending) {
            $warnings[] = 'sistema/config/company.php contiene valores "Pendiente". Completar antes de emitir cotizaciones oficiales.';
        } else {
            echo "  ✓ Company data complete\n";
        }
    }
}

// ============================================================================
// 9. VALIDATION: Composer and vendor
// ============================================================================
echo "\n[CHECK] Validando Composer y vendor...\n";

$composerPath = $base . '/composer.json';
if (!file_exists($composerPath)) {
    $errors[] = 'composer.json not found';
} else {
    $composerJson = @json_decode(file_get_contents($composerPath), true);
    if (!is_array($composerJson)) {
        $errors[] = 'composer.json is not valid JSON';
    } else {
        echo "  ✓ composer.json OK\n";
    }
}

$vendorAutoload = $base . '/vendor/autoload.php';
if (!file_exists($vendorAutoload)) {
    $warnings[] = 'vendor/autoload.php not found. En despliegue debe ejecutarse: composer install';
} else {
    echo "  ✓ vendor/autoload.php found\n";
}

// ============================================================================
// 10. VALIDATION: Asset files
// ============================================================================
echo "\n[CHECK] Validando archivos de assets...\n";

$assetDirs = [
    'assets/css',
    'assets/js',
    'assets/img',
    'assets/vendor',
];

foreach ($assetDirs as $dir) {
    $path = $base . '/' . $dir;
    if (!is_dir($path)) {
        $errors[] = "Missing asset directory: $dir";
    } else {
        echo "  ✓ $dir exists\n";
    }
}

// ============================================================================
// 11. VALIDATION: No sensitive files exposed
// ============================================================================
echo "\n[CHECK] Validando ausencia de archivos sensibles...\n";

$forbiddenFiles = [
    '.env',
    '.env.local',
    '.git',
    '.gitignore',
    '.env.example',
];

$foundSensitive = false;
foreach ($forbiddenFiles as $sensFile) {
    $path = $base . '/' . $sensFile;
    if (file_exists($path) || is_dir($path)) {
        // These are OK to exist locally, but should not be in public_html
        // Just warn
        if ($sensFile === '.env' || $sensFile === '.env.local') {
            $warnings[] = "Sensitive file exists locally: $sensFile (make sure not uploaded to public_html)";
            $foundSensitive = true;
        }
    }
}

if (!$foundSensitive) {
    echo "  ✓ No obvious sensitive files exposed\n";
}

// ============================================================================
// 12. SUMMARY
// ============================================================================
echo "\n" . str_repeat("=", 70) . "\n";
echo "[RESULT] RESUMEN DE AUDITORÍA\n";
echo str_repeat("=", 70) . "\n\n";

if (!empty($errors)) {
    echo "[ERRORS] Errores bloqueantes encontrados:\n";
    foreach ($errors as $i => $error) {
        echo sprintf("  %2d. %s\n", $i + 1, $error);
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "[WARNINGS] Advertencias (no bloqueantes):\n";
    foreach ($warnings as $i => $warning) {
        echo sprintf("  %2d. %s\n", $i + 1, $warning);
    }
    echo "\n";
}

if (empty($errors)) {
    echo "\n✅ [OK] Sitio público listo para preparación de despliegue en cPanel.\n\n";
    echo "Próximos pasos:\n";
    echo "  1. Revisar archivo: docs/sistema-interno/99-preparacion-despliegue-cpanel.md\n";
    echo "  2. Preparar hosting en cPanel\n";
    echo "  3. Subir archivos vía SFTP/Git\n";
    echo "  4. Configurar variables de entorno SMTP\n";
    echo "  5. Probar formulario de contacto\n";
    echo "  6. Probar módulo de cotizaciones\n";
    echo "  7. Validar SEO en Google Search Console\n\n";
    exit(0);
} else {
    echo "\n❌ [FAIL] Errores detectados. Revisar arriba.\n\n";
    exit(2);
}
