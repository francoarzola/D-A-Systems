<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';

use DAndASystems\Internal\Support\InternalPage;

ob_start();
?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>La gestión de cotizaciones será implementada en etapas posteriores. Esta página queda protegida y lista para integrarse al flujo interno.</p>
</section>
<?php
$content = ob_get_clean();

InternalPage::render(
    'Cotizaciones — Sistema interno D&A Systems',
    'Cotizaciones',
    'cotizaciones',
    $content
);
