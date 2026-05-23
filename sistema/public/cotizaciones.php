<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';

use DAndASystems\Internal\Support\InternalPage;

InternalPage::render(
    'Cotizaciones — Sistema interno D&A Systems',
    'Cotizaciones',
    'cotizaciones',
    static function (): void {
        ?>
<section class="status-panel">
  <h3>Módulo de cotizaciones</h3>
  <p>Maqueta estática para validar el flujo visual del listado, estados y detalle de cotizaciones. Los datos mostrados son de ejemplo y no provienen de una base de datos.</p>
</section>

<section class="grid">
  <article class="card">
    <h2>Borrador</h2>
    <p>2 cotizaciones de ejemplo en preparación.</p>
  </article>
  <article class="card">
    <h2>Enviadas</h2>
    <p>1 cotización de ejemplo enviada a cliente.</p>
  </article>
  <article class="card">
    <h2>Aceptadas</h2>
    <p>1 cotización de ejemplo aceptada.</p>
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
  <p class="quote-section-copy">Datos de ejemplo para validar columnas, lectura y estados. Las acciones son solo referencias visuales.</p>
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
      <tr>
        <td>COT-2026-0001</td>
        <td>Comercial Los Andes</td>
        <td>2026-05-20</td>
        <td>2026-06-19</td>
        <td>Enviada</td>
        <td class="quote-align-right">$1.428.000</td>
        <td class="quote-visual-action">Ver maqueta</td>
      </tr>
      <tr>
        <td>Sin emitir</td>
        <td>Constructora Norte</td>
        <td>2026-05-22</td>
        <td>Pendiente</td>
        <td>Borrador</td>
        <td class="quote-align-right">$845.000</td>
        <td class="quote-visual-action">Ver maqueta</td>
      </tr>
      <tr>
        <td>COT-2026-0002</td>
        <td>Servicios Delta</td>
        <td>2026-05-23</td>
        <td>2026-06-22</td>
        <td>Aceptada</td>
        <td class="quote-align-right">$2.120.000</td>
        <td class="quote-visual-action">Ver maqueta</td>
      </tr>
    </tbody>
  </table>
</section>

<section class="grid">
  <article class="card quote-span-2">
    <h2>Detalle de cotización de ejemplo</h2>
    <p><strong>Número:</strong> COT-2026-0001</p>
    <p><strong>Cliente:</strong> Comercial Los Andes</p>
    <p><strong>Contacto:</strong> Paula Méndez — paula@example.test</p>
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
  <h2>Ítems de ejemplo</h2>
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
  <h3>Sin funcionalidad real todavía</h3>
  <p>Esta pantalla no consulta base de datos, no guarda información, no calcula totales, no genera PDF y no ejecuta acciones. Es solo una maqueta visual estática para revisar el flujo del módulo.</p>
</section>
<?php
    }
);
