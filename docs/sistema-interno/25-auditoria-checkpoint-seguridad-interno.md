# Auditoría checkpoint de seguridad — sistema interno

## 1. Resumen ejecutivo

El sistema interno de D&A Systems está en una etapa temprana, pero con una base sensible ya implementada. Los componentes clave de autenticación están en su lugar: login real contra la tabla `users`, logout, protección de dashboard por sesión y herramientas de soporte privadas. Sin embargo, aún faltan varias protecciones de seguridad antes de avanzar a módulos de negocio complejos.

Qué ya está implementado:
- `sistema/public/login.php` autentica contra `users`.
- `password_verify` valida la contraseña frente a `password_hash`.
- `create-admin-user.php` genera hashes seguros con `password_hash`.
- `login.php` usa mensajes genéricos para fallos de autenticación.
- Verifica `active = 1` antes de permitir acceso.
- Se actualiza `last_login_at` en el usuario.
- `SessionManager` administra sesiones con nombre propio y opciones seguras.
- `SessionManager->regenerate()` está en login.
- `logout.php` destruye la sesión.
- `CsrfService` genera, valida y rota tokens CSRF.
- `sistema/config/database.php` está fuera de Git y `database.example.php` está versionado.
- El acceso público y el sistema interno están separados físicamente bajo `/sistema`.

Qué aún no está implementado:
- rate limit de login y `login_attempts` operativo.
- auditoría real de eventos de login/logout.
- timeout de inactividad en sesión.
- AuthGuard reutilizable para proteger rutas internas.
- gestión de errores centralizada.
- gestión de usuarios operativa.
- políticas de respaldo y despliegue documental.
- protección adicional de `/sistema` en un entorno de hosting.
- funcionalidades reales en dashboard: Cotizaciones, Clientes, Atenciones están como marcadores de posición.

Veredicto:
- El sistema interno está en una condición aceptable para continuar con el desarrollo de módulos, pero no está listo para producción ni para carga de negocio compleja sin antes cubrir los bloqueadores de seguridad identificados. Debe priorizarse el hardening de autenticación y sesiones antes de avanzar a clientes/cotizaciones/atenciones.

## 2. Alcance revisado

Se revisaron conceptualmente los siguientes componentes sin modificar código:
- `sistema/public/login.php`
- `sistema/public/logout.php`
- `sistema/public/dashboard.php`
- `sistema/app/Core/SessionManager.php`
- `sistema/app/Core/CsrfService.php`
- `sistema/app/Infrastructure/Config/DatabaseConfig.php`
- `sistema/app/Infrastructure/Database/Connection.php`
- herramientas privadas en `sistema/tools`
- migraciones y seeders existentes
- `sistema/config/database.example.php`
- separación entre sitio público y sistema interno

## 3. Autenticación

Evaluación:
- La autenticación ya funciona como login real contra la tabla `users`.
- `login.php` consulta la fila con `SELECT id, name, email, password_hash, role, active FROM users WHERE email = :email LIMIT 1` y usa prepared statement.
- `password_verify` se usa correctamente para validar la contraseña.
- `create-admin-user.php` usa `password_hash(..., PASSWORD_DEFAULT)` para almacenar hashes seguros.
- Mensajes de error son genéricos: no revelan si el email existe ni si la contraseña es incorrecta.
- Se comprueba `active = 1` y se rechaza en caso contrario con mensaje genérico.
- Se actualiza `last_login_at` en el usuario autenticado.
- No se encontraron credenciales hardcodeadas en los archivos revisados.
- No hay exposición de `password_hash` en la interfaz.
- La consulta evita enumeración directa del usuario en los mensajes, aunque se debe seguir manteniendo esta práctica en futuros formularios.

## 4. Sesiones

Evaluación:
- `SessionManager` ofrece un nombre de sesión propio (`DA_SYSTEMS_INTERNAL_SESSION`).
- Se establece cookie con `httponly`, `samesite=Lax` y `secure` condicional según HTTPS.
- `session_regenerate_id(true)` se ejecuta después del login exitoso.
- Se guardan datos mínimos en sesión: `auth_user_id`, `auth_user_name`, `auth_user_email`, `auth_user_role`, `auth_logged_in_at`.
- `logout.php` destruye la sesión mediante `SessionManager->destroy()` y redirige a login.
- Pendientes importantes:
  - timeout de inactividad o expiración automática de sesión.
  - AuthGuard reutilizable para proteger rutas internas de forma centralizada.
  - verificación de autorización por rol para futuras secciones.

## 5. CSRF

Evaluación:
- `login.php` integra `CsrfService` y usa token por sesión.
- `CsrfService` valida con `hash_equals`, lo que mitiga ataques por timing.
- El token se rota tras login exitoso.
- Pendiente aplicar CSRF en formularios internos futuros fuera del login.

## 6. Base de datos

Evaluación:
- Las migraciones iniciales y semilla básica existen en `sistema/database`.
- La tabla `users` soporta login real.
- `database.php` no versionado protege credenciales locales.
- `database.example.php` versionado ofrece referencia segura.
- Se usa PDO en `Connection` y `login.php` con prepared statements.
- Se debe confirmar que las migraciones usan DECIMAL para montos donde corresponda y aplican índices/FKs apropiados para integridad referencial.
- Pendientes:
  - ejecutar migraciones también en entornos de hosting/cPanel.
  - políticas de respaldo formalizadas para la base de datos.

## 7. Herramientas privadas

Evaluación:
- `sistema/tools/test-db-connection.php` prueba conexión de base.
- `sistema/tools/create-admin-user.php` está diseñado para CLI y no para web.
- El script CLI solicita password interactivamente para evitar historial de terminal.
- No imprime el hash ni la contraseña en texto plano.
- Se debe revisar despliegue para asegurar que `/sistema/tools` no quede expuesto si se publica el sitio.

## 8. Separación público/privado

Evaluación:
- El sitio público está separado del sistema interno.
- El sistema interno reside en `/sistema` y no depende del frontend público.
- Herramientas privadas están fuera de `sistema/public`.
- Configuración sensible vive en `sistema/config`, fuera de la carpeta pública interna.
- Riesgos:
  - si `/sistema` se despliega sin reglas adicionales, `sistema/app` y `sistema/tools` podrían ser accesibles en un hosting mal configurado.
  - es recomendable reforzar con `.htaccess` o reglas de servidor que bloqueen acceso directo a `/sistema/app`, `/sistema/tools` y `/sistema/config`.

## 9. UI / UX interna

Evaluación:
- El formulario de login presenta campos y botón con estilo corporativo aceptable.
- Se corrigieron problemas de inputs y autofill en Chrome.
- El botón Acceder ahora tiene cursor pointer y no aparenta deshabilitado.
- `dashboard.php` muestra un mensaje de bienvenida con nombre autenticado y tarjetas placeholder para Cotizaciones, Clientes y Atenciones.
- Se debe advertir en la interfaz y en la documentación que esas áreas aún no tienen lógica de negocio.
- Recomendación: crear un layout interno reutilizable antes de desarrollar cada módulo para mantener consistencia.

## 10. Riesgos pendientes

Crítico:
- falta rate limit de login y `login_attempts` operativo.
- falta timeout de sesión.
- falta AuthGuard reutilizable para protección interna.
- falta protección adicional de `/sistema` en el despliegue.

Alto:
- falta audit_logs de login/logout.
- falta gestión de errores centralizada.
- falta política de backup documentada.
- falta recuperación/gestión de usuarios.

Medio:
- falta CSRF en futuros formularios internos.
- falta verificación de roles en rutas internas.
- falta layout interno reutilizable para nuevos módulos.

Bajo:
- UI/UX placeholder en dashboard.
- ausencia de documentación de despliegue.

## 11. Checklist de seguridad antes de construir módulos

- [ ] Implementar rate limit de login.
- [ ] Agregar `login_attempts` operativo.
- [ ] Implementar audit logs para login/logout.
- [ ] Crear AuthGuard reutilizable para proteger rutas internas.
- [ ] Añadir timeout de sesión e inactividad.
- [ ] Proteger `sistema/tools` y `sistema/config` en el entorno de hosting.
- [ ] Verificar que `sistema/config/database.php` no esté versionado.
- [ ] Establecer política de backup de base de datos.
- [ ] Probar login/logout con credenciales válidas.
- [ ] Probar acceso a `dashboard.php` sin sesión activa.
- [ ] Probar usuario con `active = 0`.
- [ ] Probar password incorrecta.
- [ ] Probar CSRF inválido en login.

## 12. Recomendación de orden de próximas etapas

1. 6D.12 rate limit login usando `login_attempts`.
2. 6D.13 audit logs de login/logout.
3. 6D.14 AuthGuard reutilizable.
4. 6D.15 timeout de sesión.
5. 6D.16 layout interno reutilizable.
6. 6E.1 módulo clientes listado.
7. 6E.2 crear cliente.
8. 6F módulo cotizaciones.

## 13. Veredicto final

El sistema interno tiene una implementación inicial de login seguro y protección de sesión adecuada para desarrollo local. Sin embargo, no está listo para módulos de negocio complejos ni para producción sin abordar los bloqueadores críticos de seguridad (rate limit, audit logs, AuthGuard, timeout y protección de despliegue). Con el orden recomendado, el proyecto puede avanzar a la siguiente etapa de hardening antes de construir los módulos de Cotizaciones, Clientes y Atenciones.
