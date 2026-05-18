<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Core;

final class CsrfService
{
    private const SESSION_KEY = '_csrf_token';

    private SessionManager $session;

    public function __construct(SessionManager $session)
    {
        $this->session = $session;
    }

    public function token(): string
    {
        $this->session->start();

        $existing = $this->session->get(self::SESSION_KEY);
        if (is_string($existing) && $existing !== '') {
            return $existing;
        }

        $token = bin2hex(random_bytes(32));
        $this->session->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function validate(?string $token): bool
    {
        $this->session->start();

        if (!is_string($token) || $token === '') {
            return false;
        }

        $stored = $this->session->get(self::SESSION_KEY);
        if (!is_string($stored) || $stored === '') {
            return false;
        }

        // Use hash_equals to mitigate timing attacks
        return hash_equals($stored, $token);
    }

    public function rotate(): string
    {
        $this->session->start();

        $token = bin2hex(random_bytes(32));
        $this->session->set(self::SESSION_KEY, $token);

        return $token;
    }

    public function clear(): void
    {
        $this->session->start();
        $this->session->remove(self::SESSION_KEY);
    }
}
