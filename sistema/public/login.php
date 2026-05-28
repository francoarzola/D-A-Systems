<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/Core/SessionManager.php';
require_once __DIR__ . '/../app/Core/CsrfService.php';
require_once __DIR__ . '/../app/Infrastructure/Config/DatabaseConfig.php';
require_once __DIR__ . '/../app/Infrastructure/Database/Connection.php';

use DAndASystems\Internal\Core\SessionManager;
use DAndASystems\Internal\Core\CsrfService;
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
        // Audit failures must not break the login flow.
    }
}

$session = new SessionManager();
$csrf = new CsrfService($session);

$session->start();

if ($session->has('auth_user_id')) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$emailValue = '';
$isPost = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST';

if ($isPost) {
    $csrfToken = $_POST['csrf_token'] ?? null;
    $validToken = $csrf->validate(is_string($csrfToken) ? $csrfToken : null);

    if (!$validToken) {
        $message = 'No fue posible procesar la solicitud.';
    } else {
        $emailValue = trim((string) ($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if ($emailValue === '' || filter_var($emailValue, FILTER_VALIDATE_EMAIL) === false) {
            $message = 'Credenciales inválidas.';
        } elseif (!is_string($password) || $password === '') {
            $message = 'Credenciales inválidas.';
        } else {
            try {
              $config = DatabaseConfig::fromDefaultPath()->load();
              $connection = new Connection($config);
              $pdo = $connection->pdo();

              // Rate limit keys (store only hashes)
              $emailHash = hashValue(strtolower(trim($emailValue)));
              $ipHash = hashValue(getClientIp());
              $userAgentHash = hashValue($_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

              // Count recent failed attempts in the last 15 minutes
              $countStmt = $pdo->prepare(
                'SELECT COUNT(*) FROM login_attempts WHERE email_hash = :email_hash AND ip_hash = :ip_hash AND success = 0 AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)'
              );
              $countStmt->execute([
                ':email_hash' => $emailHash,
                ':ip_hash' => $ipHash,
              ]);
              $failedCount = (int) $countStmt->fetchColumn();

              if ($failedCount >= 5) {
                // Register another failed attempt and block without checking credentials
                $insertBlock = $pdo->prepare(
                  'INSERT INTO login_attempts (email_hash, ip_hash, success, user_agent_hash) VALUES (:email_hash, :ip_hash, 0, :user_agent_hash)'
                );
                $insertBlock->execute([
                  ':email_hash' => $emailHash,
                  ':ip_hash' => $ipHash,
                  ':user_agent_hash' => $userAgentHash,
                ]);

                recordAuditLog($pdo, null, 'login_blocked_rate_limit', null, ['reason' => 'rate_limited']);
                $message = 'Demasiados intentos. Intenta nuevamente más tarde.';
              } else {
                // Proceed to validate credentials
                $statement = $pdo->prepare(
                  'SELECT id, name, email, password_hash, role, active FROM users WHERE email = :email LIMIT 1'
                );
                $statement->execute([':email' => $emailValue]);
                $user = $statement->fetch(\PDO::FETCH_ASSOC);

                $loginSuccess = false;
                $auditUserId = null;
                $auditReason = 'invalid_credentials';

                if (is_array($user) && isset($user['id'])) {
                  $auditUserId = (int) $user['id'];
                }

                if (is_array($user)
                  && isset($user['password_hash'], $user['active'])
                  && (int) $user['active'] === 1
                  && password_verify($password, (string) $user['password_hash'])
                ) {
                  $loginSuccess = true;
                }

                if (is_array($user) && isset($user['active']) && (int) $user['active'] !== 1) {
                  $auditReason = 'inactive_user';
                }

                if (!$loginSuccess) {
                  // Register failed attempt
                  $insertFail = $pdo->prepare(
                    'INSERT INTO login_attempts (email_hash, ip_hash, success, user_agent_hash) VALUES (:email_hash, :ip_hash, 0, :user_agent_hash)'
                  );
                  $insertFail->execute([
                    ':email_hash' => $emailHash,
                    ':ip_hash' => $ipHash,
                    ':user_agent_hash' => $userAgentHash,
                  ]);

                  recordAuditLog($pdo, $auditUserId, 'login_failed', $auditUserId, ['reason' => $auditReason]);
                  $message = 'Credenciales inválidas.';
                } else {
                  // Register successful attempt
                  $insertSuccess = $pdo->prepare(
                    'INSERT INTO login_attempts (email_hash, ip_hash, success, user_agent_hash) VALUES (:email_hash, :ip_hash, 1, :user_agent_hash)'
                  );
                  $insertSuccess->execute([
                    ':email_hash' => $emailHash,
                    ':ip_hash' => $ipHash,
                    ':user_agent_hash' => $userAgentHash,
                  ]);

                  recordAuditLog($pdo, $auditUserId, 'login_success', $auditUserId, ['reason' => 'authenticated']);

                  // Complete login
                  $session->regenerate();
                  $session->set('auth_user_id', (int) $user['id']);
                  $session->set('auth_user_name', (string) $user['name']);
                  $session->set('auth_user_email', (string) $user['email']);
                  $session->set('auth_user_role', (string) $user['role']);
                  $session->set('auth_logged_in_at', (new \DateTimeImmutable())->format('c'));
                  $csrf->rotate();

                  $updateStatement = $pdo->prepare(
                    'UPDATE users SET last_login_at = :last_login_at WHERE id = :id'
                  );
                  $updateStatement->execute([
                    ':last_login_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                    ':id' => (int) $user['id'],
                  ]);

                  header('Location: dashboard.php');
                  exit;
                }
              }
            } catch (Throwable) {
              $message = 'No fue posible procesar la solicitud.';
            }
        }
    }
}

$token = $csrf->token();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Sistema interno D&A Systems</title>
  <link rel="stylesheet" href="assets/css/internal.css">
</head>
<body>
  <div class="container">
    <section class="header">
      <div class="brand">
        <span class="brand-dot"></span>
        D&A Systems
      </div>
      <h1 class="title">Acceso al sistema interno</h1>
      <p class="subtitle">Ingrese con credenciales válidas para acceder al dashboard.</p>
    </section>

    <?php if ($message !== ''): ?>
      <div class="status-panel" role="status">
        <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
      </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="card auth-card">
      <label for="email">Email</label>
      <input id="email" name="email" type="email" required placeholder="usuario@empresa.cl" value="<?php echo htmlspecialchars($emailValue, ENT_QUOTES, 'UTF-8'); ?>" />

      <label for="password" class="auth-field-spaced">Contraseña</label>
      <input id="password" name="password" type="password" required placeholder="Contraseña" />

      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>" />

      <div class="button-wrapper">
        <button class="button-primary" type="submit">Acceder</button>
      </div>
    </form>

    <footer>
      <small>Acceso solo para personal autorizado.</small>
    </footer>
  </div>
</body>
</html>
