# Login y Dashboard (placeholders)

## Objetivo de la etapa

Implementar pantallas base de `login`, `logout` y `dashboard` como placeholders funcionales que usan `SessionManager` y `CsrfService` sin realizar autenticación real ni conexión a base de datos.

## Archivos creados/modificados

- `sistema/public/login.php` (creado)
- `sistema/public/logout.php` (creado)
- `sistema/public/dashboard.php` (creado)
- `sistema/public/index.php` (modificado: el botón ahora enlaza a `login.php`)
- `sistema/public/assets/css/internal.css` (modificado: estilos de formulario y botones)

## Qué se implementó

- `login.php` carga `SessionManager` y `CsrfService`, inicia sesión, genera token CSRF y muestra un formulario de login. Si recibe POST valida CSRF y muestra un mensaje de estado; no autentica usuarios.
- `dashboard.php` inicia sesión (sin exigir autenticación) y muestra un panel placeholder con tarjetas para Cotizaciones, Clientes y Atenciones.
- `logout.php` destruye la sesión y redirige a `login.php`.

## Qué NO se implementó

- No hay conexión a base de datos ni tabla `users`.
- No se realiza verificación de credenciales.
- No se guardan ni transmiten contraseñas a ningún repositorio.
- No se habilita autenticación real ni creación de usuarios.

## Por qué no hay credenciales reales

- Evitamos exponer datos sensibles en una etapa de prototipo.
- La autenticación real requiere diseño de base de datos y migraciones en la siguiente fase (Etapa 6D).

## Cómo se usan `SessionManager` y `CsrfService`

- `SessionManager` se instancia y se llama a `start()` en páginas públicas del sistema.
- `CsrfService` se instancia con `SessionManager` y se usa para generar (`token()`) y validar (`validate()`) tokens.
- Los formularios incluirán el campo hidden `csrf_token` con el valor retornado por `token()`.

## Pruebas recomendadas

- Sintaxis PHP:
  - `php -l sistema/public/login.php`
  - `php -l sistema/public/logout.php`
  - `php -l sistema/public/dashboard.php`
- Flujo manual en navegador:
  - Abrir `sistema/public/login.php` y verificar que el formulario incluye un `csrf_token`.
  - Enviar formulario sin token y comprobar mensaje de error.
  - Enviar formulario con token válido y comprobar mensaje de preparación del flujo.
  - Abrir `sistema/public/dashboard.php` y verificar contenido placeholder.
  - Ejecutar `logout.php` y verificar redirección a `login.php`.

## Próxima etapa

- **Etapa 6D:** diseño e implementación de la base de datos y tabla `users`.
- **Etapa 6D.1:** conectar la autenticación real contra MySQL/MariaDB usando PDO.
