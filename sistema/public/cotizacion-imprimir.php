<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Core\AuthGuard;
use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\ViewFormatter;

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$quoteId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$quote = null;
$details = [];
$errorMessage = null;

if (!is_int($quoteId) || $quoteId <= 0) {
    $errorMessage = 'La cotización solicitada no es válida.';
} else {
    try {
        $config = DatabaseConfig::fromDefaultPath()->load();
        $connection = new Connection($config);
        $repository = new QuoteRepository($connection->pdo());
        $service = new QuoteService($repository);

        $quoteDetail = $service->getQuoteDetail($quoteId);

        if ($quoteDetail === null) {
            $errorMessage = 'No se encontró la cotización solicitada.';
        } elseif (!isPrintableQuote($quoteDetail['quote'])) {
            $errorMessage = 'La vista imprimible solo está disponible para cotizaciones emitidas con número oficial.';
        } else {
            $quote = $quoteDetail['quote'];
            $details = $quoteDetail['details'];
        }
    } catch (\Throwable $exception) {
        $errorMessage = 'No fue posible cargar la vista imprimible de la cotización.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cotización imprimible - D&A Systems</title>
  <link rel="stylesheet" href="assets/css/internal.css">
</head>
<body class="print-page">
  <main class="print-document">
    <?php if ($errorMessage !== null): ?>
    <section class="print-panel">
      <h1>Vista imprimible no disponible</h1>
      <p><?php echo ViewFormatter::e($errorMessage); ?></p>
      <p class="print-actions"><a href="cotizaciones.php">Volver al listado</a></p>
    </section>
    <?php else: ?>
    <header class="print-header">
      <div>
        <p class="print-brand">D&amp;A Systems</p>
        <h1>Cotización</h1>
      </div>
      <div class="print-meta">
        <p><strong>Número:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteNumber($quote['numero_cotizacion'] ?? null)); ?></p>
        <p><strong>Estado:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteStatus($quote['estado'] ?? null)); ?></p>
      </div>
    </header>

    <section class="print-grid">
      <article class="print-panel">
        <h2>Datos de cotización</h2>
        <p><strong>Fecha:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['fecha_cotizacion'] ?? null)); ?></p>
        <p><strong>Validez:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['valido_hasta'] ?? null)); ?></p>
      </article>
      <article class="print-panel">
        <h2>Cliente</h2>
        <p><strong>Nombre:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['nombre_cliente'] ?? null)); ?></p>
        <p><strong>RUT:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['rut_cliente'] ?? null)); ?></p>
        <p><strong>Contacto:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['nombre_contacto'] ?? null)); ?></p>
        <p><strong>Correo:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['correo_contacto'] ?? null)); ?></p>
        <p><strong>Teléfono:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['telefono_contacto'] ?? null)); ?></p>
      </article>
    </section>

    <section class="print-panel">
      <h2>Descripción</h2>
      <p><?php echo ViewFormatter::e(ViewFormatter::text($quote['descripcion'] ?? null)); ?></p>
    </section>

    <section class="print-panel">
      <h2>Detalles</h2>
      <?php if ($details === []): ?>
      <p>Esta cotización no tiene detalles registrados.</p>
      <?php else: ?>
      <table class="print-table">
        <thead>
          <tr>
            <th>Línea</th>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th class="quote-align-right">Precio unitario neto</th>
            <th class="quote-align-right">Descuento</th>
            <th class="quote-align-right">Total línea neto</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($details as $detail): ?>
          <tr>
            <td><?php echo ViewFormatter::e((string) ($detail['numero_linea'] ?? '')); ?></td>
            <td><?php echo ViewFormatter::e(ViewFormatter::text($detail['descripcion'] ?? null)); ?></td>
            <td><?php echo ViewFormatter::e(ViewFormatter::quantity($detail['cantidad'] ?? null)); ?></td>
            <td><?php echo ViewFormatter::e(ViewFormatter::text($detail['unidad'] ?? null)); ?></td>
            <td class="quote-align-right"><?php echo ViewFormatter::e(ViewFormatter::money($detail['precio_unitario_neto'] ?? null)); ?></td>
            <td class="quote-align-right"><?php echo ViewFormatter::e(ViewFormatter::money($detail['descuento_monto'] ?? null)); ?></td>
            <td class="quote-align-right"><?php echo ViewFormatter::e(ViewFormatter::money($detail['total_linea_neto'] ?? null)); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </section>

    <section class="print-summary">
      <div></div>
      <article class="print-panel">
        <h2>Resumen</h2>
        <p><strong>Subtotal neto:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['subtotal_neto'] ?? null)); ?></p>
        <p><strong>Descuento:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['descuento_monto'] ?? null)); ?></p>
        <p><strong>IVA <?php echo ViewFormatter::e(ViewFormatter::percent($quote['iva_porcentaje'] ?? null)); ?>:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['iva_monto'] ?? null)); ?></p>
        <p class="print-total"><strong>Total:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['total'] ?? null)); ?></p>
      </article>
    </section>

    <section class="print-grid">
      <article class="print-panel">
        <h2>Condiciones comerciales</h2>
        <p><?php echo ViewFormatter::e(ViewFormatter::text($quote['condiciones_comerciales'] ?? null)); ?></p>
      </article>
      <article class="print-panel">
        <h2>Observaciones</h2>
        <p><?php echo ViewFormatter::e(ViewFormatter::text($quote['observaciones'] ?? null)); ?></p>
      </article>
    </section>

    <nav class="print-actions">
      <button type="button" onclick="window.print()">Imprimir</button>
      <a href="cotizacion-detalle.php?id=<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">Volver al detalle</a>
    </nav>
    <?php endif; ?>
  </main>
</body>
</html>
<?php

function isPrintableQuote(array $quote): bool
{
    $quoteNumber = is_string($quote['numero_cotizacion'] ?? null) ? trim($quote['numero_cotizacion']) : '';

    return ($quote['estado'] ?? null) === 'emitida' && $quoteNumber !== '';
}
