# Rate limit y logging operativo — Etapa 5b

## Objetivo del cambio
Agregar protección básica contra abuso por IP y trazabilidad operativa del endpoint de contacto sin exponer datos personales del formulario.

## Política de rate limit
- Máximo **5 intentos por 15 minutos por IP**.
- Ventana temporal: **900 segundos**.
- Clave de control por archivo JSON con hash SHA-256 de la IP.

## Rutas usadas
- `storage/rate-limit/`
- `storage/logs/contact.log`

## Datos que NO se registran
No se registran datos personales del formulario:
- nombre
- email
- teléfono
- empresa
- mensaje

## Eventos registrados
- `invalid_method`
- `missing_required_fields`
- `privacy_consent_missing`
- `honeypot_triggered`
- `invalid_length`
- `invalid_email`
- `rate_limit_blocked`
- `send_attempt`
- `send_completed`

## Consideraciones para cPanel
- Compatible con hosting compartido sin base de datos.
- No usa Composer ni dependencias externas.
- Depende de permisos de escritura en `storage/rate-limit` y `storage/logs`.

## Permisos recomendados
- Directorios: `755`
- Archivos de runtime: `644`
- Usuario de PHP con permisos de escritura sobre `storage/`.

## Pruebas recomendadas
1. Hacer 6 envíos consecutivos desde la misma IP en menos de 15 minutos y verificar respuesta HTTP `429` en el sexto intento.
2. Enviar formulario sin `privacy_consent` y confirmar respuesta controlada.
3. Enviar honeypot con valor y verificar respuesta silenciosa `OK`.
4. Revisar que `storage/logs/contact.log` registre eventos en formato JSON Lines sin datos personales.

## Limitaciones de este enfoque
- El control se basa en archivos locales y puede variar en entornos con múltiples nodos.
- No distingue actores detrás de NAT compartido.
- No reemplaza medidas avanzadas de reputación o firewall de aplicaciones.

## Recomendación futura
Si el spam aumenta, complementar con **CAPTCHA** y/o **WAF** administrado.
