# Etapa 5B: Rate limiting y logging operativo para el formulario de contacto

## Objetivo
Implementar rate limiting por IP y logging operativo seguro en el formulario de contacto sin almacenar datos personales del remitente.

## Política aplicada
- Límite máximo: **5 intentos** por dirección IP.
- Ventana de tiempo: **15 minutos** (**900 segundos**).
- La dirección IP se toma de `$_SERVER['REMOTE_ADDR']`.
- Si la IP no es válida, se utiliza el valor `unknown`.
- Se aplica hash SHA-256 a la IP y al User-Agent (opcionales) antes de registrar cualquier dato.

## Rutas usadas
- Rate limiting: `storage/rate-limit/contact-<sha256(ip)>.json`
- Logging operativo: `storage/logs/contact.log`

## Datos que NO se registran
- `name`
- `email`
- `phone`
- `company`
- `message`

El registro evita almacenar información personal o sensible del formulario.

## Eventos registrados
1. `invalid_method`
2. `missing_required_fields`
3. `privacy_consent_missing`
4. `honeypot_triggered`
5. `invalid_length`
6. `invalid_email`
7. `rate_limit_blocked`
8. `send_attempt`
9. `send_completed`

Cada evento se guarda en formato JSON Lines con los campos:
- `timestamp`
- `event`
- `ip_hash`
- `method`
- `status`
- `reason`
- `user_agent_hash` (si está disponible)

## Consideraciones cPanel
- cPanel puede requerir permisos adecuados en `storage/logs` y `storage/rate-limit`.
- Asegúrate de que el usuario de PHP tenga permiso de escritura en ambas carpetas.

## Permisos recomendados
- Directorios: `0755`
- Archivos de registro: `0644`

## Pruebas recomendadas
- Enviar el formulario con método distinto a POST y verificar `invalid_method`.
- Enviar sin campos obligatorios y verificar `missing_required_fields`.
- Enviar sin consentimiento de privacidad y verificar `privacy_consent_missing`.
- Enviar con `website` poblado y verificar `honeypot_triggered`.
- Enviar datos demasiado largos y verificar `invalid_length`.
- Enviar email inválido y verificar `invalid_email`.
- Enviar 6 veces en 15 minutos desde la misma IP y verificar `rate_limit_blocked` con HTTP 429.
- Enviar un envío válido y verificar `send_attempt` y `send_completed`.

## Limitaciones
- No protege contra IP spoofing si se usa un proxy mal configurado.
- No reemplaza CAPTCHA ni WAF en caso de ataques volumétricos.
- El límite depende de la IP de origen reportada por el servidor.

## Recomendación futura
Si el volumen de spam aumenta, agregar una capa adicional como CAPTCHA o un WAF especializado, y considerar un sistema centralizado de detección de abuso.
