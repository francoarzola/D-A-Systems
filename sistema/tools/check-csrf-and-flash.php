<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo 'Forbidden';
    exit(1);
}

require_once __DIR__ . '/../app/Security/CsrfToken.php';
require_once __DIR__ . '/../app/Support/FlashMessage.php';

use DAndASystems\Internal\Security\CsrfToken;
use DAndASystems\Internal\Support\FlashMessage;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_save_handler(
        static fn (string $path, string $name): bool => true,
        static fn (): bool => true,
        static fn (string $id): string => '',
        static fn (string $id, string $data): bool => true,
        static fn (string $id): bool => true,
        static fn (int $maxLifetime): int => 0
    );
    session_id('csrf-flash-check-' . bin2hex(random_bytes(8)));
    session_start();
}

try {
    $_SESSION = [];

    checkCsrfToken();
    checkFlashMessage();

    outputOk('CsrfToken y FlashMessage funcionan correctamente.');
    exit(0);
} catch (\Throwable $exception) {
    outputError('La verificación de CSRF y mensajes flash falló.');
    exit(1);
} finally {
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_abort();
    }
}

function checkCsrfToken(): void
{
    $csrf = new CsrfToken();
    $token = $csrf->generate('quote_draft');

    if ($token === '' || strlen($token) < 64) {
        throw new RuntimeException('No se generó un token seguro.');
    }

    if ($csrf->get('quote_draft') !== $token) {
        throw new RuntimeException('No se pudo recuperar el token generado.');
    }

    if ($csrf->generate('quote_draft') !== $token) {
        throw new RuntimeException('El token se regeneró sin necesidad.');
    }

    if (!$csrf->validate($token, 'quote_draft')) {
        throw new RuntimeException('El token correcto no fue validado.');
    }

    if ($csrf->validate('token_incorrecto', 'quote_draft')) {
        throw new RuntimeException('Un token incorrecto fue aceptado.');
    }

    $input = $csrf->inputField('quote_draft');

    if (!str_contains($input, 'type="hidden"')
        || !str_contains($input, 'name="csrf_token"')
        || !str_contains($input, htmlspecialchars($token, ENT_QUOTES, 'UTF-8'))) {
        throw new RuntimeException('El campo hidden CSRF no tiene el formato esperado.');
    }

    outputOk('CSRF generó, recuperó, validó y renderizó el input hidden.');
}

function checkFlashMessage(): void
{
    $flash = new FlashMessage();

    if ($flash->has()) {
        throw new RuntimeException('La sesión no debería tener mensaje flash inicial.');
    }

    $flash->set('success', 'Borrador guardado correctamente.');
    $message = $flash->get();

    if ($message === null || $message['type'] !== 'success' || $message['message'] !== 'Borrador guardado correctamente.') {
        throw new RuntimeException('No se pudo recuperar el mensaje flash.');
    }

    if (!$flash->has()) {
        throw new RuntimeException('El mensaje flash debería existir antes de pull.');
    }

    $pulled = $flash->pull();

    if ($pulled === null || $pulled['type'] !== 'success') {
        throw new RuntimeException('No se pudo consumir el mensaje flash.');
    }

    if ($flash->has() || $flash->get() !== null) {
        throw new RuntimeException('pull no eliminó el mensaje flash.');
    }

    $flash->set('tipo_desconocido', 'Mensaje informativo.');
    $fallback = $flash->pull();

    if ($fallback === null || $fallback['type'] !== 'info') {
        throw new RuntimeException('El tipo no permitido no cayó a info.');
    }

    outputOk('FlashMessage guardó, recuperó, consumió y normalizó mensajes.');
}

function outputOk(string $message): void
{
    echo '[OK] ' . $message . PHP_EOL;
}

function outputError(string $message): void
{
    echo '[ERROR] ' . $message . PHP_EOL;
}
