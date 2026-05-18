# Modelo de datos inicial

## users

Propósito:
- Registrar usuarios que acceden al sistema interno.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `name` VARCHAR(150)
- `email` VARCHAR(255) UNIQUE
- `password_hash` VARCHAR(255)
- `role` ENUM('admin','usuario')
- `is_active` TINYINT(1) DEFAULT 1
- `created_at` DATETIME
- `updated_at` DATETIME

Relaciones:
- 1 usuario puede crear o modificar cotizaciones, clientes y atenciones.

Seguridad:
- `password_hash` nunca se expone.
- `email` único y validado.
- `role` controla permisos.

## clients

Propósito:
- Almacenar información de los clientes de D&A Systems.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `name` VARCHAR(200)
- `business_name` VARCHAR(255) NULL
- `rut` VARCHAR(30) NULL
- `email` VARCHAR(255)
- `phone` VARCHAR(50) NULL
- `address` VARCHAR(255) NULL
- `city` VARCHAR(100) NULL
- `region` VARCHAR(100) NULL
- `contact_person` VARCHAR(150) NULL
- `notes` TEXT NULL
- `is_active` TINYINT(1) DEFAULT 1
- `created_by` INT FK users(id)
- `created_at` DATETIME
- `updated_at` DATETIME

Relaciones:
- 1 cliente puede tener muchas cotizaciones.
- 1 cliente puede tener muchas atenciones técnicas.

Seguridad:
- No guardar datos de pago.
- Validar emails y teléfonos.
- `is_active` en lugar de eliminar.

## quotes

Propósito:
- Registrar cotizaciones generadas para clientes.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `client_id` INT FK clients(id)
- `user_id` INT FK users(id)
- `quote_number` VARCHAR(50) UNIQUE
- `status_id` INT FK quote_statuses(id)
- `issue_date` DATE
- `expiry_date` DATE NULL
- `currency` VARCHAR(10) DEFAULT 'CLP'
- `subtotal` DECIMAL(12,2)
- `tax` DECIMAL(12,2)
- `total` DECIMAL(12,2)
- `notes` TEXT NULL
- `is_draft` TINYINT(1) DEFAULT 1
- `created_at` DATETIME
- `updated_at` DATETIME

Relaciones:
- `quote_items` pertenece a `quotes`.
- `quote_statuses` define su ciclo de vida.

Seguridad:
- No exponer `quote_number` sin validación de acceso.
- Guardar montos con precisión decimal.
- `is_draft` para soportar cotizaciones no emitidas.

## quote_items

Propósito:
- Detallar ítems o servicios dentro de una cotización.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `quote_id` INT FK quotes(id)
- `description` VARCHAR(255)
- `quantity` INT
- `unit_price` DECIMAL(12,2)
- `discount` DECIMAL(12,2) DEFAULT 0
- `total_price` DECIMAL(12,2)
- `created_at` DATETIME
- `updated_at` DATETIME

Relaciones:
- Muchos ítems pertenecen a una cotización.

Seguridad:
- Calcular `total_price` en servidor.
- No confiar en cálculos enviados desde el cliente.

## quote_statuses

Propósito:
- Definir los estados posibles de una cotización.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `name` VARCHAR(50)
- `description` VARCHAR(255) NULL
- `created_at` DATETIME

Valores iniciales:
- `Borrador`
- `Emitida`
- `Aceptada`
- `Rechazada`
- `Vencida`

Seguridad:
- Valores administrados por la aplicación.
- No usar valores libres desde el cliente.

## service_reports

Propósito:
- Registrar atenciones técnicas y reportes de trabajo.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `client_id` INT FK clients(id)
- `user_id` INT FK users(id)
- `report_date` DATE
- `status` ENUM('abierto','en_proceso','cerrado')
- `diagnosis` TEXT
- `work_done` TEXT
- `recommendations` TEXT NULL
- `next_steps` TEXT NULL
- `created_at` DATETIME
- `updated_at` DATETIME

Relaciones:
- Asociado a un cliente.
- Asociado a un usuario responsable.

Seguridad:
- Validar que el `client_id` exista y sea activo.
- No exponer reportes sin permiso.

## audit_logs

Propósito:
- Registrar eventos relevantes para auditoría.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `user_id` INT FK users(id) NULL
- `event` VARCHAR(100)
- `description` TEXT NULL
- `resource_type` VARCHAR(50) NULL
- `resource_id` INT NULL
- `ip_address` VARCHAR(45) NULL
- `user_agent` VARCHAR(255) NULL
- `created_at` DATETIME

Seguridad:
- No guardar datos sensibles.
- Usar `user_id` cuando exista.
- Limitar longitud de `user_agent`.

## login_attempts

Propósito:
- Controlar accesos fallidos y bloquear intentos de fuerza bruta.

Campos sugeridos:
- `id` INT AUTO_INCREMENT PK
- `user_id` INT FK users(id) NULL
- `email_attempted` VARCHAR(255) NULL
- `ip_address` VARCHAR(45)
- `attempted_at` DATETIME
- `successful` TINYINT(1)

Seguridad:
- No persistir contraseñas.
- Usar IP y email para análisis.
- Borrar registros antiguos por política de retención.

## Consideraciones generales

- Usar `DATETIME` uniforme para trazabilidad.
- No almacenar información financiera sensible.
- Las claves foráneas deben tener `ON DELETE RESTRICT` o `NO ACTION` según el modelo.
- Considerar índices en `client_id`, `user_id`, `quote_number` y `status_id`.
- Implementar soft delete por `is_active` en lugar de borrado físico cuando corresponda.
