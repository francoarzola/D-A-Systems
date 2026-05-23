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

<section class="card" style="margin-top:28px;overflow-x:auto;">
  <h2>Listado de cotizaciones</h2>
  <p style="margin-bottom:18px;">Datos de ejemplo para validar columnas, lectura y estados. Las acciones son solo referencias visuales.</p>
  <table style="width:100%;border-collapse:collapse;min-width:760px;">
    <thead>
      <tr style="text-align:left;color:#526080;border-bottom:1px solid rgba(17,34,64,0.12);">
        <th style="padding:12px 10px;">Número</th>
        <th style="padding:12px 10px;">Cliente</th>
        <th style="padding:12px 10px;">Fecha</th>
        <th style="padding:12px 10px;">Validez</th>
        <th style="padding:12px 10px;">Estado</th>
        <th style="padding:12px 10px;text-align:right;">Total</th>
        <th style="padding:12px 10px;">Acción visual</th>
      </tr>
    </thead>
    <tbody>
      <tr style="border-bottom:1px solid rgba(17,34,64,0.08);">
        <td style="padding:14px 10px;">COT-2026-0001</td>
        <td style="padding:14px 10px;">Comercial Los Andes</td>
        <td style="padding:14px 10px;">2026-05-20</td>
        <td style="padding:14px 10px;">2026-06-19</td>
        <td style="padding:14px 10px;">Enviada</td>
        <td style="padding:14px 10px;text-align:right;">$1.428.000</td>
        <td style="padding:14px 10px;color:#1d75cf;font-weight:600;">Ver maqueta</td>
      </tr>
      <tr style="border-bottom:1px solid rgba(17,34,64,0.08);">
        <td style="padding:14px 10px;">Sin emitir</td>
        <td style="padding:14px 10px;">Constructora Norte</td>
        <td style="padding:14px 10px;">2026-05-22</td>
        <td style="padding:14px 10px;">Pendiente</td>
        <td style="padding:14px 10px;">Borrador</td>
        <td style="padding:14px 10px;text-align:right;">$845.000</td>
        <td style="padding:14px 10px;color:#1d75cf;font-weight:600;">Ver maqueta</td>
      </tr>
      <tr>
        <td style="padding:14px 10px;">COT-2026-0002</td>
        <td style="padding:14px 10px;">Servicios Delta</td>
        <td style="padding:14px 10px;">2026-05-23</td>
        <td style="padding:14px 10px;">2026-06-22</td>
        <td style="padding:14px 10px;">Aceptada</td>
        <td style="padding:14px 10px;text-align:right;">$2.120.000</td>
        <td style="padding:14px 10px;color:#1d75cf;font-weight:600;">Ver maqueta</td>
      </tr>
    </tbody>
  </table>
</section>

<section class="grid">
  <article class="card" style="grid-column:span 2;">
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

<section class="card" style="margin-top:28px;overflow-x:auto;">
  <h2>Ítems de ejemplo</h2>
  <table style="width:100%;border-collapse:collapse;min-width:680px;">
    <thead>
      <tr style="text-align:left;color:#526080;border-bottom:1px solid rgba(17,34,64,0.12);">
        <th style="padding:12px 10px;">Descripción</th>
        <th style="padding:12px 10px;">Cantidad</th>
        <th style="padding:12px 10px;">Unidad</th>
        <th style="padding:12px 10px;text-align:right;">Precio unitario</th>
        <th style="padding:12px 10px;text-align:right;">Total línea</th>
      </tr>
    </thead>
    <tbody>
      <tr style="border-bottom:1px solid rgba(17,34,64,0.08);">
        <td style="padding:14px 10px;">Servicio de soporte técnico mensual</td>
        <td style="padding:14px 10px;">1</td>
        <td style="padding:14px 10px;">mes</td>
        <td style="padding:14px 10px;text-align:right;">$650.000</td>
        <td style="padding:14px 10px;text-align:right;">$650.000</td>
      </tr>
      <tr>
        <td style="padding:14px 10px;">Implementación y configuración inicial</td>
        <td style="padding:14px 10px;">1</td>
        <td style="padding:14px 10px;">servicio</td>
        <td style="padding:14px 10px;text-align:right;">$550.000</td>
        <td style="padding:14px 10px;text-align:right;">$550.000</td>
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
