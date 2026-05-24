<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Security;

final class CsrfToken
{
    private const SESSION_KEY = '_internal_csrf_tokens';

    public function generate(string $key = 'default'): string
    {
        $key = $this->normalizeKey($key);
        $existing = $this->get($key);

        if ($existing !== null) {
            return $existing;
        }

        $token = bin2hex(random_bytes(32));
        $_SESSION[self::SESSION_KEY][$key] = $token;

        return $token;
    }

    public function get(string $key = 'default'): ?string
    {
        $key = $this->normalizeKey($key);
        $tokens = $_SESSION[self::SESSION_KEY] ?? [];

        if (!is_array($tokens)) {
            return null;
        }

        $token = $tokens[$key] ?? null;

        return is_string($token) && $token !== '' ? $token : null;
    }

    public function validate(?string $token, string $key = 'default'): bool
    {
        if (!is_string($token) || $token === '') {
            return false;
        }

        $stored = $this->get($key);

        if ($stored === null) {
            return false;
        }

        return hash_equals($stored, $token);
    }

    public function inputField(string $key = 'default'): string
    {
        $token = htmlspecialchars($this->generate($key), ENT_QUOTES, 'UTF-8');

        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    private function normalizeKey(string $key): string
    {
        $key = trim($key);

        return $key !== '' ? $key : 'default';
    }
}
