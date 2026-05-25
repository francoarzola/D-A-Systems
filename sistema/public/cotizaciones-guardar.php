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

const QUOTE_DRAFT_CSRF_KEY = 'quote_draft';
const QUOTE_LIST_URL = 'cotizaciones.php';
const QUOTE_DETAIL_URL = 'cotizacion-detalle.php?id=';

$session = new SessionManager();
$session->start();

$guard = new AuthGuard();
$guard->requireAuth('login.php');

$flash = new FlashMessage();
$formState = new FormState();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    $formState->clear('quote_draft');
    $flash->set('error', 'La solicitud no es válida.');
    redirectTo(QUOTE_LIST_URL);
}

$csrf = new CsrfToken();
$csrfToken = postScalar('csrf_token');

if (!$csrf->validate($csrfToken, QUOTE_DRAFT_CSRF_KEY)) {
    $formState->clear('quote_draft');
    $flash->set('error', 'La sesión del formulario expiró. Intente nuevamente.');
    redirectTo(QUOTE_LIST_URL);
}

$draftData = buildDraftDataFromPost($_POST);

try {
    $config = DatabaseConfig::fromDefaultPath()->load();
    $connection = new Connection($config);
    $repository = new QuoteRepository($connection->pdo());
    $service = new QuoteService($repository, new QuoteDraftValidator(), new QuoteTotalsCalculator());
    $result = $service->createDraft($draftData, $guard->userId());

    if ($result['success'] !== true || !is_int($result['quote_id'])) {
        $formState->set('quote_draft', $draftData);
        $flash->set('warning', 'No fue posible guardar el borrador. Revise los datos ingresados.');
        redirectTo(QUOTE_LIST_URL);
    }

    $formState->clear('quote_draft');
    $flash->set('success', 'Borrador de cotización guardado correctamente.');
    redirectTo(QUOTE_DETAIL_URL . $result['quote_id']);
} catch (\Throwable $exception) {
    $formState->clear('quote_draft');
    $flash->set('error', 'No fue posible guardar el borrador de cotización.');
    redirectTo(QUOTE_LIST_URL);
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

function postScalar(string $key): ?string
{
    return scalarFromArray($_POST, $key);
}

function scalarFromArray(array $source, string $key): ?string
{
    $value = $source[$key] ?? null;

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
