# Etapa 7A.1 — SQL inicial del módulo Cotizaciones

## Objetivo

Crear un archivo SQL inicial para las tablas futuras del módulo Cotizaciones, basado en el diseño técnico documentado en etapas anteriores, sin ejecutarlo automáticamente y sin modificar código PHP funcional.

Archivo creado:

`sistema/database/sql/001_create_quotes_tables.sql`

## Decisión de nombres físicos

Para este proyecto se decidió que el modelo físico de base de datos use nombres en español para tablas y campos.

Documentos anteriores usaban nombres conceptuales en inglés, como `quote_number`, `quote_date`, `valid_until`, `client_name`, `status`, `subtotal_net`, `tax_amount` y `total_amount`. Esos nombres siguen siendo útiles como referencia conceptual, pero el SQL inicial queda en español para mantener claridad y consistencia con el proyecto.

## Equivalencias principales

| Nombre conceptual previo | Nombre físico en SQL |
| --- | --- |
| `quote_number` | `numero_cotizacion` |
| `quote_date` | `fecha_cotizacion` |
| `valid_until` | `valido_hasta` |
| `client_name` | `nombre_cliente` |
| `client_rut` | `rut_cliente` |
| `contact_name` | `nombre_contacto` |
| `contact_email` | `correo_contacto` |
| `contact_phone` | `telefono_contacto` |
| `description` | `descripcion` |
| `status` | `estado` |
| `subtotal_net` | `subtotal_neto` |
| `discount_amount` | `descuento_monto` |
| `tax_rate` | `iva_porcentaje` |
| `tax_amount` | `iva_monto` |
| `total_amount` | `total` |
| `commercial_terms` | `condiciones_comerciales` |
| `notes` | `observaciones` |
| `created_by` | `creado_por` |
| `created_at` | `creado_en` |
| `updated_at` | `actualizado_en` |
| `quote_id` | `cotizacion_id` |
| `line_number` | `numero_linea` |
| `unit_price_net` | `precio_unitario_neto` |
| `line_subtotal_net` | `subtotal_linea_neto` |
| `line_total_net` | `total_linea_neto` |
| `quote_type` | `tipo_documento` |
| `quote_year` | `anio` |
| `last_number` | `ultimo_numero` |

## Tablas finales propuestas

### cotizaciones

Tabla principal de cabecera de cotización.

Campos principales:

- `id`
- `numero_cotizacion`
- `fecha_cotizacion`
- `valido_hasta`
- `nombre_cliente`
- `rut_cliente`
- `nombre_contacto`
- `correo_contacto`
- `telefono_contacto`
- `descripcion`
- `estado`
- `subtotal_neto`
- `descuento_monto`
- `iva_porcentaje`
- `iva_monto`
- `total`
- `condiciones_comerciales`
- `observaciones`
- `creado_por`
- `creado_en`
- `actualizado_en`

### cotizacion_detalles

Tabla de líneas o detalles asociados a una cotización.

Se usa `cotizacion_detalles` en vez de `cotizacion_items` porque resulta más claro dentro de un modelo físico en español.

Campos principales:

- `id`
- `cotizacion_id`
- `numero_linea`
- `descripcion`
- `cantidad`
- `unidad`
- `precio_unitario_neto`
- `descuento_monto`
- `subtotal_linea_neto`
- `total_linea_neto`
- `creado_en`
- `actualizado_en`

### cotizacion_correlativos

Tabla futura para controlar correlativos por tipo de documento y año.

Campos principales:

- `id`
- `tipo_documento`
- `anio`
- `ultimo_numero`
- `creado_en`
- `actualizado_en`

Esta tabla prepara el flujo futuro de emisión para evitar calcular correlativos con `MAX()+1` sin protección.

## Índices definidos

### cotizaciones

- `PRIMARY KEY (id)`.
- `UNIQUE KEY uq_cotizaciones_numero_cotizacion (numero_cotizacion)`.
- `KEY idx_cotizaciones_estado (estado)`.
- `KEY idx_cotizaciones_fecha_cotizacion (fecha_cotizacion)`.
- `KEY idx_cotizaciones_nombre_cliente (nombre_cliente)`.
- `KEY idx_cotizaciones_creado_por (creado_por)`.
- `CHECK chk_cotizaciones_numero_estado`.

`numero_cotizacion` permite `NULL` únicamente para cotizaciones en estado `borrador`. En estados `emitida`, `enviada`, `aceptada`, `rechazada` y `anulada`, `numero_cotizacion` debe existir.

En MySQL/MariaDB, un índice único permite múltiples valores `NULL`, por lo que los borradores pueden existir sin número y las cotizaciones emitidas deben mantener número único. El `CHECK` `chk_cotizaciones_numero_estado` refuerza además la consistencia entre `estado` y `numero_cotizacion`.

### cotizacion_detalles

- `PRIMARY KEY (id)`.
- `KEY idx_cotizacion_detalles_cotizacion_id (cotizacion_id)`.
- `KEY idx_cotizacion_detalles_numero_linea (numero_linea)`.
- `UNIQUE KEY uq_cotizacion_detalles_cotizacion_linea (cotizacion_id, numero_linea)`.
- `FOREIGN KEY cotizacion_id → cotizaciones.id`.

### cotizacion_correlativos

- `PRIMARY KEY (id)`.
- `UNIQUE KEY uq_cotizacion_correlativos_tipo_anio (tipo_documento, anio)`.
- `KEY idx_cotizacion_correlativos_anio (anio)`.

## Decisiones tomadas

- Se crea el archivo en `sistema/database/sql/` y no dentro de `migrations/`, porque esta etapa pide un SQL inicial específico para el módulo Cotizaciones.
- Se usan nombres físicos en español para tablas y campos.
- Se usa `cotizacion_detalles` para las líneas de cotización.
- `numero_cotizacion` queda nullable para permitir borradores sin correlativo.
- `numero_cotizacion` queda único cuando existe, apoyándose en el comportamiento de MySQL/MariaDB con valores `NULL` en índices únicos.
- Se agrega `chk_cotizaciones_numero_estado` para exigir que `numero_cotizacion` sea `NULL` en `borrador` y `NOT NULL` en estados posteriores.
- `estado` usa `ENUM` con los estados definidos: `borrador`, `emitida`, `enviada`, `aceptada`, `rechazada`, `anulada`.
- Los totales se guardan como `DECIMAL(12,2)`.
- `iva_porcentaje` queda con valor inicial `19.00`, alineado con el diseño para Chile, pero debe revisarse si el IVA será configurable.
- `cotizacion_detalles` usa `ON DELETE CASCADE` porque sus registros dependen completamente de la cotización padre.
- La relación con usuarios queda como `creado_por` indexado, sin clave foránea todavía, para no acoplar esta etapa a una decisión de permisos o usuarios internos.
- No se agrega relación con Clientes todavía; se mantienen campos denormalizados de cliente y contacto.
- La tabla `cotizacion_correlativos` permite reservar correlativos por `tipo_documento` y `anio`, evitando depender de `MAX()+1` sin protección.

## Orden sugerido de ejecución futura

Cuando se decida ejecutar manualmente en MySQL/phpMyAdmin:

1. `cotizaciones`.
2. `cotizacion_detalles`.
3. `cotizacion_correlativos`.

El archivo ya está ordenado de esa forma.

## Riesgos o puntos a revisar antes de ejecutar

- Verificar si ya existen tablas antiguas `quotes` y `quote_items` en el ambiente real, porque esta etapa propone nuevas tablas con nombres en español.
- Confirmar si se mantendrá `ENUM` para `estado` o si se preferirá tabla de estados.
- Confirmar si `ON DELETE CASCADE` para detalles es aceptable o si se prohibirá eliminar cotizaciones comerciales.
- Confirmar si `creado_por` debe tener clave foránea hacia `users.id` en una etapa posterior.
- Confirmar si el IVA será siempre `19.00` o configurable.
- Confirmar si se permitirá cotizar sin RUT.
- Verificar versión de MySQL/MariaDB antes de ejecutar, porque el soporte efectivo de `CHECK` depende de la versión; la implementación futura en backend también debe validar la regla `estado`/`numero_cotizacion`.
- Confirmar estrategia de bloqueo transaccional sobre `cotizacion_correlativos` antes de implementar emisión real.
- Hacer respaldo antes de ejecutar en cualquier ambiente con datos.

## Qué NO se implementó

- No se ejecutó SQL.
- No se creó conexión a base de datos.
- No se modificó PHP.
- No se modificó CSS.
- No se creó CRUD.
- No se crearon formularios funcionales.
- No se crearon repositories.
- No se crearon services.
- No se crearon controllers.
- No se implementó `POST`.
- No se guardaron datos.
- No se implementó PDF.
- No se implementó correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es revisar este SQL antes de ejecución manual, especialmente compatibilidad con tablas antiguas, decisión de nombres definitivos y estrategia de correlativos bajo transacción.
