# Etapa 5D: Protección CSRF para el formulario de contacto

## Objetivo
Agregar protección CSRF básica al formulario de contacto usando sesiones PHP, sin cambiar la arquitectura actual del sitio ni el envío AJAX existente.

## Archivos modificados
- `forms/contact.php`
- `forms/csrf-token.php`
- `index.html`
- `servicios-ti.html`

## Flujo CSRF implementado
1. El navegador carga `index.html` o `servicios-ti.html`.
2. El frontend busca formularios con `action="forms/contact.php"` y realiza un `fetch('forms/csrf-token.php', { credentials: 'same-origin' })`.
3. `forms/csrf-token.php` inicia sesión PHP segura con `session_name('DA_SYSTEMS_SESSION')` y configura la cookie de sesión con `httponly=true`, `samesite=Lax`, `path=/` y `secure=true` solo si HTTPS está activo.
4. Si no existe, el servidor genera `$_SESSION['csrf_token'] = bin2hex(random_bytes(32))`.
5. El endpoint devuelve JSON: `{ "csrf_token": "<token>" }`.
6. El script cliente inyecta el token en los campos ocultos `input[name="csrf_token"].csrf-token-field`.
7. Al enviar el formulario, `forms/contact.php` inicia la misma sesión y valida el token usando `hash_equals($_SESSION['csrf_token'], (string) $_POST['csrf_token'])`.
8. Si el token falta o es inválido, se registra el evento `csrf_token_invalid`, retorna HTTP 400 y un mensaje controlado.

## Detalles de la implementación
- El token solo se acepta vía `POST` en el campo `csrf_token` del formulario.
- El parámetro se valida después de aplicar rate limiting y antes de validar los campos obligatorios.
- El intento inválido cuenta dentro del rate limit.
- No se acepta token desde GET ni headers en esta etapa.
- No se registra el valor del token ni datos personales en los logs.

## Pruebas recomendadas en cPanel
- [ ] Verificar que `forms/csrf-token.php` responde con JSON válido y `csrf_token`.
- [ ] Verificar que `index.html` carga el formulario y el campo oculto `csrf_token` se rellena antes de enviar.
- [ ] Verificar que `servicios-ti.html` carga el formulario y el campo oculto `csrf_token` se rellena.
- [ ] Enviar el formulario normalmente y verificar que llega el correo.
- [ ] Enviar el formulario sin `csrf_token` y verificar que retorna HTTP 400 con mensaje de validación.
- [ ] Enviar el formulario con `csrf_token` inválido y verificar HTTP 400 con mensaje controlado.
- [ ] Recargar la página y verificar que el token sigue funcionando.
- [ ] Verificar que el rate limit sigue activo y que más de 5 envíos desde la misma IP generan HTTP 429.
- [ ] Revisar `storage/logs/contact.log` y confirmar que no contiene `csrf_token` ni datos personales (`name`, `email`, `phone`, `company`, `message`).
- [ ] Confirmar que la cookie de sesión `DA_SYSTEMS_SESSION` se establece con `HttpOnly` y `SameSite=Lax`.

## Casos de prueba
- Envío normal válido con token CSRF correcto.
- Envío sin token CSRF.
- Envío con token CSRF inválido.
- Recarga de página para generar o reutilizar el token.
- Envío después de 5 intentos para confirmar que rate limit permanece activo.
- Revisión de logs para asegurar que no se registra `csrf_token` ni datos PII.

## Limitaciones
- El token CSRF es básico y depende de sesiones PHP.
- No reemplaza CAPTCHA ni WAF en caso de spam elevado.
- Requiere cookies de sesión habilitadas en el navegador.
- Requiere que PHP pueda crear y gestionar sesiones en el hosting cPanel.

## Siguiente etapa recomendada
- Implementar CAPTCHA o Turnstile solo si aparece spam real.
