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

$pageTitle = 'Configuración — Sistema interno D&A Systems';
$pageHeading = 'Configuración';
$userName = $authUserName;
$activeNav = 'configuracion';

ob_start();
?>
<section class="status-panel">
  <h3>Módulo en preparación</h3>
  <p>Las opciones de configuración interna serán implementadas en etapas posteriores. Esta página queda disponible como base protegida.</p>
</section>
<?php
$content = ob_get_clean();

require __DIR__ . '/../app/Views/layouts/internal.php';
