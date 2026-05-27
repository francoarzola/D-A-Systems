<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Services/QuotePdfService.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Core\AuthGuard;
use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuotePdfService;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\ViewFormatter;

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$statusCode = 200;
$title = 'PDF no disponible todavía';
$message = '';
$preparedFilename = null;
$backUrl = 'cotizaciones.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    $statusCode = 405;
    $message = 'La solicitud no es válida.';
} else {
    $quoteId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

    if (!is_int($quoteId) || $quoteId <= 0) {
        $statusCode = 400;
        $message = 'La cotización solicitada no es válida.';
    } else {
        $backUrl = 'cotizacion-detalle.php?id=' . $quoteId;

        try {
            $config = DatabaseConfig::fromDefaultPath()->load();
            $connection = new Connection($config);
            $repository = new QuoteRepository($connection->pdo());
            $service = new QuoteService($repository);
            $pdfService = new QuotePdfService();

            $quoteDetail = $service->getQuoteDetail($quoteId);

            if ($quoteDetail === null) {
                $statusCode = 404;
                $message = 'No se encontró la cotización solicitada.';
            } else {
                $quote = $quoteDetail['quote'];
                $pdfService->assertCanGeneratePdf($quote);
                $preparedFilename = $pdfService->buildPdfFilename($quote);
                $statusCode = 501;
                $message = 'La generación real de PDF será implementada en una etapa posterior.';
            }
        } catch (\InvalidArgumentException $exception) {
            $statusCode = 400;
            $message = 'La cotización no está disponible para preparar PDF.';
        } catch (\Throwable $exception) {
            $statusCode = 500;
            $message = 'No fue posible preparar la información del PDF.';
        }
    }
}

if ($statusCode === 501) {
    http_response_code(501);
} else {
    http_response_code($statusCode);
}
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
      <?php if ($preparedFilename !== null): ?>
      <p><strong>Archivo preparado:</strong> <?php echo ViewFormatter::e($preparedFilename); ?></p>
      <?php endif; ?>
      <p><?php echo ViewFormatter::e($message); ?></p>
      <p class="print-actions">
        <a href="<?php echo ViewFormatter::e($backUrl); ?>">Volver al detalle</a>
      </p>
    </section>
  </main>
</body>
</html>
