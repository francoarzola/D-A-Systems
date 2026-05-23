# Etapa 6D.30 — Diseño técnico de auditoría e historial de cotizaciones

## Objetivo de auditoría/historial

Definir técnicamente la futura auditoría o historial comercial de cotizaciones, incluyendo eventos mínimos, tabla conceptual, campos sugeridos, reglas de registro, visualización en la vista detalle y seguridad.

El objetivo es preparar una trazabilidad clara del ciclo de vida de una cotización, desde la creación del borrador hasta su emisión, envío, aceptación, rechazo, anulación o duplicación futura.

Esta etapa es solo documental. No implementa auditoría real, logging real, SQL ni lógica funcional.

## Diferencia entre historial comercial y logging técnico

El historial comercial es visible para usuarios internos autorizados y explica qué ocurrió con una cotización.

Ejemplos:

- Se creó el borrador.
- Se emitió la cotización.
- Se marcó como enviada.
- Se aceptó.
- Se rechazó.
- Se anuló con motivo.

El logging técnico registra eventos del sistema para diagnóstico, seguridad o soporte.

Ejemplos:

- Error de base de datos.
- Falla de persistencia.
- CSRF inválido.
- Intento de transición no permitida.
- Excepción interna.

Ambos conceptos pueden relacionarse, pero no deben mezclarse necesariamente. El historial comercial debe ser legible y útil para operación; el logging técnico puede contener detalles internos que no deben mostrarse en la vista detalle.

## Eventos mínimos a registrar

Eventos comerciales mínimos:

- Creación de borrador.
- Actualización de borrador.
- Emisión.
- Envío.
- Aceptación.
- Rechazo.
- Anulación.
- Duplicación futura como nuevo borrador.

Eventos relevantes opcionales:

- Intento fallido de emisión por validación.
- Intento fallido de cambio de estado.
- Intento de acción no autorizada.

Los intentos fallidos pueden registrarse en logging técnico y solo pasar al historial comercial si aportan valor operativo y no exponen información sensible.

## Tabla conceptual sugerida

Tabla conceptual:

`cotizacion_historial`

Propósito:

- Registrar eventos relevantes asociados a una cotización.
- Mostrar un resumen comprensible en la vista detalle.
- Apoyar trazabilidad futura de cambios de estado y acciones comerciales.

Esta etapa no crea tabla real ni SQL.

## Campos sugeridos

| Campo | Tipo sugerido | Obligatorio | Descripción |
| --- | --- | --- | --- |
| `id` | entero autoincremental | Sí | Identificador del evento. |
| `quote_id` | entero | Sí | Cotización asociada. |
| `event_type` | varchar(40) | Sí | Tipo de evento registrado. |
| `previous_status` | varchar(20) | No | Estado anterior cuando aplica. |
| `new_status` | varchar(20) | No | Estado nuevo cuando aplica. |
| `user_id` | entero | No | Usuario interno responsable del evento cuando exista relación con usuarios. |
| `event_at` | datetime | Sí | Fecha y hora del evento. |
| `reason` | varchar(160) | No | Motivo breve, especialmente para rechazo o anulación. |
| `note` | text | No | Observación adicional del usuario. |
| `source` | varchar(40) | Sí | Origen del evento, por ejemplo `internal_panel`. |
| `metadata` | json/text | No | Datos adicionales controlados para soporte o contexto. |

Campos derivados o de presentación, como nombre de usuario, deberían obtenerse por relación futura o prepararse al renderizar la vista.

## Tipos de evento sugeridos

Tipos iniciales:

- `draft_created`
- `draft_updated`
- `quote_issued`
- `quote_sent`
- `quote_accepted`
- `quote_rejected`
- `quote_cancelled`
- `quote_duplicated`

Tipos opcionales para seguridad o operación:

- `invalid_transition_attempt`
- `issue_validation_failed`
- `unauthorized_action_attempt`

Estos tipos opcionales deben evaluarse con cuidado para decidir si pertenecen al historial visible o al logging técnico.

## Reglas de registro

Reglas generales:

- Registrar eventos relevantes para el ciclo comercial de la cotización.
- Registrar usuario responsable cuando exista.
- Registrar estado anterior y estado nuevo cuando aplique.
- Registrar motivo obligatorio para anulación.
- Registrar motivo recomendado para rechazo.
- No registrar datos sensibles innecesarios.
- No duplicar información completa de la cotización en `metadata`.
- Usar mensajes y tipos de evento consistentes.
- No confiar en valores de estado enviados desde frontend.

Cuando el evento acompaña un cambio crítico, debe registrarse dentro de la misma transacción que actualiza la cotización.

Ejemplo:

- Cambiar `status` de `enviada` a `aceptada`.
- Insertar evento `quote_accepted`.
- Confirmar ambas operaciones juntas.

Si falla el historial de un evento crítico, debe fallar la operación completa o definirse explícitamente una estrategia de recuperación. Recomendación inicial: rollback completo para eventos críticos.

## Eventos críticos transaccionales

Los siguientes eventos deben registrarse dentro de la misma transacción que modifica la cotización:

- Emisión.
- Anulación.
- Aceptación.
- Rechazo.

También se recomienda transacción para:

- Envío.
- Duplicación futura como nuevo borrador.

Motivo:

- Evitar que una cotización cambie de estado sin historial.
- Evitar historial que indique un cambio que no ocurrió.
- Mantener trazabilidad coherente.

## Reglas por evento

### draft_created

Cuándo registrar:

- Al crear un borrador por primera vez.

Datos sugeridos:

- `previous_status`: `NULL`.
- `new_status`: `borrador`.
- `reason`: opcional.
- `note`: opcional.

### draft_updated

Cuándo registrar:

- Al guardar cambios relevantes en un borrador.

Recomendación:

- No registrar cada cambio menor si puede generar ruido.
- Evaluar registrar solo guardados explícitos.

### quote_issued

Cuándo registrar:

- Al pasar de `borrador` a `emitida`.

Datos sugeridos:

- `previous_status`: `borrador`.
- `new_status`: `emitida`.
- `metadata`: número asignado, si se considera seguro.

Debe ser transaccional.

### quote_sent

Cuándo registrar:

- Al pasar de `emitida` a `enviada`.

Datos sugeridos:

- `previous_status`: `emitida`.
- `new_status`: `enviada`.
- `note`: canal de envío futuro si aplica.

### quote_accepted

Cuándo registrar:

- Al pasar de `enviada` a `aceptada`.

Datos sugeridos:

- `previous_status`: `enviada`.
- `new_status`: `aceptada`.
- `note`: observación comercial opcional.

Debe ser transaccional.

### quote_rejected

Cuándo registrar:

- Al pasar de `enviada` a `rechazada`.

Datos sugeridos:

- `previous_status`: `enviada`.
- `new_status`: `rechazada`.
- `reason`: recomendado.
- `note`: observación opcional.

Debe ser transaccional.

### quote_cancelled

Cuándo registrar:

- Al pasar a `anulada`.

Datos sugeridos:

- `previous_status`: estado anterior.
- `new_status`: `anulada`.
- `reason`: obligatorio.
- `note`: observación opcional.

Debe ser transaccional.

### quote_duplicated

Cuándo registrar:

- Al crear una nueva cotización borrador basada en otra cotización.

Datos sugeridos:

- En la cotización original: registrar que fue duplicada.
- En la cotización nueva: registrar origen en `metadata` controlada.

La nueva cotización debe quedar en `borrador`, sin `quote_number`.

## Visualización futura en detalle

La vista `cotizacion-detalle.php?id={id}` debería mostrar un bloque de historial.

Orden recomendado:

- Descendente, con evento más reciente arriba.

Campos visibles sugeridos:

- Fecha/hora.
- Usuario.
- Mensaje amigable.
- Estado anterior y nuevo cuando aplique.
- Motivo.
- Observación.

Ejemplos de mensajes amigables:

- "Borrador creado."
- "Cotización emitida."
- "Cotización marcada como enviada."
- "Cotización aceptada por el cliente."
- "Cotización rechazada."
- "Cotización anulada."
- "Cotización duplicada como nuevo borrador."

La metadata técnica no debe mostrarse al usuario normal. Solo podría mostrarse a perfiles administrativos o usarse para soporte interno si se define en el futuro.

## Seguridad

Reglas futuras:

- Exigir sesión válida para ver historial.
- Validar permisos por rol cuando existan.
- Escapar toda salida HTML.
- No exponer información sensible.
- Controlar cuidadosamente qué se guarda en `metadata`.
- No guardar contraseñas, tokens, datos de sesión ni información técnica sensible.
- No confiar en `user_id`, estado o metadata enviados desde frontend.
- Registrar `user_id` desde la sesión autenticada o contexto backend.
- Evitar que usuarios sin permisos vean motivos u observaciones sensibles.

## Relación con logging técnico

El logging técnico debería cubrir eventos que no necesariamente pertenecen al historial comercial visible.

Ejemplos:

- Errores de sistema.
- Fallas de persistencia.
- Intentos inválidos de transición.
- CSRF inválido.
- Sesión expirada durante una acción.
- Excepciones no controladas.

Recomendación:

- Mantener historial comercial en `cotizacion_historial`.
- Mantener logging técnico en el mecanismo de logs del sistema o tabla técnica futura.
- Vincular ambos solo cuando sea útil, por ejemplo con un identificador de operación futuro.

## Manejo de errores

### Error registrando historial crítico

Si falla el registro de historial para emisión, aceptación, rechazo o anulación, se recomienda rollback completo.

### Error registrando historial no crítico

Para eventos informativos, como actualización de borrador, puede evaluarse si el guardado principal continúa y se registra un error técnico.

La primera versión debería ser conservadora: si el historial es parte del comportamiento esperado, fallar de forma controlada.

### Metadata inválida

Si la metadata no cumple estructura esperada, debe omitirse o normalizarse. No debe romper la acción comercial principal salvo que sea requerida.

### Usuario no disponible

Si todavía no existe relación formal con usuarios internos, `user_id` puede quedar `NULL`, pero el sistema debería avanzar hacia registrar usuario responsable.

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
- No se implementó historial real.
- No se implementó logging real.
- No se implementaron acciones reales.
- No se implementó PDF.
- No se implementó correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar técnicamente la generación futura de PDF de cotizaciones, incluyendo cuándo estará disponible, qué datos incluirá, cómo se protegerá el acceso y cómo se relacionará con estados `emitida` y posteriores.
