<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';

use DAndASystems\Internal\Support\InternalPage;

InternalPage::render(
    'Atenciones — Sistema interno D&A Systems',
    'Atenciones',
    'atenciones',
    static function (): void {
        ?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>El registro y seguimiento de atenciones será implementado en etapas posteriores. Esta página base ya queda protegida dentro del sistema interno.</p>
</section>
<?php
    }
);
