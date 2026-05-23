# Etapa 6D.21 — Diseño técnico del modelo de datos de Cotizaciones

## Objetivo técnico del modelo

Definir el modelo de datos inicial para el módulo Cotizaciones, alineado con el diseño funcional de la etapa 6D.20, sin crear todavía tablas SQL, migraciones, CRUD, formularios ni lógica real.

El objetivo técnico es dejar preparada una estructura clara para almacenar cotizaciones y sus líneas de detalle cuando se implemente el módulo real.

## Entidades propuestas

- `cotizaciones`: cabecera de la cotización, datos comerciales, cliente, estado y totales.
- `cotizacion_items`: líneas o ítems cotizados asociados a una cotización.

De forma futura, `cotizaciones` podrá relacionarse con clientes y usuarios internos, pero esta etapa no implementa esas relaciones.

## Tabla conceptual: cotizaciones

| Campo | Tipo sugerido | Obligatorio | Reglas / observaciones |
| --- | --- | --- | --- |
| `id` | entero autoincremental | Sí | Identificador interno primario. |
| `quote_number` | varchar(30) | Sí | Número visible único, por ejemplo `COT-2026-0001`. |
| `quote_date` | date | Sí | Fecha de emisión o creación formal de la cotización. |
| `valid_until` | date | No | Fecha hasta la cual la cotización mantiene validez comercial. |
| `client_name` | varchar(160) | Sí | Nombre o razón social del cliente. |
| `client_rut` | varchar(20) | No | RUT del cliente si aplica. Debe permitir casos sin RUT confirmado. |
| `contact_name` | varchar(120) | No | Persona de contacto asociada a la cotización. |
| `contact_email` | varchar(160) | No | Correo de contacto. Validar formato en etapa posterior. |
| `contact_phone` | varchar(40) | No | Teléfono de contacto. |
| `description` | text | No | Descripción general o contexto de la cotización. |
| `status` | varchar(20) | Sí | Estado dentro de la lista permitida. Valor inicial sugerido: `borrador`. |
| `subtotal_net` | decimal(12,2) | Sí | Suma neta de líneas antes de descuento global e impuestos. |
| `discount_amount` | decimal(12,2) | Sí | Descuento global aplicado a la cotización. Valor inicial sugerido: `0.00`. |
| `tax_rate` | decimal(5,2) | Sí | Porcentaje de IVA u otro impuesto. Valor inicial sugerido en Chile: `19.00`, sujeto a decisión. |
| `tax_amount` | decimal(12,2) | Sí | Monto de impuesto calculado sobre base afecta. |
| `total_amount` | decimal(12,2) | Sí | Total final de la cotización. |
| `commercial_terms` | text | No | Condiciones comerciales, plazos, garantías o restricciones. |
| `notes` | text | No | Observaciones internas o visibles según decisión futura. |
| `created_by` | entero | No | Referencia futura a usuario interno que creó la cotización. |
| `created_at` | datetime | Sí | Fecha y hora de creación. |
| `updated_at` | datetime | Sí | Fecha y hora de última actualización. |

## Tabla conceptual: cotizacion_items

| Campo | Tipo sugerido | Obligatorio | Reglas / observaciones |
| --- | --- | --- | --- |
| `id` | entero autoincremental | Sí | Identificador interno primario. |
| `quote_id` | entero | Sí | Relación con `cotizaciones.id`. |
| `line_number` | entero | Sí | Orden de la línea dentro de la cotización. |
| `description` | text | Sí | Descripción del producto, servicio o actividad cotizada. |
| `quantity` | decimal(12,2) | Sí | Cantidad cotizada. Debe ser mayor que cero. |
| `unit` | varchar(30) | Sí | Unidad de medida, por ejemplo `unidad`, `hora`, `servicio`, `mes`. |
| `unit_price_net` | decimal(12,2) | Sí | Precio unitario neto. Debe ser mayor o igual a cero. |
| `discount_amount` | decimal(12,2) | Sí | Descuento aplicado a la línea. Valor inicial sugerido: `0.00`. |
| `line_subtotal_net` | decimal(12,2) | Sí | `quantity * unit_price_net`. |
| `line_total_net` | decimal(12,2) | Sí | Subtotal de línea menos descuento de línea. |
| `created_at` | datetime | Sí | Fecha y hora de creación. |
| `updated_at` | datetime | Sí | Fecha y hora de última actualización. |

## Estados válidos

- `borrador`: cotización editable en preparación.
- `emitida`: cotización terminada internamente.
- `enviada`: cotización enviada al cliente.
- `aceptada`: cotización aprobada por el cliente.
- `rechazada`: cotización no aceptada por el cliente.
- `anulada`: cotización invalidada por corrección, duplicidad u otro motivo.

Regla inicial sugerida: solo `borrador` debería ser editable libremente. Los cambios sobre estados posteriores deben definirse con permisos y trazabilidad en etapas futuras.

## Reglas de cálculo

- `line_subtotal_net = quantity * unit_price_net`.
- `line_total_net = line_subtotal_net - discount_amount`.
- `subtotal_net = suma(line_total_net)` de todos los ítems.
- `discount_amount` en `cotizaciones` representa un descuento global adicional.
- Base imponible sugerida: `subtotal_net - discount_amount`.
- `tax_amount = base_imponible * (tax_rate / 100)`.
- `total_amount = base_imponible + tax_amount`.
- Los montos deben almacenarse con dos decimales.
- El redondeo sugerido es a dos decimales por total de línea y total final, usando una regla consistente en backend.
- No se define todavía soporte para ítems exentos; queda como decisión pendiente.

## Regla sugerida para numeración

Se recomienda una numeración anual con prefijo:

`COT-YYYY-0001`

Ejemplo:

`COT-2026-0001`

Ventajas:

- Es legible para usuarios internos y clientes.
- Reinicia el correlativo por año.
- Evita números demasiado largos en el tiempo.
- Permite identificar rápidamente el período de emisión.

Decisión pendiente: definir si el correlativo se asigna al crear el borrador o solo al emitir la cotización.

## Relaciones

- `cotizaciones` 1:N `cotizacion_items`.
- Relación futura con clientes:
  - posible `client_id` hacia una tabla de clientes.
  - por ahora se mantienen campos denormalizados (`client_name`, `client_rut`, contacto, correo y teléfono) para permitir cotizaciones antes de integrar el módulo Clientes.
- Relación futura con usuarios internos:
  - `created_by` podrá apuntar a una tabla de usuarios internos.
  - más adelante podrían agregarse `updated_by`, `sent_by`, `accepted_by` o historial de cambios.

## Índices recomendados

- `quote_number` único.
- `status` para filtros por estado.
- `quote_date` para ordenamiento y reportes por fecha.
- `client_name` para búsqueda simple por cliente.
- `quote_id` en `cotizacion_items` para recuperar líneas de una cotización.

## Validaciones futuras

- `quote_number` obligatorio y único.
- `quote_date` obligatorio.
- `client_name` obligatorio.
- `contact_email` debe tener formato de email válido si se informa.
- `quantity` debe ser mayor que cero.
- `unit_price_net` debe ser mayor o igual a cero.
- `discount_amount` debe ser mayor o igual a cero.
- `line_total_net` no debe ser negativo.
- `subtotal_net`, `tax_amount` y `total_amount` no deben ser negativos.
- `status` debe pertenecer a la lista permitida.
- `valid_until` no debería ser anterior a `quote_date`.

## Decisiones pendientes

- Integración con clientes: definir cuándo se agregará `client_id`.
- Definir si se permitirá cotizar sin RUT.
- Confirmar si el IVA será fijo, variable o configurable por cotización.
- Definir si habrá ítems exentos de IVA.
- Definir control de correlativo para evitar duplicados en concurrencia.
- Definir si el correlativo se reserva en borrador o al emitir.
- Definir trazabilidad de cambios de estado.
- Definir si las observaciones serán internas, visibles al cliente o ambas.
- Definir permisos por rol para crear, editar, emitir, anular y aceptar cotizaciones.

## Qué NO se implementó

- No se crearon tablas SQL reales.
- No se crearon migraciones.
- No se modificó la base de datos.
- No se creó CRUD.
- No se crearon formularios.
- No se crearon controllers.
- No se modificó `cotizaciones.php`.
- No se modificó `InternalPage`.
- No se modificaron login, logout, timeout, `AuthGuard` ni `SessionManager`.
- No se implementaron cálculos reales.
- No se implementó PDF.
- No se implementó envío por correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar la estructura visual del listado de cotizaciones y de la pantalla de detalle, todavía sin formularios funcionales ni persistencia real, para validar el flujo de usuario antes de crear tablas o CRUD.
