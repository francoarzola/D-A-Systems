# Login real contra users

## Objetivo de la etapa

Implementar el login real del sistema interno contra la tabla `users`, usando la configuración existente de base de datos y la gestión de sesión/CSRF ya disponible.

## Archivos modificados

- `sistema/public/login.php`
- `sistema/public/dashboard.php`

## Flujo de login real

1. `login.php` inicia la sesión con `SessionManager`.
2. Si el usuario ya está autenticado, redirige a `dashboard.php`.
3. En GET genera un token CSRF con `CsrfService` y muestra el formulario.
4. En POST valida el token CSRF.
5. Valida el email y la contraseña de forma segura.
6. Consulta `users` con un prepared statement:
   - `SELECT id, name, email, password_hash, role, active FROM users WHERE email = :email LIMIT 1`
7. Si el usuario no existe, no está activo o la contraseña no coincide, muestra un mensaje genérico.
8. Si la autenticación es exitosa, se regenera la sesión y se guardan los datos mínimos en sesión.
9. Actualiza `last_login_at` para el usuario autenticado.
10. Redirige a `dashboard.php`.

## Uso de `users`

El login usa la tabla `users` existente y busca el registro por `email`. Solo permite el acceso cuando `active = 1`.

## Uso de `password_verify`

La contraseña ingresada se valida con `password_verify` contra el campo `password_hash` almacenado en la base de datos.

## Datos mínimos guardados en sesión

- `auth_user_id`
- `auth_user_name`
- `auth_user_email`
- `auth_user_role`
- `auth_logged_in_at`

## Protección de dashboard

`dashboard.php` verifica la existencia de `auth_user_id` en sesión. Si no existe, redirige a `login.php`.

## Logout real

`logout.php` destruye la sesión con `SessionManager->destroy()` y redirige a `login.php?logout=1`.

## Criterios de seguridad

- No se exponen errores internos ni excepciones al usuario.
- No se revela si el email existe o no.
- No se expone `password_hash`.
- Se usan prepared statements en todas las consultas.
- Se escapa la salida HTML con `htmlspecialchars`.
- Se utiliza CSRF para proteger el formulario de login.

## Qué NO se implementó todavía

- login_attempts
- rate limit de login
- auditoría de login
- recuperación de contraseña
- gestión de usuarios

## Pruebas locales recomendadas

- Iniciar sesión con email correcto y contraseña correcta.
- Intentar iniciar sesión con contraseña incorrecta.
- Intentar iniciar sesión con email inexistente.
- Intentar acceder directamente a `dashboard.php` sin sesión.
- Cerrar sesión con `logout.php`.
- Validar sintaxis PHP:
  - `php -l sistema/public/login.php`
  - `php -l sistema/public/logout.php`
  - `php -l sistema/public/dashboard.php`

## Próxima etapa

- 6D.11 login_attempts y rate limit de login.
- 6D.12 auditoría de eventos.
- 6E módulo clientes.
