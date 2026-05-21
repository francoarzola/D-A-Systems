<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Core;

final class AuthGuard
{
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
