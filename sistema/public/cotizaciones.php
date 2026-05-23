<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';

use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Core\AuthGuard;

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$authUserName = $guard->userName() ?: 'Usuario';

$pageTitle = 'Cotizaciones — Sistema interno D&A Systems';
$pageHeading = 'Cotizaciones';
$userName = $authUserName;
$activeNav = 'cotizaciones';

ob_start();
?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>La gestión de cotizaciones será implementada en etapas posteriores. Esta página queda protegida y lista para integrarse al flujo interno.</p>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../app/Views/layouts/internal.php';
