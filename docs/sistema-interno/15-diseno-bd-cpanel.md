# Diseño de base de datos y configuración cPanel

## 1. Resumen ejecutivo

Esta etapa define el diseño de datos y la configuración recomendada en cPanel para el sistema interno privado de D&A Systems. No se implementan tablas reales, migraciones ni conexión PDO en este documento. El objetivo es preparar una base sólida y segura antes de crear la base de datos y la capa de persistencia.

## 2. Decisión de motor de base de datos

### MySQL/MariaDB

- Alta compatibilidad con cPanel y phpMyAdmin.
- Integración nativa con PHP/PDO.
- Fácil respaldo y restauración en entornos compartidos.
- Suficiente para un sistema interno de clientes, cotizaciones y atenciones.
- Soporta InnoDB, transacciones y relaciones con integridad referencial.

### SQLite

- Adecuado para prototipos pequeños, pero no ideal en cPanel para un sistema en crecimiento.
- Menos conveniente para copias de seguridad centralizadas y acceso concurrente.
- No es la primera opción para sistemas administrativos basados en web con múltiples usuarios.

### PostgreSQL

- Muy robusto, pero no es necesario en esta primera versión.
- Menos común en cPanel estándar y en configuraciones de hosting compartido.
- Añade complejidad innecesaria para el MVP.

### Elección: MySQL/MariaDB

Se elige MySQL/MariaDB por su compatibilidad con cPanel, PHP/PDO y phpMyAdmin, su facilidad de respaldo y su capacidad suficiente para el sistema interno inicial.

## 3. Convención de nombres

- Base de datos lógica sugerida: `dasystems_internal`
- Usuario lógico de BD sugerido: `dasystems_app`
- En cPanel puede quedar con prefijo de cuenta: `usuariohosting_dasystems_internal`
- Tablas en `snake_case`.
- Columnas en `snake_case`.
- Campos de fechas: `created_at`, `updated_at`, `deleted_at` donde corresponda.

## 4. Configuración recomendada en cPanel

- Crear la base de datos mediante `MySQL Database Wizard` o `MySQL Databases`.
- Crear un usuario MySQL dedicado para la aplicación.
- Asignar solo los privilegios necesarios, evitando `ALL PRIVILEGES` si no es necesario.
- Usar una contraseña fuerte y única para el usuario de la aplicación.
- No compartir credenciales en Git ni en canales inseguros.
- Guardar credenciales en un archivo de configuración protegido y fuera de `public_html` si el hosting lo permite.
- No subir archivos `.env` reales al repositorio.
- Validar el acceso mediante phpMyAdmin una vez creada la base de datos.
- Exportar un backup SQL antes de hacer cambios importantes o crear migraciones.

## 5. Seguridad de credenciales

- No versionar credenciales reales en el repositorio.
- Usar un archivo de configuración local protegido por `.htaccess` si queda dentro de `sistema/config`.
- Idealmente, ubicar la configuración fuera de `public_html` si el hosting lo permite.
- Crear un usuario MySQL exclusivo para este sistema interno.
- No usar el usuario root de MySQL para la aplicación.
- No reutilizar la contraseña del cPanel para la base de datos.
- Rotar credenciales si se sospecha exposición o compromiso.

## 6. Modelo de datos propuesto

### users

- Propósito: usuarios que acceden al sistema interno.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `name` VARCHAR(120)
  - `email` VARCHAR(190) UNIQUE
  - `password_hash` VARCHAR(255)
  - `role` ENUM('admin','operador')
  - `active` TINYINT(1)
  - `last_login_at` DATETIME NULL
  - `created_at` DATETIME
  - `updated_at` DATETIME
- Restricciones: email único, `password_hash` nunca texto plano.
- Relaciones: 1:N con `quotes`, `service_reports`, `audit_logs`.
- Seguridad: login por email, solo contraseñas hasheadas.

### clients

- Propósito: datos de clientes y contactos para cotizaciones y atenciones.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `client_type` ENUM('empresa','persona')
  - `business_name` VARCHAR(180)
  - `rut` VARCHAR(20) NULL
  - `giro` VARCHAR(180) NULL
  - `contact_name` VARCHAR(120)
  - `email` VARCHAR(190)
  - `phone` VARCHAR(40)
  - `address` VARCHAR(255) NULL
  - `comuna` VARCHAR(120) NULL
  - `city` VARCHAR(120) NULL
  - `notes` TEXT NULL
  - `active` TINYINT(1)
  - `created_at` DATETIME
  - `updated_at` DATETIME
- Restricciones: email validado, rut opcional en etapa inicial.
- Relaciones: 1:N con `quotes`, 1:N con `service_reports`.
- Seguridad: no guardar múltiples contactos en MVP; RUT opcional en borrador.

### quote_statuses

- Propósito: estados de cotización.
- Estados iniciales: `borrador`, `emitida`, `enviada`, `aceptada`, `rechazada`, `anulada`.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `code` VARCHAR(30) UNIQUE
  - `name` VARCHAR(80)
  - `sort_order` INT
  - `active` TINYINT(1)
- Restricciones: `code` único.
- Relaciones: 1:N con `quotes`.

### quotes

- Propósito: cotizaciones generadas para clientes.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `quote_number` VARCHAR(30) UNIQUE
  - `client_id` INT UNSIGNED
  - `user_id` INT UNSIGNED
  - `status_id` INT UNSIGNED
  - `issue_date` DATE
  - `valid_until` DATE
  - `currency` VARCHAR(10) DEFAULT 'CLP'
  - `tax_rate` DECIMAL(5,2) DEFAULT 19.00
  - `subtotal` DECIMAL(12,2)
  - `discount_total` DECIMAL(12,2)
  - `tax_total` DECIMAL(12,2)
  - `total` DECIMAL(12,2)
  - `notes` TEXT NULL
  - `terms` TEXT NULL
  - `created_at` DATETIME
  - `updated_at` DATETIME
- Restricciones: `quote_number` único.
- Relaciones: `client_id` → `clients`, `user_id` → `users`, `status_id` → `quote_statuses`.
- Consideraciones: totales en DECIMAL, vigencia por defecto 15 días, no usar FLOAT.

### quote_items

- Propósito: líneas de detalle de una cotización.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `quote_id` INT UNSIGNED
  - `description` TEXT
  - `unit` VARCHAR(30)
  - `quantity` DECIMAL(10,2)
  - `unit_price` DECIMAL(12,2)
  - `discount_percent` DECIMAL(5,2) NULL
  - `discount_amount` DECIMAL(12,2)
  - `line_total` DECIMAL(12,2)
  - `sort_order` INT
  - `created_at` DATETIME
  - `updated_at` DATETIME
- Restricciones: `quote_id` obligatorio.
- Relaciones: `quote_id` → `quotes`.
- Notas: ítems manuales en MVP; catálogo queda para más adelante.

### service_reports

- Propósito: registrar atenciones técnicas y reportes.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `client_id` INT UNSIGNED
  - `user_id` INT UNSIGNED
  - `report_number` VARCHAR(30) UNIQUE NULL
  - `service_date` DATE
  - `service_type` VARCHAR(50)
  - `status` VARCHAR(40)
  - `reported_issue` TEXT
  - `diagnosis` TEXT
  - `work_done` TEXT
  - `recommendations` TEXT
  - `billable` TINYINT(1)
  - `created_at` DATETIME
  - `updated_at` DATETIME
- Estados sugeridos: `pendiente`, `en_proceso`, `finalizada`, `anulada`.
- Relaciones: `client_id` → `clients`, `user_id` → `users`.

### audit_logs

- Propósito: registrar eventos de auditoría y cambios relevantes.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `user_id` INT UNSIGNED NULL
  - `event` VARCHAR(80)
  - `entity_type` VARCHAR(80)
  - `entity_id` INT UNSIGNED NULL
  - `ip_hash` VARCHAR(64) NULL
  - `user_agent_hash` VARCHAR(64) NULL
  - `metadata_json` JSON NULL
  - `created_at` DATETIME
- Seguridad: no guardar contraseñas, tokens, ni datos personales innecesarios.
- Notas: usar hash para IP y user agent cuando sea posible.

### login_attempts

- Propósito: soportar rate limit de login y análisis de accesos fallidos.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `email_hash` VARCHAR(64)
  - `ip_hash` VARCHAR(64)
  - `success` TINYINT(1)
  - `user_agent_hash` VARCHAR(64) NULL
  - `attempted_at` DATETIME
- Uso: detectar intentos de acceso abusivos sin almacenar emails completos.

### system_settings

- Propósito: almacenar parámetros globales y configuraciones operativas.
- Campos sugeridos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `setting_key` VARCHAR(100) UNIQUE
  - `setting_value` TEXT
  - `is_sensitive` TINYINT(1)
  - `created_at` DATETIME
  - `updated_at` DATETIME
- Uso futuro: tasa IVA por defecto, vigencia, condición comercial, correlativo, datos de la empresa.

## 7. Tabla users

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `name` VARCHAR(120)
- `email` VARCHAR(190) UNIQUE
- `password_hash` VARCHAR(255)
- `role` ENUM('admin','operador')
- `active` TINYINT(1)
- `last_login_at` DATETIME NULL
- `created_at` DATETIME
- `updated_at` DATETIME

Notas:
- `password_hash` se genera con `password_hash()`.
- Nunca guardar contraseñas en texto plano.
- El login se realiza por email.
- En la primera versión habrá 2 usuarios admin.

## 8. Tabla clients

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `client_type` ENUM('empresa','persona')
- `business_name` VARCHAR(180)
- `rut` VARCHAR(20) NULL
- `giro` VARCHAR(180) NULL
- `contact_name` VARCHAR(120)
- `email` VARCHAR(190)
- `phone` VARCHAR(40)
- `address` VARCHAR(255) NULL
- `comuna` VARCHAR(120) NULL
- `city` VARCHAR(120) NULL
- `notes` TEXT NULL
- `active` TINYINT(1)
- `created_at` DATETIME
- `updated_at` DATETIME

Notas:
- El RUT es opcional en borrador, recomendado para emisión formal.
- Se maneja un contacto principal en MVP.
- Múltiples contactos quedan para versión futura.

## 9. Tabla quote_statuses

- Estados iniciales: `borrador`, `emitida`, `enviada`, `aceptada`, `rechazada`, `anulada`.
- Campos:
  - `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
  - `code` VARCHAR(30) UNIQUE
  - `name` VARCHAR(80)
  - `sort_order` INT
  - `active` TINYINT(1)

## 10. Tabla quotes

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `quote_number` VARCHAR(30) UNIQUE
- `client_id` INT UNSIGNED
- `user_id` INT UNSIGNED
- `status_id` INT UNSIGNED
- `issue_date` DATE
- `valid_until` DATE
- `currency` VARCHAR(10) DEFAULT 'CLP'
- `tax_rate` DECIMAL(5,2) DEFAULT 19.00
- `subtotal` DECIMAL(12,2)
- `discount_total` DECIMAL(12,2)
- `tax_total` DECIMAL(12,2)
- `total` DECIMAL(12,2)
- `notes` TEXT NULL
- `terms` TEXT NULL
- `created_at` DATETIME
- `updated_at` DATETIME

Reglas:
- Formato correlativo sugerido: `COT-YYYY-0001`.
- Vigencia por defecto: 15 días.
- IVA por defecto: 19%.
- Montos en DECIMAL, no FLOAT.
- Recalcular totales en backend, no solo en frontend.

## 11. Tabla quote_items

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `quote_id` INT UNSIGNED
- `description` TEXT
- `unit` VARCHAR(30)
- `quantity` DECIMAL(10,2)
- `unit_price` DECIMAL(12,2)
- `discount_percent` DECIMAL(5,2) NULL
- `discount_amount` DECIMAL(12,2)
- `line_total` DECIMAL(12,2)
- `sort_order` INT
- `created_at` DATETIME
- `updated_at` DATETIME

Notas:
- Ítems manuales en MVP.
- Catálogo de productos/servicios quedará para fases posteriores.
- Unidad sugerida: `unidad`, `hora`, `servicio`, `mes`, `licencia`, `otro`.

## 12. Tabla service_reports

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `client_id` INT UNSIGNED
- `user_id` INT UNSIGNED
- `report_number` VARCHAR(30) UNIQUE NULL
- `service_date` DATE
- `service_type` VARCHAR(50)
- `status` VARCHAR(40)
- `reported_issue` TEXT
- `diagnosis` TEXT
- `work_done` TEXT
- `recommendations` TEXT
- `billable` TINYINT(1)
- `created_at` DATETIME
- `updated_at` DATETIME

Estados sugeridos:
- `pendiente`
- `en_proceso`
- `finalizada`
- `anulada`

## 13. Tabla audit_logs

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `user_id` INT UNSIGNED NULL
- `event` VARCHAR(80)
- `entity_type` VARCHAR(80)
- `entity_id` INT UNSIGNED NULL
- `ip_hash` VARCHAR(64) NULL
- `user_agent_hash` VARCHAR(64) NULL
- `metadata_json` JSON NULL
- `created_at` DATETIME

Seguridad:
- No guardar contraseñas ni tokens.
- No guardar RUT completo si no es necesario.
- No guardar datos personales innecesarios.
- Usar hashes para IP y user agent cuando sea posible.

## 14. Tabla login_attempts

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `email_hash` VARCHAR(64)
- `ip_hash` VARCHAR(64)
- `success` TINYINT(1)
- `user_agent_hash` VARCHAR(64) NULL
- `attempted_at` DATETIME

Uso:
- Rate limit de login.
- Evitar enumeración de usuarios.
- No guardar email completo.

## 15. Tabla system_settings

- `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY
- `setting_key` VARCHAR(100) UNIQUE
- `setting_value` TEXT
- `is_sensitive` TINYINT(1)
- `created_at` DATETIME
- `updated_at` DATETIME

Uso futuro:
- Datos comerciales de D&A Systems.
- Tasa de IVA por defecto.
- Vigencia por defecto.
- Condiciones comerciales estándar.
- Prefijo/correlativo de documentos.

## 16. Relaciones

- `users` 1:N `quotes`
- `clients` 1:N `quotes`
- `quotes` 1:N `quote_items`
- `quote_statuses` 1:N `quotes`
- `clients` 1:N `service_reports`
- `users` 1:N `service_reports`
- `users` 1:N `audit_logs`

## 17. Índices recomendados

- `users.email` UNIQUE
- `clients.rut` INDEX o UNIQUE opcional
- `quotes.quote_number` UNIQUE
- `quote_items.quote_id` INDEX
- `login_attempts.email_hash` INDEX
- `login_attempts.ip_hash` INDEX
- `audit_logs.event` INDEX
- `audit_logs.created_at` INDEX

## 18. Reglas de negocio iniciales

- No emitir cotización sin cliente.
- No emitir cotización sin ítems.
- Cotización en borrador puede tener datos incompletos.
- Cotización emitida debe congelar totales.
- El PDF debe generarse desde datos persistidos.
- Totales deben recalcularse en backend.
- Usuarios inactivos no pueden iniciar sesión.
- Los dos usuarios iniciales serán administradores.

## 19. Backups

- Realizar backup manual desde phpMyAdmin.
- Exportar estructura y datos.
- Frecuencia sugerida: semanal al inicio.
- Hacer backup antes de migraciones o cambios estructurales.
- Guardar respaldos fuera del hosting.
- Documentar el proceso de restauración básica.

## 20. Próxima etapa recomendada

- **Etapa 6D.2:** Crear migraciones SQL iniciales.
- **Etapa 6D.3:** Crear configuración segura de base de datos sin credenciales reales.
- **Etapa 6D.4:** Crear conexión PDO.
- **Etapa 6D.5:** Crear seeder/controlado para el usuario admin inicial.
- **Etapa 6D.6:** Conectar login real contra la tabla `users`.
