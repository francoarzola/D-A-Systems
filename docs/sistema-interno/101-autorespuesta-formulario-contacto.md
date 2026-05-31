# Autorespuesta del formulario de contacto

## Objetivo
Agregar una respuesta automática para el usuario que envía el formulario de contacto, confirmando que su solicitud fue recibida, sin afectar el envío principal al equipo de D&A Systems.

## Flujo implementado
- El formulario público sigue funcionando como antes.
- Se valida el token CSRF, el consentimento de privacidad, el honeypot y la limitación de intentos.
- El correo principal se envía usando el mismo `PHP_Email_Form` y la configuración SMTP ya existente.
- Si la variable `DA_SYSTEMS_AUTO_REPLY_ENABLED` está activada, la aplicación envía un segundo correo al remitente con:
  - remitente principal: D&A Systems (misma cuenta SMTP/configuración existente)
  - destinatario: correo del usuario
  - asunto: `Hemos recibido tu solicitud | D&A Systems`
  - contenido HTML simple con alternativa de texto plano para clientes que no acepten HTML.

## Configuración
Agregar en el entorno de producción o desarrollo:

```env
DA_SYSTEMS_AUTO_REPLY_ENABLED=true
```

La configuración SMTP existente permanece sin cambios y se reutiliza para el correo de confirmación.

## Comportamiento
- Si el correo principal se envía correctamente, se devuelve `OK` al frontend.
- Si la autorespuesta se falla, el usuario aún recibe `OK` y la aplicación registra el error internamente.
- El remitente principal de la autorespuesta no es el correo del usuario; utiliza `D&A Systems` como remitente y el mismo remitente SMTP definido en `config/contact.php`.

## Archivos modificados
- `forms/contact.php`
- `config/contact.php`

## Verificación
- El formulario debe seguir enviando el mensaje correctamente al email de recepción configurado.
- Con `DA_SYSTEMS_AUTO_REPLY_ENABLED=true`, la cuenta remitente debe enviar también la confirmación automática al correo del usuario.
- Si la autorespuesta falla, el envío principal no se revierte.

## Notas de seguridad
- No se cambia la lógica de base de datos, diseño, cotizaciones ni assets.
- El correo del usuario se utiliza solo como destinatario de la respuesta automática y como `Reply-To` en el correo principal; no se usa como remitente SMTP.
