# Etapa 6D.12 — Rate limit de login usando login_attempts

## Objetivo

Mitigar ataques de fuerza bruta en el formulario de login interno implementando un rate limit basado en la tabla `login_attempts`.

## Política

- Límite máximo: 5 intentos fallidos
- Ventana de tiempo: 15 minutos
- Criterio de agrupación: combinación de `email_hash` + `ip_hash`

## Cómo funciona (resumen técnico)

- Antes de validar credenciales, se cuenta el número de intentos fallidos en los últimos 15 minutos para la combinación `email_hash` + `ip_hash`.
- `email_hash` se calcula como `hash('sha256', strtolower(trim($email)))`.
- `ip_hash` se calcula como `hash('sha256', $clientIp)` donde `$clientIp` es `$_SERVER['REMOTE_ADDR'] ?? 'unknown'`.
- `user_agent_hash` se calcula como `hash('sha256', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown')` y se registra para diagnóstico.
- Si hay 5 o más intentos fallidos en la ventana, se registra un nuevo intento fallido y se bloquea la petición con el mensaje genérico: `Demasiados intentos. Intenta nuevamente más tarde.`
- Si no está bloqueado, se procede a validar credenciales; en cada fallo se registra un intento fallido y en cada éxito se registra un intento con `success = 1`.

## SQL usado (concepto)

- Conteo de intentos recientes:

```
SELECT COUNT(*) FROM login_attempts
WHERE email_hash = :email_hash
  AND ip_hash = :ip_hash
  AND success = 0
  AND attempted_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)
```

- Registro de intentos (fallo o éxito):

```
INSERT INTO login_attempts (email_hash, ip_hash, success, user_agent_hash)
VALUES (:email_hash, :ip_hash, :success, :user_agent_hash)
```

> Todas las consultas usan `PDO` y `prepared statements`.

## Datos que se registran

- `email_hash` — hash SHA-256 del email normalizado
- `ip_hash` — hash SHA-256 de la IP cliente
- `success` — 0 o 1
- `user_agent_hash` — hash SHA-256 del User-Agent
- `attempted_at` — marca temporal (provista por la BD)

## Datos que NO se registran

- No se guarda el email en texto plano.
- No se guarda la IP en texto plano.
- No se guarda la contraseña ni el password_hash.

## Pruebas recomendadas

1. Prueba básica: login con email correcto y contraseña correcta → debe permitir acceso.
2. Prueba fallo: realizar 5 intentos fallidos con la misma combinación email+IP en menos de 15 minutos → en el 6º intento debe aparecer `Demasiados intentos...`.
3. Prueba ventana: tras 15 minutos desde los fallos, el contador debe expirar y permitir intentos nuevamente.
4. Prueba aislamiento: usar distinto IP o distinto email (hash distinto) debe mantener contadores separados.
5. Verificar que `login_attempts` contiene solo hashes y timestamps, no datos sensibles.

## Limitaciones

- El sistema usa `REMOTE_ADDR` como origen de la IP; en entornos detrás de proxies se recomienda ajustar la lógica para considerar `HTTP_X_FORWARDED_FOR` de forma controlada.
- El rate limit es por combinación `email_hash + ip_hash`; usuarios distribuidos por IP pueden eludirlo.
- No hay bloqueo de cuenta; solo limitación temporal por origen.

## Próximas etapas recomendadas

- 6D.13 — Auditoría de login/logout (audit logs)
- 6D.14 — AuthGuard reutilizable y verificación por roles
- 6D.15 — Timeout de sesión e inactividad
- 6D.16 — Mejorar la fuente de IP en entornos detrás de proxies

## Seguridad y privacidad

- Se utiliza hashing para no almacenar datos sensibles en texto plano.
- Las respuestas siguen siendo genéricas para evitar enumeración de usuarios.
- Las consultas usan `prepared statements` y los errores SQL no se exponen al usuario.

## Archivos modificados

- `sistema/public/login.php`
- `docs/sistema-interno/26-rate-limit-login.md`
