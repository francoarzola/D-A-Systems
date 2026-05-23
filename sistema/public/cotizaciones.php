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
  <p>Este módulo permitirá preparar, revisar y dar seguimiento a cotizaciones comerciales para clientes de D&A Systems.</p>
</section>

<section class="grid">
  <article class="card">
    <h2>Funcionalidades futuras</h2>
    <p>Listado de cotizaciones, creación de borradores, edición de cotizaciones abiertas, cálculo de totales y seguimiento de estados.</p>
  </article>
  <article class="card">
    <h2>Estados previstos</h2>
    <p>Borrador, emitida, enviada, aceptada, rechazada y anulada.</p>
  </article>
  <article class="card">
    <h2>Módulo en diseño funcional</h2>
    <p>Aún no existe CRUD, formularios, base de datos, generación de PDF ni envío por correo.</p>
  </article>
</section>

<section class="status-panel">
  <h3>Alcance inicial</h3>
  <p>La primera versión real deberá enfocarse en registrar una cotización simple con datos del cliente, ítems o servicios, totales, estado y observaciones comerciales.</p>
</section>
<?php
    }
);
