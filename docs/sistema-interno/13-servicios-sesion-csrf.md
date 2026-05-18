# Servicios de sesión y CSRF

## Objetivo de la etapa

Implementar servicios base reutilizables para manejo de sesión y protección CSRF en el sistema interno de D&A Systems. Estos servicios preparan la base para la implementación segura de login/logout en etapas posteriores sin implementar aún autenticación real ni conexión a la base de datos.

## Archivos creados

- `sistema/app/Core/SessionManager.php`
- `sistema/app/Core/CsrfService.php`

## Descripción de `SessionManager`

- Namespace: `DAndASystems\Internal\Core`.
- Clase final `SessionManager` que encapsula el inicio, regeneración y destrucción de sesiones.
- Configuración de cookie:
  - `session_name`: `DA_SYSTEMS_INTERNAL_SESSION`
  - `lifetime`: 0 (hasta cerrar navegador)
  - `path`: `/sistema`
  - `secure`: activado solo si HTTPS detectado
  - `httponly`: true
  - `SameSite`: Lax
- Métodos principales:
  - `start()` — inicia la sesión si no está activa.
  - `regenerate()` — regenera el id de sesión con `session_regenerate_id(true)`.
  - `destroy()` — limpia `$_SESSION`, borra cookie y destruye la sesión.
  - `isStarted()` — indica si la sesión está activa.
  - `get()`, `set()`, `remove()`, `has()` — acceso y manipulación segura de `$_SESSION`.

## Descripción de `CsrfService`

- Namespace: `DAndASystems\Internal\Core`.
- Depende de `SessionManager` inyectado en el constructor.
- Clave de sesión interna `_csrf_token`.
- Métodos principales:
  - `token()` — garantiza sesión iniciada y devuelve un token (genera uno si no existe).
  - `validate(?string $token)` — valida un token recibido comparándolo con el token de sesión usando `hash_equals`.
  - `rotate()` — reemplaza el token por uno nuevo.
  - `clear()` — elimina el token de la sesión.
- Generación de token con `bin2hex(random_bytes(32))`.

## Decisiones de seguridad

- Uso de `session_name` propio para aislar cookies.
- Cookies con `httponly` para evitar lecturas desde JavaScript.
- Cookie `secure` solo cuando se detecta HTTPS.
- `SameSite=Lax` para mitigar CSRF en la mayoría de escenarios.
- Ruta de cookie forzada a `/sistema` para limitar ámbito.
- Uso de `session_regenerate_id(true)` en login en etapas posteriores.
- Uso de `hash_equals` para comparar tokens.
- Tokens generados con `random_bytes` para entropía criptográfica.

## Qué NO se implementó

- No se implementó login ni logout funcional.
- No se creó `AuthGuard` ni controladores de autenticación.
- No se creó conexión a base de datos ni tablas `users`.
- No se implementó rate limit de login.
- No se creó endpoint público alguno para token CSRF en esta etapa.

## Cómo se usará en la siguiente etapa

- El `LoginController` usará `CsrfService::validate()` antes de procesar credenciales.
- `login.php` creará la sesión con `SessionManager::start()` y mostrará el token con `CsrfService::token()` en un input hidden.
- Tras login exitoso se llamará a `SessionManager::regenerate()`.
- El `Dashboard` verificará `SessionManager::isStarted()` y el estado del usuario.

## Pruebas recomendadas

- Sintaxis PHP:
  - `php -l sistema/app/Core/SessionManager.php`
  - `php -l sistema/app/Core/CsrfService.php`
- Flujo manual (local):
  - Instanciar `SessionManager` y llamar `start()`.
  - Instanciar `CsrfService` con `SessionManager`.
  - Llamar `token()` y verificar que retorna una cadena hex.
  - Llamar `validate()` con token correcto → true.
  - Llamar `validate()` con token incorrecto → false.
  - Llamar `rotate()` y verificar que cambia el token.
  - Llamar `destroy()` en `SessionManager` y verificar que la sesión se cierra.

## Consideraciones cPanel

- Verificar que PHP tenga soporte de sesiones activado en el hosting.
- Asegurar que HTTPS esté habilitado en producción para `secure=true`.
- Revisar permisos de la carpeta `sistema/Storage/` para logs y PDFs futuros.
- No exponer el directorio `sistema/app/` ni `sistema/config/` al público.

