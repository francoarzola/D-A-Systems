<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';

use DAndASystems\Internal\Core\SessionManager;

$session = new SessionManager();
$session->start();
$session->destroy();

// Prefer redirect before any output
header('Location: login.php?logout=1');
exit;
