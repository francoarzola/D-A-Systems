# Diseño de autenticación segura

## 1. Objetivo de la autenticación

El objetivo es garantizar que solo usuarios autorizados puedan acceder al sistema interno de D&A Systems ubicado en `/sistema/`. La autenticación protegerá la gestión de clientes, cotizaciones e informes de atención. Aunque el uso inicial será de 2 personas, el diseño debe permitir escalabilidad y crecimiento ordenado.

Los objetivos clave son:
- acceso privado al sistema interno;
- protección de datos de clientes, cotizaciones e informes técnicos;
- soporte inicial para un equipo pequeño;
- arquitectura preparada para añadir usuarios, roles y permisos adicionales.

## 2. Flujo de autenticación

1. El usuario llega a la pantalla de login en el sistema interno.
2. Ingresa email y contraseña.
3. El backend valida las credenciales contra la tabla `users`.
4. Se compara la contraseña con `password_verify()` usando el hash almacenado.
5. Si la validación es correcta:
   - se llama a `session_regenerate_id(true)` para evitar fijación de sesión;
   - se establece la sesión autenticada;
   - se guarda la última fecha de login en `last_login_at`;
   - se redirige al dashboard protegido.
6. Si la validación falla:
   - se registra el intento en `login_attempts`;
   - se muestra un mensaje genérico sin indicar si el email existe.
7. El usuario puede cerrar sesión mediante logout.
8. La sesión expira tras un periodo de inactividad configurado (recomendado 30 minutos).

## 3. Tabla users propuesta

Campos mínimos:
- `id` INT AUTO_INCREMENT PK
- `name` VARCHAR(150)
- `email` VARCHAR(255) UNIQUE
- `password_hash` VARCHAR(255)
- `role` ENUM('admin','operador')
- `active` TINYINT(1) DEFAULT 1
- `last_login_at` DATETIME NULL
- `created_at` DATETIME
- `updated_at` DATETIME

Esta tabla soporta control de acceso mínimo, auditoría de sesión y estado de usuario.

## 4. Tabla login_attempts propuesta

Campos mínimos:
- `id` INT AUTO_INCREMENT PK
- `email_hash` CHAR(64)
- `ip_hash` CHAR(64)
- `success` TINYINT(1)
- `attempted_at` DATETIME
- `user_agent_hash` CHAR(64)

Esta tabla no almacena credenciales ni correos en texto plano. Solo datos irreversibles necesarios para detección de abuso.

## 5. Roles iniciales

### admin
- administrar usuarios en etapa futura;
- crear/editar clientes;
- crear/editar/anular cotizaciones;
- generar PDF;
- ver registros básicos.

### operador
- crear clientes;
- crear cotizaciones;
- crear atenciones;
- generar PDF.

Estos roles iniciales permiten separar responsabilidades del equipo sin complejidad excesiva.

## 6. Seguridad de contraseñas

- Las contraseñas deben almacenarse con `password_hash($password, PASSWORD_DEFAULT)`.
- La verificación debe realizarse con `password_verify($password, $password_hash)`.
- No guardar contraseñas en texto plano en la base de datos ni en ningún archivo.
- No subir contraseñas temporales o credenciales reales al repositorio.
- El usuario inicial debe crearse mediante un script seguro o un seeder manual controlado.
- Como mejora futura, forzar cambio de contraseña en el primer acceso o cada cierto período.

## 7. Seguridad de sesión

- Usar `session_name()` propio, por ejemplo `DA_SYSTEMS_ADMIN_SESSION`.
- Configurar `session.cookie_httponly = true`.
- Configurar `session.cookie_secure = true` cuando esté disponible HTTPS.
- Configurar `session.cookie_samesite = 'Lax'`.
- Regenerar el ID de sesión con `session_regenerate_id(true)` tras login exitoso.
- Establecer timeout de inactividad recomendado de 30 minutos.
- El logout debe destruir la sesión y borrar la cookie en el navegador.
- No guardar datos sensibles completos en la sesión; solo el identificador de usuario, rol y marcas necesarias.

## 8. CSRF interno

- Todos los formularios internos deben incluir un token CSRF único por sesión.
- El token se genera en servidor y se coloca en campos ocultos de formularios.
- El backend valida el token con `hash_equals()` para evitar ataques de sincronización.
- No se debe registrar el token CSRF en los logs.

## 9. Rate limit de login

- Límite sugerido: 5 intentos por 15 minutos por IP/email.
- Registrar cada intento en `login_attempts` con hashes de email, IP y user agent.
- Bloquear temporalmente intentos adicionales tras superar el límite.
- Mostrar mensajes genéricos para evitar enumeración de usuarios.

## 10. Protección contra malas prácticas

- No exponer si el correo existe o no en los mensajes de error.
- No registrar contraseñas, tokens, correos completos o RUT en logs.
- No concatenar SQL; usar PDO con prepared statements.
- Escapar la salida HTML para evitar XSS.
- Validar siempre en el backend incluso si hay validación en el cliente.

## 11. Configuración cPanel

- Utilizar MySQL Database Wizard para crear la base de datos y el usuario.
- Asignar al usuario MySQL solo los privilegios mínimos necesarios para la aplicación.
- Guardar la configuración de conexión fuera de `public/` si es posible.
- Si el archivo de configuración queda en `/sistema/config/`, protegerlo con `.htaccess`.
- No subir archivos `.env` reales al repositorio.
- Realizar backups regulares de la base de datos y documentar la restauración.

## 12. Próxima etapa recomendada

### Etapa 6C.2 — Implementación base de autenticación

- `AuthService`
- `SessionManager`
- `CsrfService`
- `LoginController`
- `LogoutController`
- `login` view
- `dashboard` protegido placeholder
- documentación de pruebas de autenticación

Esta etapa debe tomar la arquitectura diseñada aquí y transformarla en una implementación segura y minimalista en el sistema interno.`