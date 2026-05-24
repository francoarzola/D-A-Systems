<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Support\InternalPage;

InternalPage::render(
    'Detalle de cotización - Sistema interno D&A Systems',
    'Detalle de cotización',
    'cotizaciones',
    static function (): void {
        $quoteId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
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

                $quote = $repository->findById($quoteId);

                if ($quote === null) {
                    $errorMessage = 'No se encontró la cotización solicitada.';
                } else {
                    $details = $repository->findDetailsByQuoteId($quoteId);
                }
            } catch (\Throwable $exception) {
                $errorMessage = 'No fue posible cargar el detalle de la cotización.';
            }
        }
        ?>
<?php if ($errorMessage !== null): ?>
<section class="status-panel">
  <h3>Detalle no disponible</h3>
  <p><?php echo e($errorMessage); ?></p>
</section>

<div class="quote-actions">
  <a class="quote-action quote-action-muted" href="cotizaciones.php">Volver al listado</a>
</div>
<?php else: ?>
<section class="status-panel">
  <h3>Detalle de cotización</h3>
  <p><strong>Estado:</strong> <?php echo e(formatQuoteStatus($quote['estado'] ?? null)); ?></p>
  <p><strong>Número:</strong> <?php echo e(formatQuoteNumber($quote['numero_cotizacion'] ?? null)); ?></p>
</section>

<section class="grid">
  <article class="card">
    <h2>Datos generales</h2>
    <p><strong>Fecha:</strong> <?php echo e(formatQuoteDate($quote['fecha_cotizacion'] ?? null)); ?></p>
    <p><strong>Validez:</strong> <?php echo e(formatQuoteDate($quote['valido_hasta'] ?? null)); ?></p>
    <p><strong>Estado:</strong> <?php echo e(formatQuoteStatus($quote['estado'] ?? null)); ?></p>
    <p><strong>Total:</strong> <?php echo e(formatQuoteMoney($quote['total'] ?? null)); ?></p>
  </article>

  <article class="card">
    <h2>Cliente</h2>
    <p><strong>Nombre:</strong> <?php echo e(formatText($quote['nombre_cliente'] ?? null)); ?></p>
    <p><strong>RUT:</strong> <?php echo e(formatText($quote['rut_cliente'] ?? null)); ?></p>
    <p><strong>Contacto:</strong> <?php echo e(formatText($quote['nombre_contacto'] ?? null)); ?></p>
    <p><strong>Correo:</strong> <?php echo e(formatText($quote['correo_contacto'] ?? null)); ?></p>
    <p><strong>Teléfono:</strong> <?php echo e(formatText($quote['telefono_contacto'] ?? null)); ?></p>
  </article>

  <article class="card">
    <h2>Resumen</h2>
    <p><strong>Subtotal neto:</strong> <?php echo e(formatQuoteMoney($quote['subtotal_neto'] ?? null)); ?></p>
    <p><strong>Descuento:</strong> <?php echo e(formatQuoteMoney($quote['descuento_monto'] ?? null)); ?></p>
    <p><strong>IVA <?php echo e(formatPercent($quote['iva_porcentaje'] ?? null)); ?>:</strong> <?php echo e(formatQuoteMoney($quote['iva_monto'] ?? null)); ?></p>
    <p><strong>Total:</strong> <?php echo e(formatQuoteMoney($quote['total'] ?? null)); ?></p>
  </article>
</section>

<section class="grid">
  <article class="card quote-span-2">
    <h2>Descripción y condiciones</h2>
    <p><strong>Descripción:</strong> <?php echo e(formatText($quote['descripcion'] ?? null)); ?></p>
    <p><strong>Condiciones comerciales:</strong> <?php echo e(formatText($quote['condiciones_comerciales'] ?? null)); ?></p>
    <p><strong>Observaciones:</strong> <?php echo e(formatText($quote['observaciones'] ?? null)); ?></p>
  </article>

  <article class="card">
    <h2>Registro</h2>
    <p><strong>Creado:</strong> <?php echo e(formatText($quote['creado_en'] ?? null)); ?></p>
    <p><strong>Actualizado:</strong> <?php echo e(formatText($quote['actualizado_en'] ?? null)); ?></p>
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
        <td><?php echo e((string) ($detail['numero_linea'] ?? '')); ?></td>
        <td><?php echo e(formatText($detail['descripcion'] ?? null)); ?></td>
        <td><?php echo e(formatQuantity($detail['cantidad'] ?? null)); ?></td>
        <td><?php echo e(formatText($detail['unidad'] ?? null)); ?></td>
        <td class="quote-align-right"><?php echo e(formatQuoteMoney($detail['precio_unitario_neto'] ?? null)); ?></td>
        <td class="quote-align-right"><?php echo e(formatQuoteMoney($detail['descuento_monto'] ?? null)); ?></td>
        <td class="quote-align-right"><?php echo e(formatQuoteMoney($detail['total_linea_neto'] ?? null)); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>

<div class="quote-actions">
  <a class="quote-action quote-action-muted" href="cotizaciones.php">Volver al listado</a>
  <span class="quote-action quote-action-primary">Editar futuro</span>
  <span class="quote-action quote-action-strong">Emitir futuro</span>
</div>
<?php endif; ?>
<?php
    }
);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function formatQuoteNumber(mixed $value): string
{
    $number = is_string($value) ? trim($value) : '';

    return $number !== '' ? $number : 'Sin emitir';
}

function formatQuoteDate(mixed $value): string
{
    $date = is_string($value) ? trim($value) : '';

    return $date !== '' ? $date : 'Pendiente';
}

function formatQuoteStatus(mixed $value): string
{
    $status = is_string($value) ? trim($value) : '';

    return $status !== '' ? ucfirst($status) : 'Sin estado';
}

function formatQuoteMoney(mixed $value): string
{
    if (!is_numeric($value)) {
        return '$0';
    }

    return '$' . number_format((float) $value, 0, ',', '.');
}

function formatPercent(mixed $value): string
{
    if (!is_numeric($value)) {
        return '0%';
    }

    return number_format((float) $value, 2, ',', '.') . '%';
}

function formatQuantity(mixed $value): string
{
    if (!is_numeric($value)) {
        return '0';
    }

    return number_format((float) $value, 2, ',', '.');
}

function formatText(mixed $value): string
{
    $text = is_string($value) ? trim($value) : '';

    return $text !== '' ? $text : 'Pendiente';
}
