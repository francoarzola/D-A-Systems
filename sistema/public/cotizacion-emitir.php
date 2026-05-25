<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';
require_once __DIR__ . '/../app/Security/CsrfToken.php';
require_once __DIR__ . '/../app/Support/FlashMessage.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteNumberRepository.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Core\AuthGuard;
use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Security\CsrfToken;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Support\FlashMessage;

const QUOTE_ISSUE_CSRF_KEY = 'quote_issue';
const QUOTE_LIST_URL = 'cotizaciones.php';
const QUOTE_DETAIL_URL = 'cotizacion-detalle.php?id=';

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$flash = new FlashMessage();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $flash->set('error', 'La solicitud no es valida.');
    redirectTo(QUOTE_LIST_URL);
}

$quoteId = positiveIntFromPost('cotizacion_id');

if ($quoteId === null) {
    $flash->set('error', 'La cotizacion solicitada no es valida.');
    redirectTo(QUOTE_LIST_URL);
}

$csrf = new CsrfToken();
$csrfToken = postScalar('csrf_token');

if (!$csrf->validate($csrfToken, QUOTE_ISSUE_CSRF_KEY)) {
    $flash->set('error', 'La sesion del formulario expiro. Intente nuevamente.');
    redirectTo(QUOTE_DETAIL_URL . $quoteId);
}

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $repository = new QuoteRepository($connection->pdo());
    $service = new QuoteService($repository);
    $result = $service->issueDraft($quoteId);

    if ($result['success'] !== true) {
        $flash->set('warning', 'No fue posible emitir la cotizacion. Verifique que siga en estado borrador.');
        redirectTo(QUOTE_DETAIL_URL . $quoteId);
    }

    $flash->set('success', 'Cotizacion emitida correctamente.');
    redirectTo(QUOTE_DETAIL_URL . $quoteId);
} catch (\Throwable $exception) {
    $flash->set('error', 'No fue posible emitir la cotizacion.');
    redirectTo(QUOTE_DETAIL_URL . $quoteId);
}

function positiveIntFromPost(string $key): ?int
{
    $value = filter_var($_POST[$key] ?? null, FILTER_VALIDATE_INT);

    if (!is_int($value) || $value <= 0) {
        return null;
    }

    return $value;
}

function postScalar(string $key): ?string
{
    $value = $_POST[$key] ?? null;

    if ($value === null || !is_scalar($value)) {
        return null;
    }

    return trim((string) $value);
}

function redirectTo(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}
