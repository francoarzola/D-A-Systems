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

$pageTitle = 'Atenciones — Sistema interno D&A Systems';
$pageHeading = 'Atenciones';
$userName = $authUserName;
$activeNav = 'atenciones';

ob_start();
?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>El registro y seguimiento de atenciones será implementado en etapas posteriores. Esta página base ya queda protegida dentro del sistema interno.</p>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../app/Views/layouts/internal.php';
