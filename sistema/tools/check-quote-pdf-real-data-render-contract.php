<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    echo "[ERROR] Esta herramienta solo puede ejecutarse por CLI.\n";
    exit(1);
}

$rootPath = dirname(__DIR__);
$projectRoot = dirname($rootPath);
$autoloadPath = $projectRoot . '/vendor/autoload.php';
$pdfEndpointPath = $rootPath . '/public/cotizacion-pdf.php';
$detailViewPath = $rootPath . '/public/cotizacion-detalle.php';
$printViewPath = $rootPath . '/public/cotizacion-imprimir.php';

$errors = [];

if (!is_file($autoloadPath)) {
    $errors[] = 'vendor/autoload.php no existe. Ejecutar composer install.';
} else {
    require_once $autoloadPath;
}

require_once $rootPath . '/app/Infrastructure/Config/DatabaseConfig.php';
require_once $rootPath . '/app/Infrastructure/Database/Connection.php';
require_once $rootPath . '/app/Support/CompanyProfile.php';
require_once $rootPath . '/app/Repositories/QuoteNumberRepository.php';
require_once $rootPath . '/app/Repositories/QuoteRepository.php';
require_once $rootPath . '/app/Services/QuoteService.php';
require_once $rootPath . '/app/Services/QuotePdfService.php';
require_once $rootPath . '/app/Services/QuotePdfRenderService.php';
require_once $rootPath . '/app/Services/QuotePdfHtmlBuilder.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuotePdfHtmlBuilder;
use DAndASystems\Internal\Services\QuotePdfRenderService;
use DAndASystems\Internal\Services\QuotePdfService;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\CompanyProfile;

$quoteId = parseQuoteId($argv[1] ?? null, $errors);

$pdfEndpoint = readFileContents($pdfEndpointPath, $errors);
assertDoesNotContain($pdfEndpoint, [
    'Content-Type: application/pdf',
    'Content-Disposition',
    'stream(',
    'readfile',
], 'cotizacion-pdf.php', $errors);

$detailView = readFileContents($detailViewPath, $errors);
$printView = readFileContents($printViewPath, $errors);
assertDoesNotContain($detailView . "\n" . $printView, [
    'Descargar PDF',
], 'vistas de cotización', $errors);

if ($errors === [] && !class_exists(\Dompdf\Dompdf::class)) {
    $errors[] = 'Dompdf\\Dompdf no está disponible. Ejecutar composer install.';
}

if ($errors === []) {
    try {
        $config = DatabaseConfig::fromDefaultPath()->load();
        $connection = new Connection($config);
        $repository = new QuoteRepository($connection->pdo());
        $quoteService = new QuoteService($repository);
        $pdfService = new QuotePdfService();
        $htmlBuilder = new QuotePdfHtmlBuilder();
        $renderService = new QuotePdfRenderService();
        $companyProfile = CompanyProfile::fromDefaultConfig()->all();

        $quoteDetail = $quoteService->getQuoteDetail($quoteId);

        if ($quoteDetail === null) {
            $errors[] = 'No se encontró la cotización solicitada.';
        } else {
            $quote = $quoteDetail['quote'];
            $details = is_array($quoteDetail['details'] ?? null) ? $quoteDetail['details'] : [];
            $pdfService->assertCanGeneratePdf($quote);

            $quoteForPdf = $quote;
            $quoteForPdf['details'] = $details;

            $html = $htmlBuilder->build($quoteForPdf, $companyProfile);
            assertHtmlContainsQuoteData($html, $quoteForPdf, $companyProfile, $errors);

            $pdf = $renderService->renderHtmlToPdf($html);

            if (!is_string($pdf)) {
                $errors[] = 'El resultado del render no es string.';
            }

            if (!str_starts_with($pdf, '%PDF')) {
                $errors[] = 'El resultado del render no comienza con %PDF.';
            }

            if (strlen($pdf) <= 1000) {
                $errors[] = 'El PDF generado en memoria no supera 1000 bytes.';
            }
        }
    } catch (\InvalidArgumentException $exception) {
        $errors[] = 'La cotización no está disponible para generar PDF.';
    } catch (\Throwable $exception) {
        $errors[] = 'No fue posible renderizar el PDF con datos reales.';
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        echo '[ERROR] ' . $error . "\n";
    }

    exit(1);
}

echo "[OK] Render PDF con datos reales generado en memoria.\n";
echo '[OK] Cotización: ' . (string) ($quote['numero_cotizacion'] ?? 'Sin número') . "\n";
echo "[OK] No se guardó archivo, no se activó descarga y no se modificó base de datos.\n";
exit(0);

function parseQuoteId(mixed $value, array &$errors): int
{
    if ($value === null || $value === '') {
        return 3;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT);

    if (!is_int($id) || $id <= 0) {
        $errors[] = 'El id de cotización debe ser un entero positivo.';

        return 0;
    }

    return $id;
}

function assertHtmlContainsQuoteData(string $html, array $quote, array $companyProfile, array &$errors): void
{
    $expectedValues = [
        (string) ($quote['numero_cotizacion'] ?? ''),
        (string) ($quote['nombre_cliente'] ?? ''),
        '$' . number_format((float) ($quote['total'] ?? 0), 0, ',', '.'),
        (string) ($companyProfile['commercial_name'] ?? ''),
    ];

    foreach ($expectedValues as $value) {
        if ($value === '' || !str_contains($html, htmlspecialchars($value, ENT_QUOTES, 'UTF-8'))) {
            $errors[] = 'El HTML generado no contiene un dato esperado de la cotización.';
        }
    }
}

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

function assertDoesNotContain(string $contents, array $forbiddenNeedles, string $label, array &$errors): void
{
    foreach ($forbiddenNeedles as $needle) {
        if (str_contains($contents, $needle)) {
            $errors[] = $label . ' contiene fragmento no permitido: ' . $needle . '.';
        }
    }
}

function displayPath(string $path): string
{
    $normalized = str_replace('\\', '/', $path);
    $marker = '/D-A-Systems/';
    $position = strpos($normalized, $marker);

    if ($position === false) {
        return $path;
    }

    return substr($normalized, $position + strlen($marker));
}
