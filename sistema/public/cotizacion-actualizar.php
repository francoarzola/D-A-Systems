<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/AuthGuard.php';
require_once __DIR__ . '/../app/Security/CsrfToken.php';
require_once __DIR__ . '/../app/Support/FlashMessage.php';
require_once __DIR__ . '/../app/Support/FormState.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';
require_once __DIR__ . '/../app/Repositories/QuoteRepository.php';
require_once __DIR__ . '/../app/Validation/QuoteDraftValidator.php';
require_once __DIR__ . '/../app/Services/QuoteTotalsCalculator.php';
require_once __DIR__ . '/../app/Services/QuoteService.php';

use DAndASystems\Internal\Core\AuthGuard;
use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;
use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Security\CsrfToken;
use DAndASystems\Internal\Services\QuoteService;
use DAndASystems\Internal\Services\QuoteTotalsCalculator;
use DAndASystems\Internal\Support\FlashMessage;
use DAndASystems\Internal\Support\FormState;
use DAndASystems\Internal\Validation\QuoteDraftValidator;

const QUOTE_DRAFT_EDIT_CSRF_KEY = 'quote_draft_edit';
const QUOTE_LIST_URL = 'cotizaciones.php';
const QUOTE_DETAIL_URL = 'cotizacion-detalle.php?id=';
const QUOTE_EDIT_URL = 'cotizacion-editar.php?id=';

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$flash = new FlashMessage();
$formState = new FormState();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    clearEditState($formState);
    $flash->set('error', 'La solicitud no es válida.');
    redirectTo(QUOTE_LIST_URL);
}

$quoteId = positiveIntFromPost('cotizacion_id');

if ($quoteId === null) {
    clearEditState($formState);
    $flash->set('error', 'La cotización solicitada no es válida.');
    redirectTo(QUOTE_LIST_URL);
}

$csrf = new CsrfToken();
$csrfToken = postScalar('csrf_token');

if (!$csrf->validate($csrfToken, QUOTE_DRAFT_EDIT_CSRF_KEY)) {
    clearEditState($formState);
    $flash->set('error', 'La sesión del formulario expiró. Intente nuevamente.');
    redirectTo(QUOTE_EDIT_URL . $quoteId);
}

$draftData = buildDraftDataFromPost($_POST);

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $repository = new QuoteRepository($connection->pdo());
    $service = new QuoteService($repository, new QuoteDraftValidator(), new QuoteTotalsCalculator());
    $result = $service->updateDraft($quoteId, $draftData);

    if ($result['success'] !== true) {
        $formState->set('quote_draft_edit', ['cotizacion_id' => $quoteId] + $draftData);
        $formState->set('quote_draft_edit_errors', validationErrorsFromResult($result['errors'] ?? []));
        $flash->set('warning', 'No fue posible actualizar el borrador. Revise los datos ingresados.');
        redirectTo(QUOTE_EDIT_URL . $quoteId);
    }

    clearEditState($formState);
    $flash->set('success', 'Borrador de cotización actualizado correctamente.');
    redirectTo(QUOTE_DETAIL_URL . $quoteId);
} catch (\Throwable $exception) {
    clearEditState($formState);
    $flash->set('error', 'No fue posible actualizar el borrador de cotización.');
    redirectTo(QUOTE_EDIT_URL . $quoteId);
}

function buildDraftDataFromPost(array $post): array
{
    return [
        'form_action' => scalarFromArray($post, 'form_action'),
        'nombre_cliente' => scalarFromArray($post, 'nombre_cliente'),
        'rut_cliente' => scalarFromArray($post, 'rut_cliente'),
        'nombre_contacto' => scalarFromArray($post, 'nombre_contacto'),
        'correo_contacto' => scalarFromArray($post, 'correo_contacto'),
        'telefono_contacto' => scalarFromArray($post, 'telefono_contacto'),
        'descripcion' => scalarFromArray($post, 'descripcion'),
        'fecha_cotizacion' => scalarFromArray($post, 'fecha_cotizacion'),
        'valido_hasta' => scalarFromArray($post, 'valido_hasta'),
        'condiciones_comerciales' => scalarFromArray($post, 'condiciones_comerciales'),
        'observaciones' => scalarFromArray($post, 'observaciones'),
        'detalles' => detailsFromPost($post['detalles'] ?? []),
    ];
}

function detailsFromPost(mixed $rawDetails): array
{
    if (!is_array($rawDetails)) {
        return [];
    }

    $details = [];

    foreach ($rawDetails as $rawDetail) {
        if (!is_array($rawDetail)) {
            continue;
        }

        $details[] = [
            'descripcion' => scalarFromArray($rawDetail, 'descripcion'),
            'cantidad' => scalarFromArray($rawDetail, 'cantidad'),
            'unidad' => scalarFromArray($rawDetail, 'unidad'),
            'precio_unitario_neto' => scalarFromArray($rawDetail, 'precio_unitario_neto'),
            'descuento_monto' => scalarFromArray($rawDetail, 'descuento_monto'),
        ];
    }

    return $details;
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
    return scalarFromArray($_POST, $key);
}

function validationErrorsFromResult(mixed $errors): array
{
    if (!is_array($errors)) {
        return [];
    }

    $messages = [];

    foreach ($errors as $error) {
        if (is_scalar($error)) {
            $messages[] = (string) $error;
        }
    }

    return $messages;
}

function scalarFromArray(array $source, string $key): ?string
{
    $value = $source[$key] ?? null;

    if ($value === null || !is_scalar($value)) {
        return null;
    }

    return trim((string) $value);
}

function clearEditState(FormState $formState): void
{
    $formState->clear('quote_draft_edit');
    $formState->clear('quote_draft_edit_errors');
}

function redirectTo(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}
