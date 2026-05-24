<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Support;

final class FlashMessage
{
    private const SESSION_KEY = '_internal_flash_message';
    private const ALLOWED_TYPES = ['success', 'error', 'warning', 'info'];

    public function set(string $type, string $message): void
    {
        $_SESSION[self::SESSION_KEY] = [
            'type' => $this->normalizeType($type),
            'message' => trim($message),
        ];
    }

    public function get(): ?array
    {
        $flash = $_SESSION[self::SESSION_KEY] ?? null;

        if (!is_array($flash)) {
            return null;
        }

        $type = $flash['type'] ?? 'info';
        $message = $flash['message'] ?? '';

        if (!is_string($type) || !is_string($message)) {
            return null;
        }

        return [
            'type' => $this->normalizeType($type),
            'message' => $message,
        ];
    }

    public function pull(): ?array
    {
        $flash = $this->get();
        unset($_SESSION[self::SESSION_KEY]);

        return $flash;
    }

    public function has(): bool
    {
        return $this->get() !== null;
    }

    private function normalizeType(string $type): string
    {
        $type = strtolower(trim($type));

        return in_array($type, self::ALLOWED_TYPES, true) ? $type : 'info';
    }
}
