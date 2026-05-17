# Formulario de contacto — Etapa 4A

## Correo receptor configurado
- `forms/contact.php` envía a: **dasystemstechnology@gmail.com**.
- El destinatario es fijo en backend y no se toma desde `POST`.

## Campos validados
Se validan como obligatorios:
- `name`
- `email`
- `subject`
- `message`

Validaciones adicionales:
- `email` con `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Sanitización en `name`, `email`, `phone`, `company`, `subject`, `message`.
- Prevención de header injection eliminando saltos de línea en `name`, `email`, `subject`.
- Límites de longitud:
  - name: 100
  - email: 150
  - phone: 30
  - company: 150
  - subject: 150
  - message: 3000

## Medidas anti-spam aplicadas
- Honeypot `website` agregado en formulario (`index.html`) y procesado en backend.
- Si `website` llega con contenido, se devuelve éxito silencioso (`OK`) sin enviar correo.

## Dependencias pendientes
- Requiere librería `assets/vendor/php-email-form/php-email-form.php`.
- Si la librería no está disponible, se devuelve error controlado sin exponer rutas internas.

## Prueba recomendada en hosting cPanel
1. Enviar formulario válido y verificar recepción en `dasystemstechnology@gmail.com`.
2. Enviar con `website` lleno (simulación bot) y verificar que no llegue correo.
3. Probar email inválido y campos vacíos para confirmar rechazo.
4. Revisar `error_log` de cPanel por errores PHP/SMTP.
5. Confirmar que SPF/DKIM/DMARC del dominio remitente/hosting no bloquee entregas.
