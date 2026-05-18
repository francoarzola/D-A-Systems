# Plan de implementación de autenticación segura

## 1. Objetivo de la próxima implementación

El objetivo es planificar la implementación segura de autenticación en el sistema interno de D&A Systems. Esta etapa prepara el flujo de login/logout y gestión de sesión, garantizando una base segura antes de conectar con la base de datos de usuarios.

## 2. Archivos que se crearán en la etapa de código

- `sistema/app/Core/SessionManager.php`
- `sistema/app/Core/CsrfService.php`
- `sistema/app/Core/AuthGuard.php`
- `sistema/app/Http/Controllers/AuthController.php`
- `sistema/app/Views/auth/login.php`
- `sistema/app/Views/dashboard/index.php`
- `sistema/config/app.example.php`
- `sistema/config/app.php` (solo si se versiona sin secretos)
- `sistema/public/login.php`
- `sistema/public/logout.php`
- `sistema/public/dashboard.php`

## 3. Archivos que NO deben tocarse

- sitio público existente
- `forms/contact.php`
- `forms/csrf-token.php`
- `.htaccess` raíz
- `robots.txt`
- `sitemap.xml`

## 4. Flujo esperado

1. Usuario solicita `GET /login`.
2. Se muestra la pantalla de login con token CSRF.
3. Usuario envía `POST /login`.
4. El backend valida el token CSRF.
5. El backend valida las credenciales.
6. Se usa `password_verify()` para comparar la contraseña.
7. En caso de éxito, se llama a `session_regenerate_id(true)`.
8. Se redirige al `dashboard` protegido.
9. El usuario puede cerrar sesión con `logout`.
10. Se aplica timeout por inactividad para expirar la sesión.

## 5. Seguridad

- Configurar sesiones con `httponly`.
- Usar `secure` si el sistema se sirve sobre HTTPS.
- Establecer `SameSite=Lax`.
- Implementar CSRF en el formulario de login.
- Mostrar mensajes genéricos en errores de autenticación.
- Planificar rate limit de login como una etapa posterior.
- No exponer si el usuario existe o no en el sistema.
- No registrar contraseñas ni tokens en logs.

## 6. Dependencias

- No usar base de datos todavía si no está implementada.
- La primera implementación puede preparar el flujo de autenticación sin credenciales reales.
- Recomendar que la autenticación real se conecte a la tabla `users` cuando exista la base de datos.
- Mantener el diseño compatible con PDO y MySQL/MariaDB para la etapa siguiente.

## 7. Plan por etapas

- **Etapa 6C.4:** implementar `SessionManager` y `CsrfService`.
- **Etapa 6C.5:** implementar pantallas `login`, `logout` y `dashboard` placeholder.
- **Etapa 6D:** diseñar la base de datos y la tabla `users`.
- **Etapa 6D.1:** implementar autenticación real contra MySQL.
