<?php

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "ERROR: Este script debe ejecutarse solo por CLI.\n");
    exit(1);
}

$root = dirname(__DIR__, 2);
$baseUrl = 'https://www.dasystems.cl';
$expectedFavicon = 'assets/img/uploads/favicon-dasystems.png';
$expectedOgImage = 'https://www.dasystems.cl/assets/img/uploads/Logoweb400.png';

$pages = [
    'index.html' => [
        'canonical' => "$baseUrl/",
    ],
    'servicios-ti.html' => [
        'canonical' => "$baseUrl/servicios-ti.html",
    ],
    'soluciones-ti.html' => [
        'canonical' => "$baseUrl/soluciones-ti.html",
    ],
    'nosotros.html' => [
        'canonical' => "$baseUrl/nosotros.html",
    ],
    'terminos-condiciones.html' => [
        'canonical' => "$baseUrl/terminos-condiciones.html",
    ],
    'politica-privacidad.html' => [
        'canonical' => "$baseUrl/politica-privacidad.html",
    ],
];

function fail(string $message): void
{
    fwrite(STDERR, "ERROR: {$message}\n");
    exit(1);
}

function ok(string $message): void
{
    echo "OK: {$message}\n";
}

function getAttributeContent(string $content, string $pattern, string $field, string $file): string
{
    if (!preg_match($pattern, $content, $matches)) {
        fail("{$file}: no se encontró {$field}.");
    }

    return trim($matches[1]);
}

foreach ($pages as $page => $data) {
    $path = $root . DIRECTORY_SEPARATOR . $page;
    if (!file_exists($path)) {
        fail("Archivo público no encontrado: {$page}");
    }

    $content = file_get_contents($path);
    if ($content === false) {
        fail("No se pudo leer el archivo: {$page}");
    }

    if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
        fail("BOM UTF-8 encontrado en {$page}");
    }

    if (stripos($content, '<meta name="keywords"') !== false || stripos($content, '<meta name=keywords') !== false) {
        fail("meta keywords encontrado en {$page}");
    }

    $mojibakePatterns = [
        'Ã',
        'Â',
        'â€',
        'â–',
        'â—',
        'â€™',
        'â€œ',
    ];
    foreach ($mojibakePatterns as $pattern) {
        if (strpos($content, $pattern) !== false) {
            fail("posible texto mal codificado/mojibake detectado en {$page}.");
        }
    }

    $description = getAttributeContent($content, '/<meta\s+[^>]*name=["\"]description["\"][^>]*content=["\"]([^"\"]+)["\"]/i', 'meta description', $page);
    if ($description === '') {
        fail("{$page}: meta description está vacía.");
    }
    if (preg_match('/\bSpA\b/i', $description)) {
        fail("{$page}: meta description contiene 'SpA'.");
    }

    $canonical = getAttributeContent($content, '/<link\s+[^>]*rel=["\"]canonical["\"][^>]*href=["\"]([^"\"]+)["\"]/i', 'canonical', $page);
    if ($canonical !== $data['canonical']) {
        fail("{$page}: canonical esperado {$data['canonical']}, encontrado {$canonical}.");
    }
    if (stripos($canonical, 'http') !== 0) {
        fail("{$page}: canonical no es absoluto.");
    }

    $ogTitle = getAttributeContent($content, '/<meta\s+[^>]*property=["\"]og:title["\"][^>]*content=["\"]([^"\"]+)["\"]/i', 'og:title', $page);
    if (preg_match('/\bSpA\b/i', $ogTitle)) {
        fail("{$page}: og:title contiene 'SpA'.");
    }

    $ogDescription = getAttributeContent($content, '/<meta\s+[^>]*property=["\"]og:description["\"][^>]*content=["\"]([^"\"]+)["\"]/i', 'og:description', $page);
    if ($ogDescription === '') {
        fail("{$page}: og:description está vacía.");
    }
    if (preg_match('/\bSpA\b/i', $ogDescription)) {
        fail("{$page}: og:description contiene 'SpA'.");
    }

    $ogUrl = getAttributeContent($content, '/<meta\s+[^>]*property=["\"]og:url["\"][^>]*content=["\"]([^"\"]+)["\"]/i', 'og:url', $page);
    if ($ogUrl !== $data['canonical']) {
        fail("{$page}: og:url esperado {$data['canonical']}, encontrado {$ogUrl}.");
    }
    if (stripos($ogUrl, 'http') !== 0) {
        fail("{$page}: og:url no es absoluto.");
    }

    $ogImage = getAttributeContent($content, '/<meta\s+[^>]*property=["\"]og:image["\"][^>]*content=["\"]([^"\"]+)["\"]/i', 'og:image', $page);
    if ($ogImage !== $GLOBALS['expectedOgImage']) {
        fail("{$page}: og:image esperado {$GLOBALS['expectedOgImage']}, encontrado {$ogImage}.");
    }

    if (!preg_match('/<link\s+[^>]*href=["\"]' . preg_quote($expectedFavicon, '/') . '["\"][^>]*rel=["\"]icon["\"]/', $content)) {
        fail("{$page}: favicon no apunta a {$expectedFavicon}.");
    }
    if (stripos($content, 'favicon D&A Systems') !== false || stripos($content, 'favicon D&amp;A Systems') !== false) {
        fail("{$page}: encontrado favicon antiguo con espacios o nombre no normalizado.");
    }

    if (preg_match('/<head.*?<\/head>/is', $content, $headMatches)) {
        $headContent = $headMatches[0];
        if (preg_match('/\bD&amp;A Systems SpA\b/i', $headContent) || preg_match('/\bD&A Systems SpA\b/i', $headContent)) {
            fail("{$page}: 'D&A Systems SpA' encontrado en metadata.");
        }
    }

    ok("{$page} pasa las verificaciones de metadatos.");
}

// index page specific validations
$indexPath = $root . DIRECTORY_SEPARATOR . 'index.html';
$indexContent = file_get_contents($indexPath);
if ($indexContent === false) {
    fail('No se pudo leer index.html para validación adicional.');
}

$indexPatterns = [
    'form action="forms/contact.php"' => 'action del formulario de contacto',
    'name="csrf_token"' => 'campo csrf_token',
    'name="privacy_consent"' => 'campo privacy_consent',
    'name="website"' => 'campo honeypot website',
    'assets/vendor/bootstrap/js/bootstrap.bundle.min.js' => 'script bootstrap.bundle.min.js',
    'assets/vendor/php-email-form/validate.js' => 'script validate.js',
    'assets/js/main.js' => 'script main.js',
];
foreach ($indexPatterns as $pattern => $label) {
    if (stripos($indexContent, $pattern) === false) {
        fail("index.html: no se encontró {$label} ({$pattern}).");
    }
}
ok('index.html conserva los campos de formulario y scripts principales.');

// robots.txt validation
$robotsPath = $root . DIRECTORY_SEPARATOR . 'robots.txt';
if (!file_exists($robotsPath)) {
    fail('robots.txt no encontrado.');
}
$robotsContent = file_get_contents($robotsPath);
if ($robotsContent === false) {
    fail('No se pudo leer robots.txt.');
}
$robotsExpected = [
    'User-agent: *',
    'Allow: /',
    'Sitemap: https://www.dasystems.cl/sitemap.xml',
];
foreach ($robotsExpected as $line) {
    if (stripos($robotsContent, $line) === false) {
        fail("robots.txt: no se encontró la línea esperada '{$line}'.");
    }
}
ok('robots.txt contiene las reglas esperadas.');

// sitemap.xml validation
$sitemapPath = $root . DIRECTORY_SEPARATOR . 'sitemap.xml';
if (!file_exists($sitemapPath)) {
    fail('sitemap.xml no encontrado.');
}
$sitemapContent = file_get_contents($sitemapPath);
if ($sitemapContent === false) {
    fail('No se pudo leer sitemap.xml.');
}
$xml = @simplexml_load_string($sitemapContent);
if ($xml === false) {
    fail('sitemap.xml no es un XML válido.');
}

$expectedSitemapUrls = [
    "$baseUrl/",
    "$baseUrl/servicios-ti.html",
    "$baseUrl/soluciones-ti.html",
    "$baseUrl/nosotros.html",
    "$baseUrl/terminos-condiciones.html",
    "$baseUrl/politica-privacidad.html",
];
$foundUrls = [];
foreach ($xml->url as $urlNode) {
    $loc = trim((string) $urlNode->loc);
    if ($loc === '') {
        fail('sitemap.xml contiene una etiqueta <loc> vacía.');
    }
    $foundUrls[] = $loc;
    if (!in_array($loc, $expectedSitemapUrls, true)) {
        fail("sitemap.xml contiene una URL inesperada: {$loc}");
    }
    $parsed = parse_url($loc);
    if (!isset($parsed['host']) || stripos($loc, $baseUrl) !== 0) {
        fail("sitemap.xml contiene una URL no absoluta o fuera de {$baseUrl}: {$loc}");
    }
    $localPath = $parsed['path'] === '/' ? 'index.html' : ltrim($parsed['path'], '/');
    if (!file_exists($root . DIRECTORY_SEPARATOR . $localPath)) {
        fail("URL en sitemap.xml no tiene archivo local correspondiente: {$loc}");
    }
}

sort($foundUrls);
sort($expectedSitemapUrls);
if ($foundUrls !== $expectedSitemapUrls) {
    fail('sitemap.xml no contiene exactamente las URLs públicas esperadas.');
}
ok('sitemap.xml contiene las URLs públicas esperadas y es válido.');

ok('Normalización SEO del sitio público cumple el contrato esperado.');
exit(0);
