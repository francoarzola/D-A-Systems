# Auditoría y checklist de pruebas cPanel — Etapa 5C

## Contexto
Rama actual: `etapa-5c-auditoria-pruebas-cpanel`
Proyecto: D&A Systems

Este documento detalla la auditoría post-merge del formulario de contacto y propone un checklist operativo de pruebas para publicación en hosting cPanel.

---

## Revisión de estado actual

### Archivos revisados
- `forms/contact.php`
- `config/contact.php`
- `config/contact.example.php`
- `.gitignore`
- `storage/logs/.gitkeep`
- `storage/rate-limit/.gitkeep`
- `.htaccess`
- `index.html`
- `servicios-ti.html`

### Observaciones generales
- `forms/contact.php` implementa el envío del formulario sin modificaciones en los bloques PHP analizados.
- `config/contact.php` carga el destinatario desde la variable de entorno `DA_SYSTEMS_RECEIVING_EMAIL`, con fallback a `dasystemstechnology@gmail.com`.
- `config/contact.example.php` define un ejemplo de configuración con `correo@tudominio.cl`.
- `.gitignore` descarta logs y archivos de rate limit en `storage/logs/*.log` y `storage/rate-limit/*.json`.
- Los archivos `storage/logs/.gitkeep` y `storage/rate-limit/.gitkeep` existen como marcadores de directorio.
- `.htaccess` solo define cabeceras de seguridad HTTP y mantiene HSTS comentado.
- `index.html` y `servicios-ti.html` incluyen un formulario que apunta a `forms/contact.php` y cargan `assets/vendor/php-email-form/validate.js`.

---

## Confirmación de funcionalidades del formulario

### Aspectos verificados en `forms/contact.php`
- Carga de destinatario desde `config/contact.php`.
- Validación del destinatario usando `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Validación del método de solicitud: rechaza cualquier cosa que no sea `POST` con `405`.
- Campos obligatorios: `name`, `email`, `subject`, `message`.
- Verificación de `privacy_consent` igual a `accepted`.
- Honeypot `website` para detectar bots.
- Sanitización de entradas con `trim(strip_tags(...))`.
- Mitigación de header injection para `name`, `email` y `subject` con reemplazo de `\r`, `\n`, `%0a`, `%0d`.
- Límites de longitud aplicados a los campos:
  - `name` ≤ 100
  - `email` ≤ 150
  - `phone` ≤ 30
  - `company` ≤ 150
  - `subject` ≤ 150
  - `message` ≤ 3000
- Validación de email del remitente con `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Rate limiting por IP mediante `storage/rate-limit/contact-<sha256(ip)>.json`.
- Logging operativo en JSON Lines mediante `storage/logs/contact.log`.
- No se registra información personal sensible del formulario en el log.
- Mensajes de error controlados y con códigos HTTP adecuados.

### Datos no registrados
El log operacional omite explícitamente:
- `name`
- `email`
- `phone`
- `company`
- `message`

---

## Checklist operativo de pruebas para cPanel

### Pruebas de carga
- [ ] Abrir `index.html` y verificar que la página carga correctamente.
- [ ] Abrir `servicios-ti.html` y verificar que la página carga correctamente.
- [ ] Abrir `politica-privacidad.html` y verificar que la página carga correctamente.
- [ ] Abrir `terminos-condiciones.html` y verificar que la página carga correctamente.
- [ ] Comprobar favicon y logo en las páginas.
- [ ] Confirmar rutas relativas de assets (`assets/css`, `assets/vendor`, `assets/img`, `assets/js`) funcionan sin 404.

### Pruebas del formulario
- [ ] Envío correcto desde `index.html` y recepción de respuesta `OK`.
- [ ] Envío correcto desde `servicios-ti.html` si el formulario existe allí.
- [ ] Envío con falta de campos obligatorios y respuesta controlada `Faltan campos obligatorios.`.
- [ ] Envío sin consentimiento de privacidad y respuesta controlada `Debes aceptar la política de privacidad para enviar la solicitud.`.
- [ ] Envío con email inválido y respuesta controlada `El correo electrónico no es válido.`.
- [ ] Envío con el campo honeypot `website` lleno y respuesta controlada.
- [ ] Prueba de rate limit: realizar más de 5 intentos desde la misma IP en 15 minutos y verificar HTTP `429` con mensaje `Has realizado demasiados intentos. Intenta nuevamente más tarde.`.
- [ ] Revisar `storage/logs/contact.log` tras las pruebas y verificar entradas JSON Lines.
- [ ] Revisar `storage/rate-limit/contact-<hash>.json` tras los envíos y verificar que contiene un objeto `attempts` con timestamps.
- [ ] Confirmar que el archivo de log no guarda `name`, `email`, `phone`, `company` ni `message`.

### Pruebas de servidor
- [ ] Confirmar que la versión de PHP es compatible con el stack del proyecto y está disponible en cPanel.
- [ ] Confirmar que PHP tiene permisos de escritura en `storage/logs` y `storage/rate-limit`.
- [ ] Confirmar que `forms/contact.php` puede incluir `assets/vendor/php-email-form/php-email-form.php` correctamente.
- [ ] Confirmar que `config/contact.php` existe en el entorno de producción y que no se usa `config/contact.example.php` directamente.
- [ ] Confirmar que no se expone listado de directorios desde la raíz del sitio.
- [ ] Confirmar que HTTPS está activo en el sitio de publicación.
- [ ] Confirmar que `.htaccess` no rompe la navegación ni bloquea el sitio.

### Pruebas de correo
- [ ] Confirmar recepción de correo en `dasystemstechnology@gmail.com` o en `DA_SYSTEMS_RECEIVING_EMAIL` si se define.
- [ ] Revisar bandejas de spam/promociones tras el envío.
- [ ] Probar el envío desde un correo real y verificar recepción.
- [ ] Probar el envío desde un correo inválido y verificar que la validación rechaza el envío antes de enviar correo real.
- [ ] Documentar el resultado del envío en el ambiente de cPanel.

---

## Riesgos pendientes
- Falta un token CSRF para proteger el formulario contra envíos forjados.
- Falta CAPTCHA o WAF si aumenta el volumen de spam.
- La dependencia `assets/vendor/php-email-form/php-email-form.php` debe monitorearse y actualizarse regularmente.
- El logging local requiere revisión de retención y rotación para evitar archivos grandes.
- HSTS se debe activar solo cuando HTTPS esté estable; actualmente permanece comentado en `.htaccess`.

---

## Criterio de publicación
El sitio puede publicarse si se cumple lo siguiente:
- [ ] Las páginas cargan correctamente en el navegador.
- [ ] El formulario envía correo real y retorna respuesta controlada.
- [ ] Los errores se muestran de forma controlada y no se filtra información técnica.
- [ ] PHP escribe en `storage/logs` y `storage/rate-limit` sin errores de permisos.
- [ ] No se registran datos personales del formulario en el log.
- [ ] HTTPS funciona correctamente en el entorno.
- [ ] `robots.txt` y `sitemap.xml` cargan sin errores.

---

## Siguiente etapa recomendada
- Etapa 5D — Implementar CSRF token para el formulario de contacto.
- Etapa posterior — Agregar CAPTCHA o Turnstile si aparece spam real.
