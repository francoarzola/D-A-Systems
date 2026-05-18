<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Core;

final class SessionManager
{
    private string $name = 'DA_SYSTEMS_INTERNAL_SESSION';
    private string $path = '/sistema';
    private int $lifetime = 0; // session cookie lifetime (0 = until browser close)
    private bool $httponly = true;
    private string $sameSite = 'Lax';

    public function __construct()
    {
        // Constructor intentionally empty: no auto-start of session.
    }

    public function start(): void
    {
        if ($this->isStarted()) {
            return;
        }

        if (!headers_sent()) {
            session_name($this->name);

            $secure = $this->isHttps();

            // Use array-style options supported in PHP 7.3+ and PHP 8.3
            $params = [
                'lifetime' => $this->lifetime,
                'path' => $this->path,
                'domain' => '',
                'secure' => $secure,
                'httponly' => $this->httponly,
                'samesite' => $this->sameSite,
            ];

            // Only set cookie params if session not already active to avoid warnings
            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_set_cookie_params($params);
            }
        }

        // Start session if still not active
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function regenerate(): void
    {
        if ($this->isStarted()) {
            // regenerate session id and delete old
            session_regenerate_id(true);
        }
    }

    public function destroy(): void
    {
        if (!$this->isStarted()) {
            // Ensure session_name is reset so cookie deletion uses correct name
            session_name($this->name);
            // Nothing more to do
            return;
        }

        // Clear session data
        $_SESSION = [];

        // Delete session cookie
        $name = session_name();
        $params = session_get_cookie_params();

        // Use same path and secure settings as when started
        $secure = $this->isHttps();

        setcookie(
            $name,
            '',
            time() - 3600,
            $this->path ?: ($params['path'] ?? '/'),
            $params['domain'] ?? '',
            $secure,
            $this->httponly
        );

        // Destroy session data on server
        session_unset();
        session_destroy();
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->isStarted()) {
            return $default;
        }

        return $_SESSION[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if (!$this->isStarted()) {
            $this->start();
        }

        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        if (!$this->isStarted()) {
            return;
        }

        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function has(string $key): bool
    {
        if (!$this->isStarted()) {
            return false;
        }

        return array_key_exists($key, $_SESSION);
    }

    private function isHttps(): bool
    {
        if (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') {
            return true;
        }

        if (!empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }

        return false;
    }
}
