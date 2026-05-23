# Etapa 6D.27 — Diseño técnico del flujo de emisión de cotizaciones

## Objetivo del flujo de emisión

Definir cómo una cotización futura pasará desde estado `borrador` a estado `emitida`, asignando un `quote_number` único y formal solo en el momento de emisión.

El objetivo es documentar validaciones estrictas, control de concurrencia, transacciones, manejo de errores y seguridad requerida antes de implementar PHP, SQL, base de datos, CRUD o correlativos reales.

Esta etapa es solo documental.

## Diferencia entre guardar borrador y emitir

Guardar borrador permite conservar trabajo en progreso con reglas mínimas, sin consumir número oficial de cotización.

Emitir cotización formaliza el documento comercial:

- Exige datos completos.
- Exige al menos un ítem válido.
- Recalcula totales en backend.
- Asigna `quote_number`.
- Cambia estado de `borrador` a `emitida`.
- Debe impedir edición libre posterior, salvo reglas futuras de trazabilidad.

La emisión es más sensible que el guardado de borrador porque genera un identificador oficial y cambia el estado comercial de la cotización.

## Precondiciones para emitir

Antes de emitir, el sistema futuro debe confirmar:

- Sesión válida.
- CSRF válido.
- Solicitud `POST`.
- `form_action=emitir_cotizacion`.
- Cotización existente.
- Cotización pertenece al contexto permitido para el usuario autenticado.
- Estado actual exactamente `borrador`.
- Datos mínimos completos.
- Al menos un ítem válido.
- Totales recalculables.
- `quote_number` actual en `NULL`.

Si cualquiera de estas condiciones falla, la emisión no debe ejecutarse.

## Validaciones estrictas

Validaciones mínimas para emitir:

- `client_name` obligatorio.
- `quote_date` obligatoria y válida.
- `valid_until` válida.
- `valid_until` no anterior a `quote_date`.
- Al menos un ítem completo.
- Cada ítem emitido debe tener descripción.
- `quantity > 0`.
- `unit_price_net >= 0`.
- `discount_amount >= 0`.
- `line_total_net >= 0` después de recalcular.
- `subtotal_net >= 0`.
- `tax_amount >= 0`.
- `total_amount >= 0`.
- `contact_email` válido si se informa.
- `status` actual debe ser `borrador`.

La validación de emisión no debe depender de validaciones de frontend ni de campos deshabilitados u ocultos.

## Flujo técnico propuesto

1. Recibir `POST` con `form_action=emitir_cotizacion`.
2. Validar sesión activa.
3. Validar token CSRF.
4. Identificar el borrador a emitir.
5. Cargar la cotización desde almacenamiento futuro.
6. Validar que exista.
7. Validar que su estado actual sea `borrador`.
8. Ignorar cualquier `quote_number` enviado desde frontend.
9. Normalizar datos de cabecera.
10. Normalizar ítems.
11. Validar reglas estrictas de emisión.
12. Recalcular totales en backend.
13. Iniciar transacción.
14. Bloquear o reservar el correlativo anual de forma segura.
15. Generar `quote_number` con formato `COT-YYYY-0001`.
16. Asignar `quote_number` a la cotización.
17. Cambiar `status` a `emitida`.
18. Guardar fecha de emisión si el modelo futuro agrega un campo como `issued_at`.
19. Guardar totales recalculados.
20. Confirmar transacción.
21. Redirigir al detalle de la cotización emitida.

Si ocurre un error desde el paso transaccional, se debe ejecutar rollback y no debe quedar una cotización parcialmente emitida.

## Regla de quote_number

Regla definida:

- Se genera solo al emitir.
- No se genera al crear borrador.
- No se recibe desde frontend.
- Si llega desde `POST`, debe ignorarse.
- Debe ser único.
- Formato sugerido: `COT-YYYY-0001`.
- El año del formato debe basarse en la fecha de emisión o en `quote_date`, según decisión futura; recomendación inicial: usar año de `quote_date` si representa la fecha formal de cotización.
- Una vez asignado, no debe poder modificarse por edición normal.
- Los estados posteriores (`enviada`, `aceptada`, `rechazada`, `anulada`) deben conservar el mismo número.

El sistema debe tratar `quote_number` como dato generado por backend, no como dato editable.

## Control de concurrencia

Existe riesgo de que dos usuarios emitan cotizaciones al mismo tiempo y ambos intenten tomar el mismo correlativo.

Ejemplo de riesgo:

1. Usuario A calcula siguiente número `COT-2026-0008`.
2. Usuario B calcula al mismo tiempo `COT-2026-0008`.
3. Ambos intentan guardar.
4. Se produce duplicidad o falla por índice único.

Para evitarlo, no se debe usar `MAX()+1` sin protección.

Opciones técnicas futuras:

- Usar una tabla de correlativos por año y bloquear la fila dentro de una transacción.
- Usar una secuencia o mecanismo nativo de la base de datos si está disponible.
- Usar índice único sobre `quote_number` como respaldo obligatorio.
- Reintentar emisión si falla por conflicto de correlativo, siempre dentro de reglas controladas.

Recomendación inicial:

- Crear en etapa futura una tabla conceptual de correlativos por tipo y año.
- Dentro de transacción, bloquear el registro del año actual.
- Incrementar el valor.
- Construir `quote_number`.
- Guardar cotización emitida.
- Confirmar transacción.

Esta etapa no crea tabla ni SQL; solo documenta la necesidad.

## Transacciones

La emisión debe ser atómica: o se completa entera o no se aplica nada.

Operaciones que deben ocurrir juntas:

- Validar y bloquear la cotización borrador.
- Reservar o incrementar correlativo.
- Asignar `quote_number`.
- Cambiar `status` a `emitida`.
- Guardar totales recalculados.
- Guardar fecha de emisión si existe campo futuro.
- Registrar auditoría futura si se implementa.

Rollback requerido cuando:

- Falla la reserva del correlativo.
- El `quote_number` entra en conflicto con un índice único.
- La cotización ya no está en estado `borrador`.
- Fallan cálculos o validaciones dentro del flujo.
- Falla cualquier operación de persistencia.

La transacción debe proteger tanto el correlativo como el cambio de estado.

## Cambios de estado

Transición permitida para esta etapa conceptual:

- `borrador` → `emitida`

No se debe permitir emitir cotizaciones en estos estados:

- `emitida`
- `enviada`
- `aceptada`
- `rechazada`
- `anulada`

Si una cotización ya fue emitida, el sistema debe impedir nueva emisión para evitar reasignación de número o duplicidad documental.

## Manejo de errores

### Validación fallida

Si faltan datos obligatorios o existen ítems inválidos, se debe volver al formulario o detalle editable del borrador con mensajes claros.

Ejemplos:

- Cliente obligatorio.
- Fecha inválida.
- Sin ítems completos.
- Total no recalculable.
- Email inválido.

### Cotización no encontrada

Si el identificador no existe, mostrar error controlado o redirigir al listado con mensaje.

No se debe revelar información interna del sistema.

### Estado inválido

Si la cotización no está en `borrador`, rechazar emisión y redirigir al detalle o listado con mensaje.

### Correlativo duplicado

Si ocurre conflicto de `quote_number`, debe hacerse rollback.

Luego se puede:

- Reintentar una vez usando nueva reserva segura.
- Mostrar error genérico y registrar evento técnico.

La estrategia exacta queda pendiente para la implementación real.

### Error de persistencia

Ante error de base de datos o transacción, revertir cambios y registrar evento técnico futuro.

El usuario debe recibir un mensaje claro sin detalles sensibles.

## Redirecciones futuras

### Emisión correcta

Redirigir al detalle de la cotización emitida:

`cotizacion-detalle.php?id={id}&status=emitida`

Si la página de detalle aún no existe, redirigir al listado:

`cotizaciones.php?status=cotizacion_emitida`

### Error de validación

Volver al formulario o detalle editable del borrador, conservando mensajes de error y datos ingresados cuando sea posible.

### Sesión expirada

Redirigir a `login.php`, manteniendo el comportamiento de protección de sesión del sistema interno.

## Seguridad

Reglas requeridas:

- Exigir sesión válida.
- Validar CSRF antes de procesar emisión.
- Validar todo en backend.
- No confiar en `quote_number` enviado desde frontend.
- No confiar en totales enviados desde frontend.
- Recalcular montos siempre en backend.
- Escapar salida al mostrar datos.
- No mostrar errores técnicos sensibles al usuario final.
- Registrar usuario que emite cuando exista relación con usuarios internos.
- Registrar auditoría futura de emisión, cambio de estado y número asignado.
- Usar transacciones y bloqueo de correlativo.
- Mantener índice único sobre `quote_number` como última defensa.

## Qué NO se implementó

- No se creó PHP nuevo.
- No se modificó PHP existente.
- No se modificó CSS.
- No se creó base de datos.
- No se creó SQL.
- No se crearon migraciones.
- No se creó CRUD.
- No se crearon controllers.
- No se crearon repositories.
- No se crearon models.
- No se implementó `POST` real.
- No se guardaron datos.
- No se implementaron correlativos reales.
- No se implementó CSRF.
- No se implementó PDF.
- No se implementó correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar técnicamente el detalle de cotización emitida y sus cambios posteriores de estado, especialmente `emitida` → `enviada`, `aceptada`, `rechazada` y `anulada`, incluyendo auditoría y restricciones de edición.
