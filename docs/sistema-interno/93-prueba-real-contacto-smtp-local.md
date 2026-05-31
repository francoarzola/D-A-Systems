# 7C.01 — Prueba real de envío del formulario de contacto con SMTP local

## Objetivo

Preparar el formulario público de contacto para una prueba real local donde el correo llegue a una casilla configurada por variables de entorno. Esta etapa aplica solo al formulario de contacto público y no al módulo de cotizaciones.

## Alcance

- Solo formulario público de contacto en `index.html`.
- Solo `forms/contact.php` y `config/contact.php`.
- No se implementa envío de cotizaciones por correo.
- No se modifica `index.html`, `assets/vendor/php-email-form/validate.js` ni `forms/csrf-token.php`.

## Variables de entorno necesarias

- `DA_SYSTEMS_RECEIVING_EMAIL`
- `DA_SYSTEMS_SMTP_HOST`
- `DA_SYSTEMS_SMTP_PORT`
- `DA_SYSTEMS_SMTP_ENCRYPTION`
- `DA_SYSTEMS_SMTP_USERNAME`
- `DA_SYSTEMS_SMTP_PASSWORD`
- `DA_SYSTEMS_SMTP_MAILER`

## Ejemplo de configuración local en PowerShell

```powershell
$env:DA_SYSTEMS_RECEIVING_EMAIL = 'contacto@midominio.cl'
$env:DA_SYSTEMS_SMTP_HOST = 'smtp.midominio.cl'
$env:DA_SYSTEMS_SMTP_PORT = '587'
$env:DA_SYSTEMS_SMTP_ENCRYPTION = 'tls'
$env:DA_SYSTEMS_SMTP_USERNAME = 'smtp-user'
$env:DA_SYSTEMS_SMTP_PASSWORD = 'smtp-password'
$env:DA_SYSTEMS_SMTP_MAILER = 'smtp'
```

> No incluir contraseñas reales en el repositorio. Usa variables de entorno locales.

## Cómo levantar el servidor local desde la raíz del proyecto

```powershell
php -S 127.0.0.1:8080
```

Luego abrir en el navegador:

```
http://127.0.0.1:8080/index.html#contact
```

## Pasos de prueba manual

1. Configurar variables de entorno en PowerShell.
2. Iniciar el servidor local desde la raíz del repositorio.
3. Abrir `http://127.0.0.1:8080/index.html#contact`.
4. Completar el formulario de contacto.
5. Enviar el formulario.
6. Confirmar que la respuesta es `OK`.
7. Verificar que el correo llegó a la casilla configurada.

## Revisar el log local

- Archivo: `storage/logs/contact.log`
- Busca eventos `send_completed`, `rate_limit_blocked`, `csrf_token_invalid`, `missing_required_fields` y `honeypot_triggered`.

## Cómo limpiar rate limit local si se bloquean intentos

Eliminar o vaciar el archivo de rate limit correspondiente en `storage/rate-limit/`, por ejemplo:

```powershell
Remove-Item .\storage\rate-limit\contact-*.json
```

## Riesgos

- Credenciales incorrectas.
- SMTP bloqueado por el proveedor.
- Correo dirigido a spam.
- Vendor o librería de `php-email-form` faltante.
- Usar contraseña normal en lugar de credenciales SMTP válidas.

## Qué NO se implementó

- Envío de cotizaciones por email.
- Cambios al módulo de cotizaciones.
- Base de datos.
- API JSON.
- JavaScript nuevo.

## Comandos de validación local

```powershell
php -l config/contact.php
php -l forms/contact.php
php -l sistema/tools/check-contact-form-smtp-local-contract.php
php sistema/tools/check-contact-form-smtp-local-contract.php
```

## Comandos Laragon equivalentes

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l config/contact.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l forms/contact.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -l sistema/tools/check-contact-form-smtp-local-contract.php
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-contact-form-smtp-local-contract.php
```

## Resultado esperado

- El formulario de contacto usa `DA_SYSTEMS_RECEIVING_EMAIL`.
- Si `DA_SYSTEMS_SMTP_HOST` está configurado, se utiliza SMTP.
- Si no hay SMTP, el formulario sigue funcionando con la configuración por defecto.
- La prueba local puede enviar correo real al buzón configurado.

## Notas adicionales

- El formulario sigue usando CSRF, honeypot, rate limit y validación de campos.
- No se expone ninguna contraseña ni configuración sensible.
