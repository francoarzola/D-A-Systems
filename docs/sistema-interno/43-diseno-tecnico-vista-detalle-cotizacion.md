# Etapa 6D.29 — Diseño técnico de vista detalle de cotización

## Objetivo de la vista detalle

Definir técnicamente la futura vista de detalle de una cotización, incluyendo información visible, acciones disponibles según estado, campos editables o bloqueados, historial futuro de cambios, manejo de errores y seguridad.

La vista detalle debe permitir revisar una cotización completa y ejecutar acciones coherentes con su estado, sin permitir modificaciones indebidas sobre cotizaciones emitidas, enviadas, aceptadas, rechazadas o anuladas.

Esta etapa es solo documental. No implementa una vista real.

## Ruta futura sugerida

Ruta propuesta:

`sistema/public/cotizacion-detalle.php?id={id}`

Razones:

- Mantiene una ruta simple dentro del sistema interno actual.
- Permite acceder al detalle por identificador.
- Separa el detalle de la página general `cotizaciones.php`.
- Prepara un punto natural para mostrar acciones futuras de emisión, envío, aceptación, rechazo, anulación o duplicado.

El parámetro `id` debe validarse siempre en backend. No se debe confiar en que el identificador recibido por URL sea válido o autorizado.

## Información que debe mostrar

La vista detalle debería mostrar una cabecera clara con:

- Número de cotización.
- Estado.
- Fecha.
- Fecha de validez.
- Cliente.
- RUT.
- Contacto.
- Correo.
- Teléfono.

También debe mostrar contenido comercial:

- Descripción.
- Condiciones comerciales.
- Observaciones.

Debe mostrar la tabla de ítems:

- Línea.
- Descripción.
- Cantidad.
- Unidad.
- Precio unitario neto.
- Descuento.
- Subtotal línea.
- Total línea.

Debe mostrar resumen de totales:

- Subtotal.
- Descuento.
- IVA.
- Total.

Para cotizaciones en `borrador`, el número puede mostrarse como pendiente o "Se asignará al emitir", porque `quote_number` permanece `NULL` hasta la emisión.

## Comportamiento según estado

### Borrador

La vista debe funcionar como detalle editable o permitir acceso a edición.

Comportamiento esperado:

- Mostrar datos actuales del borrador.
- Permitir edición de datos de cabecera.
- Permitir edición de ítems.
- Permitir guardar borrador.
- Permitir emitir si cumple validaciones estrictas.
- Mostrar `quote_number` como pendiente.

### Emitida

La cotización ya tiene número oficial.

Comportamiento esperado:

- Mostrar `quote_number`.
- Bloquear edición de datos comerciales e ítems.
- Permitir acciones de estado compatibles.
- Permitir marcar como enviada.
- Permitir anular con motivo.
- Mostrar acceso futuro a PDF.

### Enviada

La cotización fue entregada o informada al cliente.

Comportamiento esperado:

- Bloquear edición.
- Permitir marcar como aceptada.
- Permitir marcar como rechazada.
- Permitir anular con motivo.
- Mostrar acceso futuro a PDF.
- Mostrar fecha de envío futura si existe.

### Aceptada

La cotización fue aprobada por el cliente.

Comportamiento esperado:

- Solo lectura.
- Mostrar estado aceptada con fecha futura de aceptación si existe.
- Permitir anulación solo como excepción futura con permisos y motivo obligatorio.
- No permitir edición normal.

### Rechazada

La cotización no fue aceptada.

Comportamiento esperado:

- Solo lectura.
- Mostrar motivo futuro de rechazo si existe.
- Permitir duplicar como nuevo borrador solo como idea futura.
- No permitir volver a borrador ni reemitir la misma cotización.

### Anulada

La cotización fue invalidada.

Comportamiento esperado:

- Solo lectura.
- Mostrar motivo de anulación futuro.
- No permitir cambios de estado.
- No permitir edición.
- No permitir reactivar.

## Acciones visibles por estado

| Estado | Acciones visibles futuras |
| --- | --- |
| `borrador` | Editar, guardar borrador, emitir, cancelar |
| `emitida` | Marcar enviada, anular, ver PDF futuro |
| `enviada` | Aceptar, rechazar, anular, ver PDF futuro |
| `aceptada` | Ver, anular solo como excepción futura |
| `rechazada` | Ver, duplicar como nuevo borrador futuro |
| `anulada` | Solo ver |

Las acciones visibles no son autorización suficiente. El backend debe volver a validar estado, permisos y CSRF en cada acción.

## Reglas de edición

Reglas sugeridas:

- `borrador`: editable.
- `emitida`: bloqueada o con edición administrativa muy limitada.
- `enviada`: bloqueada.
- `aceptada`: bloqueada.
- `rechazada`: bloqueada.
- `anulada`: bloqueada.

Campos editables en borrador:

- `quote_date`
- `valid_until`
- `client_name`
- `client_rut`
- `contact_name`
- `contact_email`
- `contact_phone`
- `description`
- `commercial_terms`
- `notes`
- Ítems de cotización

Campos bloqueados siempre:

- `id`
- `quote_number`
- `status`, salvo mediante acciones controladas
- Totales calculados, salvo recálculo backend
- Campos de auditoría futura

Una cotización con `quote_number` asignado no debe permitir edición libre de datos comerciales en la primera versión.

## Historial futuro

La vista detalle debería reservar un bloque para historial de cambios cuando exista auditoría real.

Eventos a mostrar:

- Creación de borrador.
- Emisión.
- Envío.
- Aceptación.
- Rechazo.
- Anulación.
- Cambios administrativos relevantes.

Campos visibles sugeridos por evento:

- Estado anterior.
- Estado nuevo.
- Usuario.
- Fecha/hora.
- Motivo.
- Observación.

El historial debe mostrarse en orden descendente o ascendente de forma consistente. Recomendación inicial: descendente, con el evento más reciente arriba.

## Manejo de errores

### Cotización no encontrada

Si el `id` no corresponde a una cotización existente, redirigir al listado con mensaje genérico o mostrar página de error controlada.

### ID inválido

Si `id` no es numérico, está vacío o no cumple formato esperado, rechazar la solicitud antes de consultar datos.

### Acceso no autorizado

Si el usuario no tiene permiso futuro para ver la cotización, mostrar error controlado o redirigir al dashboard/listado.

No se deben revelar detalles de cotizaciones ajenas o no autorizadas.

### Sesión expirada

Redirigir a `login.php`, manteniendo la protección existente del sistema interno.

### Estado no permitido para acción

Si el usuario intenta ejecutar una acción no compatible con el estado actual, rechazar la operación y volver al detalle con mensaje.

Ejemplo:

- Intentar aceptar una cotización `emitida` que aún no está `enviada`.
- Intentar editar una cotización `anulada`.

## Redirecciones futuras

### Detalle correcto

Mostrar:

`cotizacion-detalle.php?id={id}`

### Acción correcta

Después de una acción, redirigir al mismo detalle:

`cotizacion-detalle.php?id={id}&status=accion_correcta`

### Error de validación o acción

Redirigir al detalle con mensaje de error:

`cotizacion-detalle.php?id={id}&error=accion_no_permitida`

### Cotización no encontrada

Redirigir al listado:

`cotizaciones.php?error=cotizacion_no_encontrada`

### Sesión expirada

Redirigir a:

`login.php`

## Seguridad

Reglas futuras:

- Exigir sesión válida.
- Validar `id` en backend.
- No confiar en `id` ni `status` enviados desde frontend.
- Consultar estado real desde base de datos antes de mostrar acciones.
- Validar permisos por rol cuando existan.
- Usar CSRF para acciones de cambio de estado o guardado.
- Escapar toda salida HTML.
- No confiar en totales enviados desde frontend.
- No permitir edición directa de `quote_number`.
- No mostrar acciones que no correspondan al estado, pero validar igualmente en backend.
- Registrar auditoría futura de acciones relevantes.

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
- No se implementó vista real.
- No se implementaron acciones reales.
- No se implementó historial real.
- No se implementó PDF.
- No se implementó correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar técnicamente la estructura de auditoría o historial de cotizaciones, definiendo tablas conceptuales, eventos mínimos, campos requeridos y reglas para registrar acciones relevantes sin implementar todavía SQL ni lógica real.
