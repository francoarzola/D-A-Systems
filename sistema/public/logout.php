<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';

use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Infrastructure\Config\DatabaseConfig;
use DAndASystems\Internal\Infrastructure\Database\Connection;

function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function hashValue(string $value): string
{
    return hash('sha256', $value);
}

function recordAuditLog(PDO $pdo, ?int $userId, string $event, ?int $entityId, array $metadata = []): void
{
    try {
        $metadataJson = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($metadataJson === false) {
            $metadataJson = '{}';
        }

        $statement = $pdo->prepare(
            'INSERT INTO audit_logs (user_id, event, entity_type, entity_id, ip_hash, user_agent_hash, metadata_json)
             VALUES (:user_id, :event, :entity_type, :entity_id, :ip_hash, :user_agent_hash, :metadata_json)'
        );

        $statement->execute([
            ':user_id' => $userId,
            ':event' => $event,
            ':entity_type' => 'auth',
            ':entity_id' => $entityId,
            ':ip_hash' => hashValue(getClientIp()),
            ':user_agent_hash' => hashValue($_SERVER['HTTP_USER_AGENT'] ?? 'unknown'),
            ':metadata_json' => $metadataJson,
        ]);
    } catch (\Throwable) {
        // Do not block logout if audit fails.
    }
}

$session = new SessionManager();
$session->start();
$userId = $session->has('auth_user_id') ? (int) $session->get('auth_user_id') : null;

if ($userId !== null) {
    try {
        $config = DatabaseConfig::fromDefaultPath()->load();
        $connection = new Connection($config);
        $pdo = $connection->pdo();
        recordAuditLog($pdo, $userId, 'logout', $userId, ['reason' => 'logout']);
    } catch (\Throwable) {
        // Do not expose logout failures.
    }
}

$session->destroy();

// Prefer redirect before any output
header('Location: login.php?logout=1');
exit;
