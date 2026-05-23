<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';

use DAndASystems\Internal\Support\InternalPage;

ob_start();
?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>Las opciones de configuración interna serán implementadas en etapas posteriores. Esta página queda disponible como base protegida.</p>
</section>
<?php
$content = ob_get_clean();

InternalPage::render(
    'Configuración — Sistema interno D&A Systems',
    'Configuración',
    'configuracion',
    $content
);
