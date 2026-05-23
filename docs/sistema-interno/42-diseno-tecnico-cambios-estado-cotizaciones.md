# Etapa 6D.28 — Diseño técnico de cambios de estado de cotizaciones

## Objetivo del flujo de cambios de estado

Definir técnicamente cómo deberán cambiar de estado las cotizaciones después de su emisión, manteniendo reglas claras para proteger el número oficial, evitar regresiones indebidas y preparar auditoría futura.

Esta etapa documenta transiciones permitidas, transiciones prohibidas, reglas de edición por estado, validaciones, seguridad, manejo de errores y redirecciones futuras.

No se implementa lógica funcional todavía.

## Estados y significado comercial

Estados definidos para cotizaciones:

- `borrador`: cotización editable, todavía sin número oficial y sin validez comercial formal.
- `emitida`: cotización formalizada internamente, con `quote_number` asignado y lista para envío.
- `enviada`: cotización entregada o informada al cliente.
- `aceptada`: cotización aprobada por el cliente.
- `rechazada`: cotización no aprobada por el cliente.
- `anulada`: cotización invalidada por corrección, error, duplicidad u otra razón administrativa.

El estado debe representar el avance comercial real de la cotización y no debe depender de valores enviados desde frontend sin validación backend.

## Transiciones permitidas

Transiciones base permitidas:

- `borrador` → `emitida`
- `emitida` → `enviada`
- `emitida` → `anulada`
- `enviada` → `aceptada`
- `enviada` → `rechazada`
- `enviada` → `anulada`

Transición excepcional futura:

- `aceptada` → `anulada`

La transición `aceptada` → `anulada` debe considerarse excepción administrativa y requerir motivo obligatorio, permiso especial y auditoría. No debe ser acción cotidiana.

## Transiciones prohibidas

Transiciones prohibidas por defecto:

- `aceptada` → `borrador`
- `rechazada` → `borrador`
- `anulada` → `borrador`
- `anulada` → `emitida`
- `emitida` → `borrador`
- `enviada` → `borrador`
- `aceptada` → `emitida`
- `rechazada` → `emitida`
- `rechazada` → `enviada`

La reversa `emitida` → `borrador` solo podría existir en un mecanismo formal futuro de reversa, con auditoría fuerte y reglas explícitas. No se recomienda para la primera versión.

## Reglas de edición según estado

### Borrador

Regla:

- Editable.

Permitir edición de datos de cliente, contacto, condiciones, ítems y observaciones mientras no tenga `quote_number`.

### Emitida

Regla:

- Edición limitada o bloqueada.

Recomendación inicial:

- Bloquear edición de datos comerciales e ítems.
- Permitir solo cambios de estado y observaciones administrativas futuras.
- Mantener `quote_number` inmutable.

### Enviada

Regla:

- Bloqueada salvo cambios de estado.

Permitir únicamente acciones comerciales posteriores:

- Marcar como aceptada.
- Marcar como rechazada.
- Anular, si corresponde.

### Aceptada

Regla:

- Bloqueada.

No debe permitir edición normal. Una anulación posterior solo debería existir como excepción futura con motivo, permisos y auditoría.

### Rechazada

Regla:

- Bloqueada o duplicable como nueva cotización futura.

No debe volver a borrador. Si se necesita rehacer la propuesta, la opción recomendada es duplicarla como un nuevo borrador, con nuevo ciclo y sin reutilizar `quote_number`.

### Anulada

Regla:

- Bloqueada.

No debe poder reactivarse ni volver a `emitida` en la primera versión. Si se requiere una corrección, debe generarse una nueva cotización.

## Acciones futuras

Acciones previstas:

- Marcar como enviada.
- Marcar como aceptada.
- Marcar como rechazada.
- Anular cotización.
- Duplicar cotización como nuevo borrador, solo como idea futura.

La acción de duplicar debe crear una nueva cotización en estado `borrador`, sin `quote_number`, manteniendo el número de la cotización original intacto.

## Validaciones por acción

### Marcar como enviada

Validaciones:

- Sesión válida.
- CSRF válido.
- Cotización existente.
- Estado actual `emitida`.
- `quote_number` asignado.
- Totales recalculados o persistidos de forma válida.

Resultado esperado:

- Cambiar estado a `enviada`.
- Registrar fecha/hora futura de envío si existe campo como `sent_at`.

### Marcar como aceptada

Validaciones:

- Sesión válida.
- CSRF válido.
- Cotización existente.
- Estado actual `enviada`.
- Motivo u observación opcional según decisión futura.

Resultado esperado:

- Cambiar estado a `aceptada`.
- Registrar fecha/hora futura de aceptación si existe campo como `accepted_at`.

### Marcar como rechazada

Validaciones:

- Sesión válida.
- CSRF válido.
- Cotización existente.
- Estado actual `enviada`.
- Motivo recomendado.

Resultado esperado:

- Cambiar estado a `rechazada`.
- Registrar fecha/hora futura de rechazo si existe campo como `rejected_at`.

### Anular cotización

Validaciones:

- Sesión válida.
- CSRF válido.
- Cotización existente.
- Estado actual `emitida` o `enviada`.
- Estado `aceptada` solo si se habilita excepción futura.
- Motivo obligatorio.
- Permiso especial futuro si aplica.

Resultado esperado:

- Cambiar estado a `anulada`.
- Registrar motivo, observación, usuario y fecha/hora.

## Auditoría futura

Cada cambio de estado debería registrar un evento de auditoría.

Campos sugeridos:

| Campo | Descripción |
| --- | --- |
| `id` | Identificador del evento. |
| `quote_id` | Cotización afectada. |
| `previous_status` | Estado anterior. |
| `new_status` | Estado nuevo. |
| `user_id` | Usuario que ejecutó el cambio. |
| `changed_at` | Fecha y hora del cambio. |
| `reason` | Motivo estructurado cuando aplique. |
| `note` | Observación adicional. |
| `source` | Origen del cambio, por ejemplo `internal_panel`. |

Eventos mínimos a auditar:

- Emisión.
- Envío.
- Aceptación.
- Rechazo.
- Anulación.
- Intentos fallidos relevantes si se define logging de seguridad.

Esta etapa no crea auditoría real ni tablas.

## Seguridad

Reglas futuras:

- Exigir sesión válida.
- Validar CSRF para cada cambio de estado.
- Validar permisos por rol cuando existan.
- Validar estado actual en backend antes de cambiarlo.
- No confiar en el estado enviado desde frontend.
- No confiar en acciones visibles como fuente de autorización.
- Escapar salida al mostrar estado, motivo u observaciones.
- Registrar usuario responsable del cambio.
- No permitir modificación de `quote_number` después de emisión.
- Usar transacción cuando el cambio de estado deba ir junto con auditoría.

## Manejo de errores

### Estado inválido

Si el estado actual no permite la acción solicitada, rechazar el cambio y redirigir con mensaje.

Ejemplo:

- Intentar marcar como aceptada una cotización `emitida` que aún no está `enviada`.

### Transición no permitida

Si se solicita una transición fuera de la matriz permitida, no se debe cambiar nada.

Ejemplo:

- `anulada` → `emitida`.
- `rechazada` → `borrador`.

### Cotización no encontrada

Mostrar error controlado o redirigir al listado con mensaje genérico.

No mostrar detalles internos.

### Sesión expirada

Redirigir a `login.php`, manteniendo la protección de sesión existente.

### Error de persistencia

Si falla el guardado del estado o la auditoría futura, ejecutar rollback cuando corresponda y mostrar mensaje genérico.

## Redirecciones futuras

### Cambio correcto

Opciones:

- Redirigir al detalle: `cotizacion-detalle.php?id={id}&status=estado_actualizado`.
- Redirigir al listado: `cotizaciones.php?status=estado_actualizado`.

Recomendación:

- Redirigir al detalle para conservar contexto del usuario.

### Error de validación o transición

Opciones:

- Redirigir al detalle con mensaje de error.
- Redirigir al listado si la cotización no existe.

### Sesión expirada

Redirigir a `login.php`.

## Matriz resumida de transición

| Estado actual | Acciones permitidas | Estados destino |
| --- | --- | --- |
| `borrador` | Emitir | `emitida` |
| `emitida` | Marcar enviada, anular | `enviada`, `anulada` |
| `enviada` | Aceptar, rechazar, anular | `aceptada`, `rechazada`, `anulada` |
| `aceptada` | Anular solo como excepción futura | `anulada` |
| `rechazada` | Duplicar como nuevo borrador futuro | Nueva cotización `borrador` |
| `anulada` | Ninguna | Ninguno |

Duplicar una cotización rechazada no es una transición de estado. Es una creación nueva basada en datos previos.

## Qué NO se implementó

- No se creó PHP nuevo.
- No se modificó PHP existente.
- No se modificó CSS.
- No se creó SQL.
- No se creó base de datos.
- No se crearon migraciones.
- No se creó CRUD.
- No se crearon controllers.
- No se crearon repositories.
- No se crearon models.
- No se implementó auditoría real.
- No se implementaron cambios de estado reales.
- No se implementó CSRF.
- No se implementaron permisos.
- No se implementó PDF.
- No se implementó correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar técnicamente la vista de detalle de una cotización, incluyendo qué acciones mostrar según estado, qué información queda bloqueada y cómo se presentará el historial futuro de cambios de estado.
