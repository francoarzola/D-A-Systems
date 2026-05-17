# Configuración de contacto — Etapa 5a

## Objetivo del cambio
Externalizar el correo receptor del formulario de contacto para evitar mantenerlo hardcodeado en `forms/contact.php`.

## Archivos modificados
- `forms/contact.php`
- `config/contact.php`
- `config/contact.example.php`
- `.gitignore`
- `docs/configuracion-contacto-etapa-5a.md`

## Cómo se obtiene ahora el correo receptor
El backend carga `config/contact.php`, valida que retorne un arreglo y usa la clave `receiving_email_address` como destinatario fijo.

## Variable de entorno soportada
- `DA_SYSTEMS_RECEIVING_EMAIL`

## Fallback actual
Si la variable de entorno no está definida, se mantiene el fallback operativo actual:
- `dasystemstechnology@gmail.com`

## Recomendación para cPanel
- Definir `DA_SYSTEMS_RECEIVING_EMAIL` en el entorno del hosting.
- Mantener `config/contact.php` versionado con fallback temporal mientras se completa la transición.
- Probar envío real desde formularios después del despliegue.

## Pruebas recomendadas
1. Envío válido del formulario con respuesta de éxito.
2. Envío sin `privacy_consent` con error controlado.
3. Honeypot `website` poblado para validar bloqueo anti-bot.
4. Verificación del destinatario efectivo por variable de entorno o fallback.

## Nota de seguridad
El destinatario del correo no debe venir desde `POST` ni desde el frontend; debe mantenerse fijo desde configuración backend.
