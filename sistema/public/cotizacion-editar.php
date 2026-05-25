<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Security/CsrfToken.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Security\CsrfToken;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\InternalPage;
use DAndASystems\Internal\Support\ViewFormatter;

// InternalPage inicia sesión y protege la página con AuthGuard::requireAuth().
InternalPage::render(
    'Editar borrador de cotización - Sistema interno D&A Systems',
    'Editar borrador de cotización',
    'cotizaciones',
    static function (): void {
        $quoteId = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        $quote = null;
        $details = [];
        $errorMessage = null;
        $csrf = new CsrfToken();

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
                } elseif (!isDraftQuote($quoteDetail['quote'])) {
                    $errorMessage = 'Solo las cotizaciones en estado borrador pueden abrirse en modo edición.';
                } else {
                    $quote = $quoteDetail['quote'];
                    $details = $quoteDetail['details'];
                }
            } catch (\Throwable $exception) {
                $errorMessage = 'No fue posible cargar el borrador de cotización.';
            }
        }

        $firstDetail = isset($details[0]) && is_array($details[0]) ? $details[0] : [];
        ?>
<?php if ($errorMessage !== null): ?>
<section class="status-panel">
  <h3>Edición no disponible</h3>
  <p><?php echo ViewFormatter::e($errorMessage); ?></p>
</section>

<div class="quote-actions">
  <a class="quote-action quote-action-muted" href="cotizaciones.php">Volver al listado</a>
  <?php if (is_int($quoteId) && $quoteId > 0): ?>
  <a class="quote-action quote-action-muted" href="cotizacion-detalle.php?id=<?php echo ViewFormatter::e((string) $quoteId); ?>">Ver detalle</a>
  <?php endif; ?>
</div>
<?php else: ?>
<section class="status-panel">
  <h3>Preparación de edición</h3>
  <p>Este formulario carga datos reales del borrador, pero todavía no guarda cambios. La actualización se implementará en una etapa posterior.</p>
</section>

<section class="card quote-section">
  <h2>Borrador en edición</h2>
  <p class="quote-section-copy">Número: <?php echo ViewFormatter::e(ViewFormatter::quoteNumber($quote['numero_cotizacion'] ?? null)); ?> · Estado: <?php echo ViewFormatter::e(ViewFormatter::quoteStatus($quote['estado'] ?? null)); ?></p>

  <form method="post" action="cotizacion-actualizar.php">
    <?php echo $csrf->inputField('quote_draft_edit'); ?>
    <input type="hidden" name="cotizacion_id" value="<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">

    <div class="grid quote-subgrid">
      <article class="card quote-nested-card">
        <h2>Datos generales</h2>
        <label class="quote-field">
          <span class="quote-label">Fecha</span>
          <input class="quote-input" type="date" name="fecha_cotizacion" value="<?php echo ViewFormatter::e(editValue($quote, 'fecha_cotizacion')); ?>">
        </label>
        <label class="quote-field">
          <span class="quote-label">Validez</span>
          <input class="quote-input" type="date" name="valido_hasta" value="<?php echo ViewFormatter::e(editValue($quote, 'valido_hasta')); ?>">
        </label>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Descripción</span>
          <input class="quote-input" type="text" name="descripcion" value="<?php echo ViewFormatter::e(editValue($quote, 'descripcion')); ?>">
        </label>
      </article>

      <article class="card quote-nested-card">
        <h2>Cliente</h2>
        <label class="quote-field">
          <span class="quote-label">Razón social</span>
          <input class="quote-input" type="text" name="nombre_cliente" value="<?php echo ViewFormatter::e(editValue($quote, 'nombre_cliente')); ?>">
        </label>
        <label class="quote-field">
          <span class="quote-label">RUT</span>
          <input class="quote-input" type="text" name="rut_cliente" value="<?php echo ViewFormatter::e(editValue($quote, 'rut_cliente')); ?>">
        </label>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Condiciones comerciales</span>
          <input class="quote-input" type="text" name="condiciones_comerciales" value="<?php echo ViewFormatter::e(editValue($quote, 'condiciones_comerciales')); ?>">
        </label>
      </article>

      <article class="card quote-nested-card">
        <h2>Contacto</h2>
        <label class="quote-field">
          <span class="quote-label">Nombre</span>
          <input class="quote-input" type="text" name="nombre_contacto" value="<?php echo ViewFormatter::e(editValue($quote, 'nombre_contacto')); ?>">
        </label>
        <label class="quote-field">
          <span class="quote-label">Correo</span>
          <input class="quote-input" type="email" name="correo_contacto" value="<?php echo ViewFormatter::e(editValue($quote, 'correo_contacto')); ?>">
        </label>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Teléfono</span>
          <input class="quote-input" type="text" name="telefono_contacto" value="<?php echo ViewFormatter::e(editValue($quote, 'telefono_contacto')); ?>">
        </label>
      </article>
    </div>

    <div class="grid quote-subgrid-large">
      <article class="card quote-nested-card quote-span-2">
        <h2>Primer detalle</h2>
        <label class="quote-field">
          <span class="quote-label">Descripción del ítem</span>
          <input class="quote-input" type="text" name="detalles[0][descripcion]" value="<?php echo ViewFormatter::e(editValue($firstDetail, 'descripcion')); ?>">
        </label>
        <div class="grid quote-subgrid">
          <label class="quote-field">
            <span class="quote-label">Cantidad</span>
            <input class="quote-input" type="number" name="detalles[0][cantidad]" value="<?php echo ViewFormatter::e(editValue($firstDetail, 'cantidad')); ?>" step="0.01">
          </label>
          <label class="quote-field">
            <span class="quote-label">Unidad</span>
            <input class="quote-input" type="text" name="detalles[0][unidad]" value="<?php echo ViewFormatter::e(editValue($firstDetail, 'unidad')); ?>">
          </label>
          <label class="quote-field">
            <span class="quote-label">Precio unitario neto</span>
            <input class="quote-input" type="number" name="detalles[0][precio_unitario_neto]" value="<?php echo ViewFormatter::e(editValue($firstDetail, 'precio_unitario_neto')); ?>" step="1">
          </label>
          <label class="quote-field quote-field-last">
            <span class="quote-label">Descuento línea</span>
            <input class="quote-input" type="number" name="detalles[0][descuento_monto]" value="<?php echo ViewFormatter::e(editValue($firstDetail, 'descuento_monto')); ?>" step="1">
          </label>
        </div>
      </article>

      <article class="card quote-nested-card">
        <h2>Observaciones</h2>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Notas internas</span>
          <textarea class="quote-input" name="observaciones" rows="7"><?php echo ViewFormatter::e(editValue($quote, 'observaciones')); ?></textarea>
        </label>
      </article>
    </div>

    <div class="quote-actions">
      <button class="quote-action quote-action-muted" type="button" disabled>Guardar cambios próximamente</button>
      <a class="quote-action quote-action-muted" href="cotizacion-detalle.php?id=<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">Ver detalle</a>
      <a class="quote-action quote-action-muted" href="cotizaciones.php">Volver al listado</a>
    </div>
  </form>
</section>
<?php endif; ?>
<?php
    }
);

function isDraftQuote(array $quote): bool
{
    return ($quote['estado'] ?? null) === 'borrador';
}

function editValue(array $data, string $key): string
{
    $value = $data[$key] ?? null;

    if ($value === null || !is_scalar($value)) {
        return '';
    }

    return (string) $value;
}
