# Etapa 6D.14 — AuthGuard reutilizable

## Objetivo

Crear una clase `AuthGuard` reutilizable para centralizar la lógica de protección de rutas internas y evitar duplicación en futuros módulos.

## Archivo creado

- `sistema/app/Core/AuthGuard.php`

## Archivo modificado

- `sistema/public/dashboard.php`

## Problema que resuelve

Evita replicar comprobaciones de sesión y redirecciones en cada página del sistema interno, facilitando mantenimiento y seguridad consistente.

## Cómo se usará en futuros módulos

- Incluir `SessionManager`, iniciar sesión y luego instanciar `AuthGuard`.
- Llamar a `$guard->requireAuth('login.php')` al inicio de páginas privadas.
- Leer datos del usuario con `$guard->user()` o métodos auxiliares.

## Qué valida

- Presencia de `auth_user_id` en `$_SESSION` y que sea un entero o cadena numérica.
- No se conecta a la base de datos; confía en los datos de sesión establecidos por el proceso de login.

## Qué NO valida todavía

- No valida que la sesión no haya sido revocada en la base de datos.
- No verifica roles/permissions elaborados; sólo provee `userRole()` para uso posterior.

## Por qué no consulta la base de datos

- La capa de autenticación central (login) ya escribe datos mínimos en la sesión.
- Evitar acceder a la BD en cada request mejora rendimiento y mantiene separación de responsabilidades.

## Datos mínimos de sesión usados

- `auth_user_id`
- `auth_user_name`
- `auth_user_email`
- `auth_user_role`
- `auth_logged_in_at`

## Pruebas recomendadas

1. Acceder a `dashboard.php` con sesión válida → debe mostrar el dashboard.
2. Acceder a `dashboard.php` sin sesión → debe redirigir a `login.php`.
3. Cerrar sesión y reintentar acceso a `dashboard.php` → debe redirigir.
4. Ejecutar `php -l sistema/app/Core/AuthGuard.php`.
5. Ejecutar `php -l sistema/public/dashboard.php`.

## Próximas etapas

- 6D.15 — Timeout de sesión por inactividad
- 6D.16 — Layout interno reutilizable
- 6E — Módulo clientes
