<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Security/CsrfToken.php';
require_once __DIR__ . '/../app/Support/FlashMessage.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Security\CsrfToken;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\FlashMessage;
use DAndASystems\Internal\Support\InternalPage;
use DAndASystems\Internal\Support\ViewFormatter;

InternalPage::render(
    'Detalle de cotización - Sistema interno D&A Systems',
    'Detalle de cotización',
    'cotizaciones',
    static function (): void {
        $quoteId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $quote = null;
        $details = [];
        $errorMessage = null;
        $csrf = new CsrfToken();
        $flash = (new FlashMessage())->pull();
        $flashType = normalizeFlashType($flash['type'] ?? null);

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
                } else {
                    $quote = $quoteDetail['quote'];
                    $details = $quoteDetail['details'];
                }
            } catch (\Throwable $exception) {
                $errorMessage = 'No fue posible cargar el detalle de la cotización.';
            }
        }
        ?>
<section class="internal-topbar" aria-label="Navegación interna de cotizaciones">
  <div class="internal-topbar-brand">
    <span class="internal-topbar-title">D&amp;A Systems</span>
    <span class="internal-topbar-subtitle">Sistema interno</span>
  </div>
  <nav class="internal-nav" aria-label="Accesos de cotizaciones">
    <a class="internal-nav-link internal-nav-link-active" href="cotizaciones.php" aria-current="page">Cotizaciones</a>
    <a class="internal-nav-link" href="cotizaciones.php">Crear borrador</a>
    <a class="internal-nav-link" href="logout.php">Cerrar sesión</a>
  </nav>
</section>

<?php if ($flash !== null): ?>
<section class="flash-message flash-message-<?php echo ViewFormatter::e($flashType); ?>">
  <h3><?php echo ViewFormatter::e(flashTitle($flashType)); ?></h3>
  <p><?php echo ViewFormatter::e(ViewFormatter::text($flash['message'] ?? null)); ?></p>
</section>
<?php endif; ?>

<?php if ($errorMessage !== null): ?>
<section class="status-panel">
  <h3>Detalle no disponible</h3>
  <p><?php echo ViewFormatter::e($errorMessage); ?></p>
</section>

<div class="quote-actions">
  <a class="quote-action quote-action-muted" href="cotizaciones.php">Volver al listado</a>
</div>
<?php else: ?>
<section class="status-panel">
  <h3>Detalle de cotización</h3>
  <p><strong>Estado:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteStatus($quote['estado'] ?? null)); ?></p>
  <p><strong>Número:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteNumber($quote['numero_cotizacion'] ?? null)); ?></p>
</section>

<section class="grid">
  <article class="card">
    <h2>Datos generales</h2>
    <p><strong>Fecha:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['fecha_cotizacion'] ?? null)); ?></p>
    <p><strong>Validez:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['valido_hasta'] ?? null)); ?></p>
    <p><strong>Estado:</strong> <?php echo ViewFormatter::e(ViewFormatter::quoteStatus($quote['estado'] ?? null)); ?></p>
    <p><strong>Total:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['total'] ?? null)); ?></p>
  </article>

  <article class="card">
    <h2>Cliente</h2>
    <p><strong>Nombre:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['nombre_cliente'] ?? null)); ?></p>
    <p><strong>RUT:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['rut_cliente'] ?? null)); ?></p>
    <p><strong>Contacto:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['nombre_contacto'] ?? null)); ?></p>
    <p><strong>Correo:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['correo_contacto'] ?? null)); ?></p>
    <p><strong>Teléfono:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['telefono_contacto'] ?? null)); ?></p>
  </article>

  <article class="card">
    <h2>Resumen</h2>
    <p><strong>Subtotal neto:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['subtotal_neto'] ?? null)); ?></p>
    <p><strong>Descuento:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['descuento_monto'] ?? null)); ?></p>
    <p><strong>IVA <?php echo ViewFormatter::e(ViewFormatter::percent($quote['iva_porcentaje'] ?? null)); ?>:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['iva_monto'] ?? null)); ?></p>
    <p><strong>Total:</strong> <?php echo ViewFormatter::e(ViewFormatter::money($quote['total'] ?? null)); ?></p>
  </article>
</section>

<section class="grid">
  <article class="card quote-span-2">
    <h2>Descripción y condiciones</h2>
    <p><strong>Descripción:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['descripcion'] ?? null)); ?></p>
    <p><strong>Condiciones comerciales:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['condiciones_comerciales'] ?? null)); ?></p>
    <p><strong>Observaciones:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['observaciones'] ?? null)); ?></p>
  </article>

  <article class="card">
    <h2>Registro</h2>
    <p><strong>Creado:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['creado_en'] ?? null)); ?></p>
    <p><strong>Actualizado:</strong> <?php echo ViewFormatter::e(ViewFormatter::text($quote['actualizado_en'] ?? null)); ?></p>
  </article>
</section>

<section class="card quote-section quote-table-wrapper">
  <h2>Detalles</h2>
  <?php if ($details === []): ?>
    <p class="quote-section-copy">Esta cotización no tiene detalles registrados.</p>
  <?php else: ?>
  <table class="quote-table quote-table-compact">
    <thead>
      <tr>
        <th>Línea</th>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th class="quote-align-right">Precio unitario neto</th>
        <th class="quote-align-right">Descuento</th>
        <th class="quote-align-right">Total línea</th>
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

<div class="quote-actions">
  <a class="quote-action quote-action-muted" href="cotizaciones.php">Volver al listado</a>
  <?php if (($quote['estado'] ?? null) === 'borrador'): ?>
  <a class="quote-action quote-action-primary" href="cotizacion-editar.php?id=<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">Editar borrador</a>
  <form method="post" action="cotizacion-emitir.php" class="quote-inline-form">
    <?php echo $csrf->inputField('quote_issue'); ?>
    <input type="hidden" name="cotizacion_id" value="<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">
    <button class="quote-action quote-action-strong" type="submit">Emitir cotizaci&oacute;n</button>
  </form>
  <?php endif; ?>
  <?php if (($quote['estado'] ?? null) === 'emitida' && trim((string) ($quote['numero_cotizacion'] ?? '')) !== ''): ?>
  <a class="quote-action quote-action-primary" href="cotizacion-imprimir.php?id=<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">Vista imprimible</a>
  <a class="quote-action quote-action-strong" href="cotizacion-pdf.php?id=<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">Descargar PDF</a>
  <?php endif; ?>
</div>
<?php if (($quote['estado'] ?? null) === 'borrador'): ?>
<section class="status-panel">
  <h3>Emisi&oacute;n oficial</h3>
  <p>Al emitir, se asignar&aacute; un n&uacute;mero oficial y la cotizaci&oacute;n dejar&aacute; de ser editable.</p>
</section>
<?php endif; ?>
<?php endif; ?>
<?php
    }
);

function normalizeFlashType(mixed $type): string
{
    if (!is_string($type)) {
        return 'info';
    }

    $type = strtolower(trim($type));
    $allowedTypes = ['success', 'error', 'warning', 'info'];

    return in_array($type, $allowedTypes, true) ? $type : 'info';
}

function flashTitle(string $type): string
{
    return match ($type) {
        'success' => 'Confirmación',
        'error' => 'Error',
        'warning' => 'Advertencia',
        default => 'Información',
    };
}
