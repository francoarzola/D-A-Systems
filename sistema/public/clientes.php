<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Support/InternalPage.php';

use DAndASystems\Internal\Support\InternalPage;

InternalPage::render(
    'Clientes — Sistema interno D&A Systems',
    'Clientes',
    'clientes',
    static function (): void {
        ?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>La administración de clientes será implementada en etapas posteriores. Esta página queda protegida y preparada para el módulo correspondiente.</p>
</section>
<?php
    }
);
