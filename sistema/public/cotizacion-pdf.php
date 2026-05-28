<?php

declare(strict_types=1);

$autoloadPath = __DIR__ . '/../../vendor/autoload.php';

if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Support/CompanyProfile.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteNumberRepository.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Services/QuotePdfService.php';
require_once __DIR__ . '/../app/Services/QuotePdfHtmlBuilder.php';
require_once __DIR__ . '/../app/Services/QuotePdfRenderService.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Core\AuthGuard;
use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuotePdfHtmlBuilder;
use DAndASystems\Internal\Services\QuotePdfRenderService;
use DAndASystems\Internal\Services\QuotePdfService;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\CompanyProfile;
use DAndASystems\Internal\Support\ViewFormatter;

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    renderErrorPage(405, 'Solicitud no válida', 'La solicitud no es válida.', null);
}

$quoteId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!is_int($quoteId) || $quoteId <= 0) {
    renderErrorPage(400, 'Cotización no válida', 'La cotización solicitada no es válida.', null);
}

$backUrl = 'cotizacion-detalle.php?id=' . $quoteId;

try {
    if (!is_file($autoloadPath)) {
        throw new RuntimeException('Dompdf no está disponible.');
    }

    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $repository = new QuoteRepository($connection->pdo());
    $service = new QuoteService($repository);
    $pdfService = new QuotePdfService();
    $htmlBuilder = new QuotePdfHtmlBuilder();
    $renderService = new QuotePdfRenderService();
    $companyProfile = CompanyProfile::fromDefaultConfig()->all();

    $quoteDetail = $service->getQuoteDetail($quoteId);

    if ($quoteDetail === null) {
        renderErrorPage(404, 'Cotización no encontrada', 'No se encontró la cotización solicitada.', $backUrl);
    }

    $quote = $quoteDetail['quote'];
    $details = is_array($quoteDetail['details'] ?? null) ? $quoteDetail['details'] : [];

    $pdfService->assertCanGeneratePdf($quote);

    $quoteForPdf = $quote;
    $quoteForPdf['details'] = $details;

    $html = $htmlBuilder->build($quoteForPdf, $companyProfile);
    $pdf = $renderService->renderHtmlToPdf($html);
    $filename = $pdfService->buildPdfFilename($quote);
    $downloadFilename = addcslashes($filename, "\\\"");

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, no-store, no-cache, must-revalidate');
    header('Pragma: no-cache');

    echo $pdf;
    exit;
} catch (\InvalidArgumentException $exception) {
    renderErrorPage(409, 'PDF no disponible', 'La cotización no está disponible para descargar PDF.', $backUrl);
} catch (\Throwable $exception) {
    renderErrorPage(500, 'Error al generar PDF', 'No fue posible generar el PDF de la cotización.', $backUrl);
}

function renderErrorPage(int $statusCode, string $title, string $message, ?string $backUrl): never
{
    http_response_code($statusCode);
    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo ViewFormatter::e($title); ?></title>
  <link rel="stylesheet" href="assets/css/internal.css">
</head>
<body class="print-page">
  <main class="print-document">
    <section class="print-panel">
      <h1><?php echo ViewFormatter::e($title); ?></h1>
      <p><?php echo ViewFormatter::e($message); ?></p>
      <p class="print-actions">
        <a href="<?php echo ViewFormatter::e($backUrl ?? 'cotizaciones.php'); ?>">Volver al detalle</a>
      </p>
    </section>
  </main>
</body>
</html>
    <?php
    exit;
}
