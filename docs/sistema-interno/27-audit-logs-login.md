# Etapa 6D.13 — Auditoría de eventos de autenticación (audit logs)

## Objetivo

Implementar registros de auditoría básicos para eventos de autenticación del sistema interno usando la tabla `audit_logs`, sin exponer datos sensibles.

## Archivos modificados

- `sistema/public/login.php`
- `sistema/public/logout.php`
- `docs/sistema-interno/27-audit-logs-login.md`

## Tabla usada

- `audit_logs`

## Eventos registrados

- `login_success`
- `login_failed`
- `login_blocked_rate_limit`
- `logout`

## Datos guardados

- `user_id` — id del usuario cuando se conoce
- `event` — nombre del evento
- `entity_type` — siempre `auth`
- `entity_id` — id del usuario cuando aplica, `NULL` si no aplica
- `ip_hash` — SHA-256 de `REMOTE_ADDR` o hash de `unknown`
- `user_agent_hash` — SHA-256 de `HTTP_USER_AGENT` o hash de `unknown`
- `metadata_json` — JSON mínimo con razón del evento:
  - `{"reason":"invalid_credentials"}`
  - `{"reason":"inactive_user"}`
  - `{"reason":"rate_limited"}`
  - `{"reason":"authenticated"}`
  - `{"reason":"logout"}`

## Datos que NO se guardan

- correo electrónico completo
- IP completa
- contraseña
- `password_hash`
- token CSRF

## Por qué se usa hash de IP y user agent

- El hash de IP protege la privacidad del cliente y reduce el riesgo de exposición de datos sensibles.
- El hash de user agent permite análisis y correlación sin guardar el valor completo del encabezado.

## Por qué no se guarda correo completo

- Guardar el correo completo aumenta el riesgo de enumeración y exposición de datos personales.
- Se usa `user_id` cuando sea posible y se evita almacenar información de identificación directa en el log.

## Comportamiento si falla el audit log

- Si el insert en `audit_logs` falla, el flujo de autenticación no se rompe.
- El usuario sigue pudiendo iniciar sesión o cerrar sesión, y no se muestran excepciones internas.

## Pruebas recomendadas

1. Login correcto: usar credenciales válidas y verificar redirección a `dashboard.php`.
2. Login incorrecto: usar credenciales inválidas y verificar mensaje genérico.
3. Usuario bloqueado por rate limit: forzar más de 5 intentos fallidos y verificar `Demasiados intentos...`.
4. Logout: acceder al logout y verificar redirección a `login.php?logout=1`.
5. Revisar `audit_logs` y confirmar que los eventos esperados están presentes.
6. Confirmar que no hay `email` completo, IP completa, contraseña ni `password_hash` en los registros.
7. Ejecutar `php -l sistema/public/login.php` y `php -l sistema/public/logout.php`.

## Limitaciones

- Todavía no hay visor de auditoría en la interfaz.
- Todavía no hay limpieza histórica automática de logs.
- Todavía no hay alertas administrativas basadas en auditoría.

## Próximas etapas recomendadas

- 6D.14 — AuthGuard reutilizable
- 6D.15 — Timeout de sesión
- 6D.16 — Layout interno reutilizable
