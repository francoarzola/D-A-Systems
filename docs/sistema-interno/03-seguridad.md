# Seguridad del sistema interno

## Autenticación

- Usuarios registrados con `email` y `password`.
- Contraseñas almacenadas con `password_hash(..., PASSWORD_DEFAULT)`.
- Verificación con `password_verify()`.
- Control de bloqueo por intentos fallidos en `login_attempts`.
- Mensajes de error genéricos para evitar información de usuario.

## Sesiones PHP seguras

- Nombre de sesión único, por ejemplo `DA_SYSTEMS_ADMIN_SESSION`.
- `session.cookie_httponly = true`.
- `session.cookie_samesite = 'Lax'` o `Strict` si no hay integraciones externas.
- `session.cookie_secure = true` cuando el sitio use HTTPS.
- Regenerar ID de sesión en login exitoso y logout.
- Limitar duración de sesión y tiempo de inactividad.

## CSRF

- Token CSRF único por sesión y por formulario.
- Agregar hidden field en formularios sensibles.
- Validar en backend antes de procesar la solicitud.
- No confiar en JavaScript para la protección.

## Prepared statements y PDO

- Todas las consultas usan `PDO` y `prepare()`.
- No concatenar variables en SQL.
- Validar y sanitizar datos antes de bindearlos.
- Usar transacciones para operativas que modifican múltiples tablas.

## Validación backend

- Validar todos los datos en el servidor.
- Reglas de validación estricta para email, fechas, montos y relaciones.
- No confiar en validación JavaScript.
- Normalizar datos antes de persistir.

## Control de roles

- Roles iniciales: `admin` y `usuario`.
- `admin` puede gestionar usuarios, cotizaciones y atenciones.
- `usuario` puede crear y editar cotizaciones y clientes según permisos.
- Control de acceso basado en middleware en cada ruta.
- Separar permisos de vista y permisos de operación.

## Rate limit en login

- Límite de intentos por IP y por usuario.
- Bloqueo temporal tras 5 intentos fallidos.
- Registro en `login_attempts` con timestamp y resultado.
- Mensaje genérico en caso de bloqueo.

## Bloqueo de carpetas sensibles

- Proteger `/sistema/Config/`, `/sistema/Storage/` y `/sistema/vendor/`.
- Usar `.htaccess` en `/sistema/public/` para negar acceso a carpetas no públicas.
- Archivos de configuración no deben estar en `/public`.

## No exposición pública de PDFs

- Generar PDFs en `/sistema/Storage/pdfs/` o ruta privada.
- Servir descargas solo mediante controlador autenticado.
- No crear links directos a archivos PDF.
- Validar permisos antes de entregar documentos.

## Backups

- Respaldar la base de datos con exportación periódica.
- Copias de seguridad de `Config/` y `Storage/` sensibles.
- No incluir en backups archivos temporales innecesarios.
- Tener un plan de restauración documentado.

## Logs de auditoría

- Registrar eventos clave en `audit_logs`.
- Ejemplos: login exitoso/fallido, creación/edición de cotización, emisión de PDF, modificación de cliente.
- No almacenar contraseñas ni tokens.
- Guardar IP, usuario, acción, timestamp y detalles mínimos.

## Protección contra IDOR

- Validar que el usuario autenticado tenga acceso al recurso solicitado.
- No usar IDs secuenciales sin control de acceso.
- Evitar inferencia de recursos mediante URLs.
- Comprobar relación `quote.user_id`, `client.user_id`, `report.client_id` según el permiso.

## No registrar datos sensibles innecesarios

- No escribir en logs: contraseñas, tokens, información personal completa.
- Guardar solo identificadores y resultados de procesos.
- En `audit_logs`, usar descripciones claras pero parciales.
- Proteger logs con permisos de archivo.

## Recomendaciones adicionales

- Usar HTTPS en todo el sistema.
- Configurar cabeceras de seguridad (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`).
- Limitar acceso por IP si se desea mayor seguridad interna.
- Auditar el hosting cPanel y habilitar 2FA para las cuentas de administración.
