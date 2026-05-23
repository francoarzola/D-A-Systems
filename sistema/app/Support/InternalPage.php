<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Support;

require_once __DIR__ . '/../Core/SessionManager.php';
require_once __DIR__ . '/../Core/AuthGuard.php';

use DAndASystems\Internal\Core\AuthGuard;
use DAndASystems\Internal\Core\SessionManager;

final class InternalPage
{
    public static function render(
        string $pageTitle,
        string $pageHeading,
        string $activeNav,
        callable $contentCallback,
        string $redirectTo = 'login.php'
    ): void {
        $session = new SessionManager();
        $session->start();

        $guard = new AuthGuard();
        $guard->requireAuth($redirectTo);

        $userName = $guard->userName() ?: 'Usuario';

        ob_start();
        $contentCallback();
        $content = ob_get_clean();

        require __DIR__ . '/../Views/layouts/internal.php';
    }
}
