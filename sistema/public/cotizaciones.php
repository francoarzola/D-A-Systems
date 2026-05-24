<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';
require_once __DIR__ . '/../app/Support/ViewFormatter.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';

use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
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

        try {
            $config = DatabaseConfig::fromDefaultPath()->load();
            $connection = new Connection($config);
            $repository = new QuoteRepository($connection->pdo());

            $quoteCount = $repository->countAll();
            $recentQuotes = $repository->findRecent(10);
        } catch (\Throwable $exception) {
            $quotesLoadError = true;
        }
        ?>
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
      <tr>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteNumber($quote['numero_cotizacion'] ?? null)); ?></td>
        <td><?php echo ViewFormatter::e((string) ($quote['nombre_cliente'] ?? '')); ?></td>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['fecha_cotizacion'] ?? null)); ?></td>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteDate($quote['valido_hasta'] ?? null)); ?></td>
        <td><?php echo ViewFormatter::e(ViewFormatter::quoteStatus($quote['estado'] ?? null)); ?></td>
        <td class="quote-align-right"><?php echo ViewFormatter::e(ViewFormatter::money($quote['total'] ?? null)); ?></td>
        <td><a class="quote-visual-action" href="cotizacion-detalle.php?id=<?php echo ViewFormatter::e((string) ($quote['id'] ?? '')); ?>">Ver detalle</a></td>
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
