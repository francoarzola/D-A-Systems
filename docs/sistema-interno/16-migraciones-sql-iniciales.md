# Migraciones SQL iniciales

## Objetivo de la etapa

Definir y crear las migraciones SQL iniciales para el sistema interno de D&A Systems. Estas migraciones preparan la estructura de datos para `users`, `clients`, `quotes`, `quote_items`, `service_reports`, `audit_logs`, `login_attempts` y `system_settings`, sin insertar datos reales ni crear conexiones a la base de datos.

## Migraciones creadas

- `sistema/database/migrations/001_create_users_table.sql`
- `sistema/database/migrations/002_create_clients_table.sql`
- `sistema/database/migrations/003_create_quote_statuses_table.sql`
- `sistema/database/migrations/004_create_quotes_table.sql`
- `sistema/database/migrations/005_create_quote_items_table.sql`
- `sistema/database/migrations/006_create_service_reports_table.sql`
- `sistema/database/migrations/007_create_audit_logs_table.sql`
- `sistema/database/migrations/008_create_login_attempts_table.sql`
- `sistema/database/migrations/009_create_system_settings_table.sql`

## Orden de ejecución recomendado

1. `001_create_users_table.sql`
2. `002_create_clients_table.sql`
3. `003_create_quote_statuses_table.sql`
4. `004_create_quotes_table.sql`
5. `005_create_quote_items_table.sql`
6. `006_create_service_reports_table.sql`
7. `007_create_audit_logs_table.sql`
8. `008_create_login_attempts_table.sql`
9. `009_create_system_settings_table.sql`

## Por qué aún no hay seeders

No se incluyen seeders en esta etapa porque el foco está en la estructura de datos. Los registros iniciales, como estados de cotización y usuarios admin, se crearán en etapas posteriores cuando la configuración segura de la base de datos y la autenticación real estén listas.

## Cómo ejecutar manualmente desde phpMyAdmin

1. Abrir phpMyAdmin desde cPanel.
2. Seleccionar la base de datos del sistema interno.
3. Usar la pestaña SQL para ejecutar las migraciones en el orden recomendado.
4. Verificar que cada tabla se crea sin errores.
5. No ejecutar estas migraciones en producción sin respaldo previo.

## Recomendación de backup antes de ejecutar migraciones

- Realizar backup completo de la base de datos antes de aplicar migraciones.
- Exportar estructura y datos existentes desde phpMyAdmin.
- Guardar el respaldo fuera del hosting cuando sea posible.
- No ejecutar las migraciones en un entorno compartido sin respaldo.

## Notas cPanel

- Estas migraciones son compatibles con MySQL/MariaDB en cPanel.
- Usan `ENGINE=InnoDB` y `DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`.
- Las claves foráneas usan `ON UPDATE CASCADE` y `ON DELETE RESTRICT` en datos comerciales críticos.
- `quote_items` usa `ON DELETE CASCADE` para limpiar ítems cuando se elimina una cotización.

## Advertencia

No ejecutar estas migraciones en producción sin un respaldo válido. Esta etapa es de preparación estructural; los datos reales y la lógica de negocio llegarán en etapas posteriores.

## Próximas etapas

- **Etapa 6D.3:** seeders básicos para estados y configuración inicial.
- **Etapa 6D.4:** configuración segura de base de datos sin credenciales reales.
- **Etapa 6D.5:** conexión PDO.
- **Etapa 6D.6:** autenticación real contra la tabla `users`.
