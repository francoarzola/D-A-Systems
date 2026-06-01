# Formulario de contacto — Etapa 4A

## Correo receptor configurado
- Backend configurado en `forms/contact.php` para enviar a: `contacto@dasystems.cl`.
- El destinatario es fijo en backend y no se toma desde `POST`.

## Campos validados
- Requeridos: `name`, `email`, `subject`, `message`.
- Validación de método HTTP: solo `POST`.
- Validación de email: `filter_var(..., FILTER_VALIDATE_EMAIL)`.
- Sanitización: `name`, `email`, `phone`, `company`, `subject`, `message`.
- Mitigación de header injection en: `name`, `email`, `subject`.
- Límites de longitud:
  - name: 100
  - email: 150
  - phone: 30
  - company: 150
  - subject: 150
  - message: 3000

## Medidas anti-spam aplicadas
- Honeypot `website` agregado en `index.html`.
- Si `website` trae contenido, backend responde `OK` y no envía correo.

## Dependencia de PHP Email Form
- Requiere `assets/vendor/php-email-form/php-email-form.php`.
- Si no existe, el backend devuelve error controlado sin exponer rutas internas.

## Prueba recomendada en hosting cPanel
1. Enviar formulario válido y confirmar recepción en `contacto@dasystems.cl`.
2. Enviar con honeypot `website` lleno y confirmar que no se envía correo.
3. Probar email inválido y campos requeridos vacíos.
4. Revisar `error_log` en cPanel por errores de envío.
5. Verificar entregabilidad (SPF/DKIM/DMARC) del entorno de hosting.
