<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';

use DAndASystems\Internal\Support\InternalPage;

ob_start();
?>
<section class="grid">
  <article class="card">
    <h2>Cotizaciones</h2>
    <p>Resumen de cotizaciones y accesos al módulo prioritario.</p>
  </article>
  <article class="card">
    <h2>Clientes</h2>
    <p>Acceso al listado y gestión de clientes.</p>
  </article>
  <article class="card">
    <h2>Atenciones</h2>
    <p>Registro y seguimiento de atenciones técnicas.</p>
  </article>
</section>
<?php
$content = ob_get_clean();

InternalPage::render(
    'Dashboard — Sistema interno D&A Systems',
    'Dashboard interno',
    'dashboard',
    $content
);
