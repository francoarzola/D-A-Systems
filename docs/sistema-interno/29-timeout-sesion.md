# Etapa 6D.15 — Timeout de sesión por inactividad

## Objetivo

Implementar un timeout de sesión por inactividad para el sistema interno, cerrando la sesión si el usuario está inactivo por más de 30 minutos.

## Archivos modificados

- `sistema/app/Core/AuthGuard.php`
- `sistema/public/dashboard.php` (no requiere cambios adicionales si ya usa `AuthGuard`)
- `docs/sistema-interno/29-timeout-sesion.md`

## Política de timeout

- Timeout: 30 minutos (1,800 segundos)
- Variable de sesión usada: `auth_last_activity_at` (timestamp Unix)

## Flujo

1. `AuthGuard->requireAuth()` se ejecuta al inicio de páginas privadas.
2. Si el usuario no está autenticado, se redirige a `login.php`.
3. Si el usuario está autenticado, `AuthGuard` comprueba `auth_last_activity_at`:
   - Si no existe, se inicializa con el timestamp actual.
   - Si existe y han pasado más de 30 minutos desde la última actividad, se limpian las credenciales de sesión y se destruye la sesión, luego se redirige a `login.php?timeout=1`.
   - Si no ha expirado, se actualiza `auth_last_activity_at` con el timestamp actual.

## Qué ocurre si la sesión expira

- Se eliminan las claves de autenticación de la sesión y se destruye la sesión en el servidor.
- El usuario es redirigido a `login.php?timeout=1`.
- No se registra un evento de auditoría por timeout en esta etapa.

## Por qué se implementa en AuthGuard

- El `AuthGuard` centraliza la lógica de protección de rutas; el timeout por inactividad es una política de acceso que debe aplicarse de forma consistente en todas las rutas privadas.

## Qué NO hace todavía

- No registra un `audit_log` cuando la sesión expira.
- No muestra un contador visual de inactividad.
- No permite configurar el timeout desde la base de datos.

## Pruebas recomendadas

1. Acceso normal al `dashboard.php` con sesión activa → debe permitir acceso y actualizar `auth_last_activity_at`.
2. Acceso al `dashboard.php` sin sesión → debe redirigir a `login.php`.
3. Simular expiración: modificar manualmente la sesión `auth_last_activity_at` a un valor antiguo y acceder a `dashboard.php` → debe redirigir a `login.php?timeout=1`.
4. Logout normal: cerrar sesión y verificar que el flujo no rompe.
5. Ejecutar `php -l sistema/app/Core/AuthGuard.php`.
6. Ejecutar `php -l sistema/public/dashboard.php`.

## Próximas etapas

- 6D.16 — Layout interno reutilizable
- 6D.17 — Navegación interna base
- 6E — Módulo clientes
