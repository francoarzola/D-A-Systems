<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Core;

final class AuthGuard
{
    private const TIMEOUT_SECONDS = 1800; // 30 minutes

    public function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $id = $_SESSION['auth_user_id'] ?? null;

        if ($id === null) {
            return false;
        }

        if (is_int($id)) {
            return true;
        }

        if (is_string($id) && ctype_digit($id)) {
            return true;
        }

        return false;
    }

    public function requireAuth(string $redirectTo = 'login.php'): void
    {
        if (!$this->isAuthenticated()) {
            header('Location: ' . $redirectTo);
            exit;
        }

        // If session expired due to inactivity, clear auth and redirect with timeout flag
        if ($this->hasSessionExpired()) {
            $this->clearAuthentication();
            header('Location: ' . $redirectTo . '?timeout=1');
            exit;
        }

        // Update last activity timestamp
        $this->touchActivity();
    }

    private function hasSessionExpired(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return false;
        }

        $last = $_SESSION['auth_last_activity_at'] ?? null;

        if ($last === null) {
            return false;
        }

        $lastInt = null;
        if (is_int($last)) {
            $lastInt = $last;
        } elseif (is_string($last) && ctype_digit($last)) {
            $lastInt = (int) $last;
        }

        if ($lastInt === null) {
            return false;
        }

        return (time() - $lastInt) > self::TIMEOUT_SECONDS;
    }

    private function touchActivity(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION['auth_last_activity_at'] = time();
    }

    private function clearAuthentication(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        // Remove only authentication-related session keys to avoid wiping unrelated session data
        $keys = [
            'auth_user_id',
            'auth_user_name',
            'auth_user_email',
            'auth_user_role',
            'auth_logged_in_at',
            'auth_last_activity_at',
        ];

        foreach ($keys as $k) {
            if (array_key_exists($k, $_SESSION)) {
                unset($_SESSION[$k]);
            }
        }

        // Additionally destroy session cookie/server data to ensure cleanup
        try {
            $sm = new SessionManager();
            $sm->destroy();
        } catch (\Throwable) {
            // If SessionManager is not available for any reason, fall back to clearing session data
            $_SESSION = [];
            if (function_exists('session_unset')) {
                @session_unset();
            }
            if (function_exists('session_destroy')) {
                @session_destroy();
            }
        }
    }

    public function user(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return [
                'id' => null,
                'name' => '',
                'email' => '',
                'role' => '',
                'logged_in_at' => '',
            ];
        }

        return [
            'id' => isset($_SESSION['auth_user_id']) ? (is_int($_SESSION['auth_user_id']) ? $_SESSION['auth_user_id'] : (ctype_digit((string) $_SESSION['auth_user_id']) ? (int) $_SESSION['auth_user_id'] : null)) : null,
            'name' => (string) ($_SESSION['auth_user_name'] ?? ''),
            'email' => (string) ($_SESSION['auth_user_email'] ?? ''),
            'role' => (string) ($_SESSION['auth_user_role'] ?? ''),
            'logged_in_at' => (string) ($_SESSION['auth_logged_in_at'] ?? ''),
        ];
    }

    public function userId(): ?int
    {
        $u = $this->user();
        return isset($u['id']) ? (is_int($u['id']) ? $u['id'] : (ctype_digit((string) $u['id']) ? (int) $u['id'] : null)) : null;
    }

    public function userName(): string
    {
        $u = $this->user();
        return $u['name'] ?? '';
    }

    public function userEmail(): string
    {
        $u = $this->user();
        return $u['email'] ?? '';
    }

    public function userRole(): string
    {
        $u = $this->user();
        return $u['role'] ?? '';
    }
}
