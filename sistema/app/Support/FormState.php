<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Support;

final class FormState
{
    private const SESSION_KEY = '_internal_form_state';

    public function set(string $key, array $data): void
    {
        $key = $this->normalizeKey($key);
        $this->ensureContainer();

        $_SESSION[self::SESSION_KEY][$key] = $data;
    }

    public function get(string $key): ?array
    {
        $key = $this->normalizeKey($key);
        $states = $_SESSION[self::SESSION_KEY] ?? [];

        if (!is_array($states)) {
            return null;
        }

        $state = $states[$key] ?? null;

        return is_array($state) ? $state : null;
    }

    public function pull(string $key): ?array
    {
        $key = $this->normalizeKey($key);
        $state = $this->get($key);
        $this->clear($key);

        return $state;
    }

    public function clear(string $key): void
    {
        $key = $this->normalizeKey($key);

        if (isset($_SESSION[self::SESSION_KEY]) && is_array($_SESSION[self::SESSION_KEY])) {
            unset($_SESSION[self::SESSION_KEY][$key]);
        }
    }

    private function ensureContainer(): void
    {
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }
    }

    private function normalizeKey(string $key): string
    {
        $key = trim($key);

        return $key !== '' ? $key : 'default';
    }
}
