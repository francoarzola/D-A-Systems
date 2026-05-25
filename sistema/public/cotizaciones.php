<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Security/CsrfToken.php';
require_once __DIR__ . '/../app/Support/FlashMessage.php';
require_once __DIR__ . '/../app/Support/FormState.php';
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
use DAndASystems\Internal\Support\FormState;
use DAndASystems\Internal\Support\InternalPage;
use DAndASystems\Internal\Support\ViewFormatter;

InternalPage::render(
    'Cotizaciones - Sistema interno D&A Systems',
    'Cotizaciones',
    'cotizaciones',
    static function (): void {
        $quoteCount = 0;
        $recentQuotes = [];
        $quotesLoadError = false;
        $csrf = new CsrfToken();
        $flash = (new FlashMessage())->pull();
        $flashType = normalizeFlashType($flash['type'] ?? null);
        $formState = new FormState();
        $draftState = $formState->pull('quote_draft') ?? [];
        $draftErrors = normalizeFormErrors($formState->pull('quote_draft_errors') ?? []);
        $draftDetails = isset($draftState['detalles']) && is_array($draftState['detalles']) ? $draftState['detalles'] : [];
        $draftFirstDetail = isset($draftDetails[0]) && is_array($draftDetails[0]) ? $draftDetails[0] : [];
        $today = new DateTimeImmutable('today');
        $validUntil = $today->modify('+30 days');
        $quoteDateValue = formValue($draftState, 'fecha_cotizacion', $today->format('Y-m-d'));
        $validUntilValue = formValue($draftState, 'valido_hasta', $validUntil->format('Y-m-d'));
        $quantityValue = formValue($draftFirstDetail, 'cantidad', '1');
        $unitValue = formValue($draftFirstDetail, 'unidad', 'servicio');
        $lineDiscountValue = formValue($draftFirstDetail, 'descuento_monto', '0');

        try {
            $config = DatabaseConfig::fromDefaultPath()->load();
            $connection = new Connection($config);
            $repository = new QuoteRepository($connection->pdo());
            $service = new QuoteService($repository);

            $quoteCount = $service->countQuotes();
            $recentQuotes = $service->getRecentQuotes(10);
        } catch (\Throwable $exception) {
            $quotesLoadError = true;
        }
        ?>
<?php if ($flash !== null): ?>
<section class="flash-message flash-message-<?php echo ViewFormatter::e($flashType); ?>">
  <h3><?php echo ViewFormatter::e(flashTitle($flashType)); ?></h3>
  <p><?php echo ViewFormatter::e(ViewFormatter::text($flash['message'] ?? null)); ?></p>
</section>
<?php endif; ?>

<section class="status-panel">
  <h3>Módulo de cotizaciones</h3>
  <p>Listado conectado a lectura real de cotizaciones. La captura, edición, emisión y acciones comerciales siguen pendientes para etapas posteriores.</p>
</section>

<section class="grid">
  <article class="card">
    <h2>Total</h2>
    <?php if ($quotesLoadError): ?>
    <p>No disponible.</p>
    <?php else: ?>
    <p><?php echo ViewFormatter::e((string) $quoteCount); ?> cotizaciones registradas.</p>
    <?php endif; ?>
  </article>
  <article class="card">
    <h2>Lectura</h2>
    <p>Mostrando hasta 10 registros recientes desde la base de datos.</p>
  </article>
  <article class="card">
    <h2>Acciones</h2>
    <p>Las acciones visibles siguen siendo referencias no funcionales.</p>
  </article>
</section>

<section class="card quote-section">
  <h2>Crear borrador de cotización</h2>
  <p class="quote-section-copy">Formulario mínimo real para guardar un borrador con un detalle. Los totales se calculan en el servidor.</p>

<?php if ($draftErrors !== []): ?>
  <div class="form-error-summary">
    <h3>Revise los datos del formulario</h3>
    <ul>
      <?php foreach ($draftErrors as $draftError): ?>
      <li><?php echo ViewFormatter::e($draftError); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

  <form method="post" action="cotizaciones-guardar.php">
    <?php echo $csrf->inputField('quote_draft'); ?>
    <input type="hidden" name="form_action" value="guardar_borrador">

    <div class="grid quote-subgrid">
      <article class="card quote-nested-card">
        <h2>Datos generales</h2>
        <label class="quote-field<?php echo hasFieldError($draftErrors, 'fecha_cotizacion') ? ' field-has-error' : ''; ?>">
          <span class="quote-label">Fecha</span>
          <input class="quote-input" type="date" name="fecha_cotizacion" value="<?php echo ViewFormatter::e($quoteDateValue); ?>" required>
          <?php if (hasFieldError($draftErrors, 'fecha_cotizacion')): ?>
          <span class="field-error"><?php echo ViewFormatter::e(fieldError($draftErrors, 'fecha_cotizacion')); ?></span>
          <?php endif; ?>
        </label>
        <label class="quote-field<?php echo hasFieldError($draftErrors, 'valido_hasta') ? ' field-has-error' : ''; ?>">
          <span class="quote-label">Validez</span>
          <input class="quote-input" type="date" name="valido_hasta" value="<?php echo ViewFormatter::e($validUntilValue); ?>">
          <?php if (hasFieldError($draftErrors, 'valido_hasta')): ?>
          <span class="field-error"><?php echo ViewFormatter::e(fieldError($draftErrors, 'valido_hasta')); ?></span>
          <?php endif; ?>
        </label>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Descripción</span>
          <input class="quote-input" type="text" name="descripcion" value="<?php echo ViewFormatter::e(formValue($draftState, 'descripcion')); ?>" placeholder="Servicios a cotizar">
        </label>
      </article>

      <article class="card quote-nested-card">
        <h2>Cliente</h2>
        <label class="quote-field">
          <span class="quote-label">Razón social</span>
          <input class="quote-input" type="text" name="nombre_cliente" value="<?php echo ViewFormatter::e(formValue($draftState, 'nombre_cliente')); ?>" required>
          <?php if (hasFieldError($draftErrors, 'nombre_cliente')): ?>
          <span class="field-error"><?php echo ViewFormatter::e(fieldError($draftErrors, 'nombre_cliente')); ?></span>
          <?php endif; ?>
        </label>
        <label class="quote-field">
          <span class="quote-label">RUT</span>
          <input class="quote-input" type="text" name="rut_cliente" value="<?php echo ViewFormatter::e(formValue($draftState, 'rut_cliente')); ?>">
        </label>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Condiciones comerciales</span>
          <input class="quote-input" type="text" name="condiciones_comerciales" value="<?php echo ViewFormatter::e(formValue($draftState, 'condiciones_comerciales')); ?>" placeholder="Validez, forma de pago u observaciones comerciales">
        </label>
      </article>

      <article class="card quote-nested-card">
        <h2>Contacto</h2>
        <label class="quote-field">
          <span class="quote-label">Nombre</span>
          <input class="quote-input" type="text" name="nombre_contacto" value="<?php echo ViewFormatter::e(formValue($draftState, 'nombre_contacto')); ?>">
        </label>
        <label class="quote-field<?php echo hasFieldError($draftErrors, 'correo_contacto') ? ' field-has-error' : ''; ?>">
          <span class="quote-label">Correo</span>
          <input class="quote-input" type="email" name="correo_contacto" value="<?php echo ViewFormatter::e(formValue($draftState, 'correo_contacto')); ?>">
          <?php if (hasFieldError($draftErrors, 'correo_contacto')): ?>
          <span class="field-error"><?php echo ViewFormatter::e(fieldError($draftErrors, 'correo_contacto')); ?></span>
          <?php endif; ?>
        </label>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Teléfono</span>
          <input class="quote-input" type="text" name="telefono_contacto" value="<?php echo ViewFormatter::e(formValue($draftState, 'telefono_contacto')); ?>">
        </label>
      </article>
    </div>

    <div class="grid quote-subgrid-large">
      <article class="card quote-nested-card quote-span-2">
        <h2>Detalle</h2>
        <label class="quote-field">
          <span class="quote-label">Descripción del ítem</span>
          <input class="quote-input" type="text" name="detalles[0][descripcion]" value="<?php echo ViewFormatter::e(formValue($draftFirstDetail, 'descripcion')); ?>" required>
          <?php if (detailFieldError($draftErrors, 'descripcion') !== ''): ?>
          <span class="field-error"><?php echo ViewFormatter::e(detailFieldError($draftErrors, 'descripcion')); ?></span>
          <?php endif; ?>
        </label>
        <div class="grid quote-subgrid">
          <label class="quote-field">
            <span class="quote-label">Cantidad</span>
            <input class="quote-input" type="number" name="detalles[0][cantidad]" value="<?php echo ViewFormatter::e($quantityValue); ?>" min="0.01" step="0.01" required>
            <?php if (detailFieldError($draftErrors, 'cantidad') !== ''): ?>
            <span class="field-error"><?php echo ViewFormatter::e(detailFieldError($draftErrors, 'cantidad')); ?></span>
            <?php endif; ?>
          </label>
          <label class="quote-field">
            <span class="quote-label">Unidad</span>
            <input class="quote-input" type="text" name="detalles[0][unidad]" value="<?php echo ViewFormatter::e($unitValue); ?>">
          </label>
          <label class="quote-field">
            <span class="quote-label">Precio unitario neto</span>
            <input class="quote-input" type="number" name="detalles[0][precio_unitario_neto]" value="<?php echo ViewFormatter::e(formValue($draftFirstDetail, 'precio_unitario_neto')); ?>" min="0" step="1" required>
            <?php if (detailFieldError($draftErrors, 'precio_unitario_neto') !== ''): ?>
            <span class="field-error"><?php echo ViewFormatter::e(detailFieldError($draftErrors, 'precio_unitario_neto')); ?></span>
            <?php endif; ?>
          </label>
          <label class="quote-field quote-field-last">
            <span class="quote-label">Descuento línea</span>
            <input class="quote-input" type="number" name="detalles[0][descuento_monto]" value="<?php echo ViewFormatter::e($lineDiscountValue); ?>" min="0" step="1">
            <?php if (detailFieldError($draftErrors, 'descuento_monto') !== ''): ?>
            <span class="field-error"><?php echo ViewFormatter::e(detailFieldError($draftErrors, 'descuento_monto')); ?></span>
            <?php endif; ?>
          </label>
        </div>
      </article>

      <article class="card quote-nested-card">
        <h2>Observaciones</h2>
        <label class="quote-field quote-field-last">
          <span class="quote-label">Notas internas</span>
          <textarea class="quote-input" name="observaciones" rows="7" placeholder="Notas visibles en el borrador"><?php echo ViewFormatter::e(formValue($draftState, 'observaciones')); ?></textarea>
        </label>
      </article>
    </div>

    <div class="quote-actions">
      <button class="quote-action quote-action-primary" type="submit">Guardar borrador</button>
    </div>
  </form>
</section>

<section class="card quote-section">
  <h2>Nueva cotización</h2>
  <p>Maqueta visual de captura. Los campos están deshabilitados y los botones no ejecutan acciones.</p>

  <div class="grid quote-subgrid">
    <article class="card quote-nested-card">
      <h2>Datos generales</h2>
      <label class="quote-field">
        <span class="quote-label">Número</span>
        <input class="quote-input" type="text" value="Se asignará al emitir" disabled>
      </label>
      <label class="quote-field">
        <span class="quote-label">Fecha</span>
        <input class="quote-input" type="text" value="2026-05-23" disabled>
      </label>
      <label class="quote-field quote-field-last">
        <span class="quote-label">Validez</span>
        <input class="quote-input" type="text" value="30 días" disabled>
      </label>
    </article>

    <article class="card quote-nested-card">
      <h2>Cliente</h2>
      <label class="quote-field">
        <span class="quote-label">Razón social</span>
        <input class="quote-input" type="text" value="Cliente de ejemplo SpA" disabled>
      </label>
      <label class="quote-field">
        <span class="quote-label">RUT</span>
        <input class="quote-input" type="text" value="76.000.000-0" disabled>
      </label>
      <label class="quote-field quote-field-last">
        <span class="quote-label">Descripción general</span>
        <input class="quote-input" type="text" value="Servicios TI para operación interna" disabled>
      </label>
    </article>

    <article class="card quote-nested-card">
      <h2>Contacto</h2>
      <label class="quote-field">
        <span class="quote-label">Nombre</span>
        <input class="quote-input" type="text" value="Andrea Pérez" disabled>
      </label>
      <label class="quote-field">
        <span class="quote-label">Correo</span>
        <input class="quote-input" type="email" value="andrea@example.test" disabled>
      </label>
      <label class="quote-field quote-field-last">
        <span class="quote-label">Teléfono</span>
        <input class="quote-input" type="text" value="+56 9 0000 0000" disabled>
      </label>
    </article>
  </div>

  <div class="grid quote-subgrid-large">
    <article class="card quote-nested-card quote-span-2 quote-table-wrapper">
      <h2>Ítems por cotizar</h2>
      <table class="quote-table quote-table-compact">
        <thead>
          <tr>
            <th>Descripción</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th class="quote-align-right">Precio unitario</th>
            <th class="quote-align-right">Total línea</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Mesa de ayuda mensual</td>
            <td>1</td>
            <td>mes</td>
            <td class="quote-align-right">$480.000</td>
            <td class="quote-align-right">$480.000</td>
          </tr>
          <tr>
            <td>Configuración inicial</td>
            <td>1</td>
            <td>servicio</td>
            <td class="quote-align-right">$320.000</td>
            <td class="quote-align-right">$320.000</td>
          </tr>
        </tbody>
      </table>
    </article>

    <article class="card quote-nested-card">
      <h2>Resumen de totales</h2>
      <p><strong>Subtotal neto:</strong> $800.000</p>
      <p><strong>Descuento:</strong> $0</p>
      <p><strong>IVA 19%:</strong> $152.000</p>
      <p><strong>Total:</strong> $952.000</p>
    </article>
  </div>

  <section class="status-panel">
    <h3>Condiciones comerciales</h3>
    <p>Valores de ejemplo con validez de 30 días. Esta sección no guarda datos ni calcula totales.</p>
  </section>

  <div class="quote-actions">
    <span class="quote-action quote-action-primary">Guardar borrador</span>
    <span class="quote-action quote-action-strong">Emitir cotización</span>
    <span class="quote-action quote-action-muted">Cancelar</span>
  </div>
</section>

<section class="card quote-section quote-table-wrapper">
  <h2>Listado de cotizaciones</h2>
  <p class="quote-section-copy">Datos reales leídos desde la tabla cotizaciones. Las acciones son solo referencias visuales.</p>
  <?php if ($quotesLoadError): ?>
    <p class="quote-section-copy">No fue posible cargar el listado de cotizaciones.</p>
  <?php elseif ($recentQuotes === []): ?>
    <p class="quote-section-copy">Aún no hay cotizaciones registradas.</p>
  <?php else: ?>
  <table class="quote-table quote-table-list">
    <thead>
      <tr>
        <th>Número</th>
        <th>Cliente</th>
        <th>Fecha</th>
        <th>Validez</th>
        <th>Estado</th>
        <th class="quote-align-right">Total</th>
        <th>Acción visual</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($recentQuotes as $quote): ?>
      <?php
        $quoteId = (int) ($quote['id'] ?? 0);
        $isDraft = ($quote['estado'] ?? null) === 'borrador';
      ?>
      <tr>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteNumber($quote['numero_cotizacion'] ?? null)); ?></td>
        <td><?php echo ViewFormatter::e((string) ($quote['nombre_cliente'] ?? '')); ?></td>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['fecha_cotizacion'] ?? null)); ?></td>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['valido_hasta'] ?? null)); ?></td>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteStatus($quote['estado'] ?? null)); ?></td>
        <td class="quote-align-right"><?php echo ViewFormatter::e(ViewFormatter::money($quote['total'] ?? null)); ?></td>
        <td>
          <a class="quote-visual-action" href="cotizacion-detalle.php?id=<?php echo ViewFormatter::e((string) $quoteId); ?>">Ver detalle</a>
          <?php if ($isDraft && $quoteId > 0): ?>
          <a class="quote-visual-action" href="cotizacion-editar.php?id=<?php echo ViewFormatter::e((string) $quoteId); ?>">Editar</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</section>

<section class="grid">
  <article class="card quote-span-2">
    <h2>Vista de referencia: detalle de cotización</h2>
    <p><strong>Número:</strong> COT-2026-0001</p>
    <p><strong>Cliente:</strong> Comercial Los Andes</p>
    <p><strong>Contacto:</strong> Paula Méndez - paula@example.test</p>
    <p><strong>Estado:</strong> Enviada</p>
    <p><strong>Condiciones:</strong> Validez 30 días, valores netos sujetos a IVA.</p>
  </article>
  <article class="card">
    <h2>Resumen comercial</h2>
    <p><strong>Subtotal neto:</strong> $1.200.000</p>
    <p><strong>Descuento:</strong> $0</p>
    <p><strong>IVA 19%:</strong> $228.000</p>
    <p><strong>Total:</strong> $1.428.000</p>
  </article>
</section>

<section class="card quote-section quote-table-wrapper">
  <h2>Vista de referencia: ítems</h2>
  <table class="quote-table quote-table-compact">
    <thead>
      <tr>
        <th>Descripción</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th class="quote-align-right">Precio unitario</th>
        <th class="quote-align-right">Total línea</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Servicio de soporte técnico mensual</td>
        <td>1</td>
        <td>mes</td>
        <td class="quote-align-right">$650.000</td>
        <td class="quote-align-right">$650.000</td>
      </tr>
      <tr>
        <td>Implementación y configuración inicial</td>
        <td>1</td>
        <td>servicio</td>
        <td class="quote-align-right">$550.000</td>
        <td class="quote-align-right">$550.000</td>
      </tr>
    </tbody>
  </table>
</section>

<section class="status-panel">
  <h3>Solo lectura inicial</h3>
  <p>Esta pantalla lee el listado de cotizaciones, pero no guarda información, no calcula totales, no genera PDF y no ejecuta acciones. Las secciones de nueva cotización y detalle siguen siendo referencias visuales.</p>
</section>
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

function formValue(array $data, string $key, string $default = ''): string
{
    $value = $data[$key] ?? null;

    if ($value === null || !is_scalar($value)) {
        return $default;
    }

    return (string) $value;
}

function normalizeFormErrors(array $errors): array
{
    $messages = [];

    foreach ($errors as $error) {
        if (is_scalar($error)) {
            $message = trim((string) $error);

            if ($message !== '') {
                $messages[] = $message;
            }
        }
    }

    return $messages;
}

function fieldError(array $errors, string $key): string
{
    $patterns = [
        'nombre_cliente' => ['nombre del cliente'],
        'correo_contacto' => ['correo de contacto'],
        'fecha_cotizacion' => ['fecha de cotización'],
        'valido_hasta' => ['fecha de validez'],
    ];

    foreach ($patterns[$key] ?? [] as $pattern) {
        foreach ($errors as $error) {
            if (stripos($error, $pattern) !== false) {
                return $error;
            }
        }
    }

    return '';
}

function hasFieldError(array $errors, string $key): bool
{
    return fieldError($errors, $key) !== '';
}

function detailFieldError(array $errors, string $field): string
{
    $patterns = [
        'descripcion' => ['El detalle 1 requiere descripción', 'El detalle 1 debe ser una estructura válida'],
        'cantidad' => ['El detalle 1 requiere cantidad'],
        'precio_unitario_neto' => ['El detalle 1 requiere precio unitario neto'],
        'descuento_monto' => ['El detalle 1 requiere descuento', 'El detalle 1 no puede tener total de línea negativo'],
    ];

    foreach ($patterns[$field] ?? [] as $pattern) {
        foreach ($errors as $error) {
            if (stripos($error, $pattern) !== false) {
                return $error;
            }
        }
    }

    return '';
}
