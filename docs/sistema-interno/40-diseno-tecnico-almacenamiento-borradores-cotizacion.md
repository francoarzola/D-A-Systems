# Etapa 6D.26 — Diseño técnico del almacenamiento de borradores de cotización

## Objetivo del flujo de borradores

Definir cómo se almacenarán borradores de cotización en una etapa futura, manteniendo el sistema preparado para guardar trabajo incompleto sin consumir números oficiales de cotización.

El objetivo técnico es dejar claro el flujo de `POST`, las responsabilidades por capa, las validaciones mínimas, el tratamiento de ítems, el recálculo de totales y la regla de `quote_number = NULL` mientras la cotización permanezca en estado `borrador`.

Esta etapa es solo documental. No implementa persistencia, SQL, formularios reales ni lógica de negocio.

## Diferencia entre maqueta actual y flujo futuro real

La pantalla actual `sistema/public/cotizaciones.php` es una maqueta visual estática:

- No tiene formulario real.
- No envía `POST`.
- No guarda datos.
- No calcula totales.
- No consulta base de datos.
- No ejecuta acciones al presionar los botones visuales.

El flujo futuro real deberá convertir la sección "Nueva cotización" en una captura protegida, con envío controlado, validación backend, recálculo de montos y persistencia en tablas futuras de `cotizaciones` y `cotizacion_items`.

## Ruta o archivo futuro para procesar POST

Propuesta inicial: crear un archivo público protegido específico para guardar borradores:

`sistema/public/cotizaciones-guardar.php`

Razones:

- Mantiene `cotizaciones.php` enfocado en mostrar listado, detalle y maqueta o formulario.
- Separa la recepción de `POST` del renderizado principal.
- Permite redirigir después de guardar usando patrón POST/Redirect/GET.
- Reduce el riesgo de reenviar formularios al recargar la página.

La página `cotizaciones.php` podría seguir siendo la pantalla de entrada del formulario, mientras `cotizaciones-guardar.php` procesaría acciones como `guardar_borrador` y, en una etapa posterior, podría separarse la emisión a otro endpoint si el flujo crece.

Decisión pendiente: confirmar si se mantendrá un único procesador de acciones o si se separarán archivos para guardar borrador y emitir cotización.

## Responsabilidades futuras por capa

### Página pública protegida

Responsabilidades esperadas:

- Cargar sesión.
- Exigir autenticación.
- Validar que el método sea `POST`.
- Validar token CSRF cuando exista.
- Leer `form_action`.
- Delegar validación y persistencia a capas internas.
- Redirigir con resultado.

Archivo sugerido: `sistema/public/cotizaciones-guardar.php`.

### Validación

Responsabilidades esperadas:

- Normalizar valores recibidos.
- Validar campos mínimos.
- Validar estructura de `items`.
- Separar errores de cabecera y errores de ítems.
- Rechazar acciones no permitidas.
- Preparar datos limpios para el servicio.

Ubicación futura posible: una clase simple dentro de `sistema/app/Support` o una carpeta de módulo si se define convención más adelante.

### Servicio de cotizaciones

Responsabilidades esperadas:

- Orquestar el guardado de borradores.
- Definir estado `borrador`.
- Garantizar que `quote_number` permanezca `NULL`.
- Recalcular totales usando datos normalizados.
- Coordinar guardado de cabecera e ítems.
- Definir respuesta de éxito o error de negocio.

Ubicación futura posible: `sistema/app/Services/QuoteService.php` o equivalente, si el proyecto adopta esa convención.

### Repositorio o acceso a datos

Responsabilidades esperadas:

- Insertar o actualizar la cabecera en `cotizaciones`.
- Insertar, actualizar o reemplazar ítems en `cotizacion_items`.
- Ejecutar operaciones dentro de transacción.
- Devolver identificador del borrador guardado.
- No contener reglas de negocio, salvo las necesarias para persistencia.

Ubicación futura posible: `sistema/app/Repositories/QuoteRepository.php` o una clase simple de acceso a datos cuando exista convención.

### Base de datos

Responsabilidades esperadas:

- Mantener integridad referencial entre cotización e ítems.
- Permitir `quote_number = NULL` en borrador.
- Asegurar unicidad de `quote_number` para cotizaciones emitidas.
- Guardar timestamps de creación y actualización.

Esta etapa no crea tablas ni SQL.

## Flujo técnico propuesto

1. El usuario autenticado abre la pantalla "Nueva cotización".
2. El formulario futuro envía `POST` con `form_action=guardar_borrador`.
3. `cotizaciones-guardar.php` valida que exista sesión activa.
4. Se valida token CSRF.
5. Se verifica que `form_action` sea una acción permitida.
6. Se normalizan datos de cabecera.
7. Se normalizan líneas de ítems.
8. Se eliminan líneas completamente vacías.
9. Se validan reglas mínimas de borrador.
10. Se recalculan totales en backend.
11. Se guarda cabecera en tabla futura `cotizaciones` con estado `borrador`.
12. Se guarda cada ítem válido en tabla futura `cotizacion_items`.
13. Se mantiene `quote_number` en `NULL`.
14. Se confirma la transacción.
15. Se redirige al detalle del borrador o al listado con mensaje de éxito.

Si ocurre un error de validación, el flujo debe volver al formulario con mensajes claros y, si es posible, conservar los datos ingresados.

## Validaciones mínimas para borrador

Validaciones sugeridas:

- Sesión válida.
- CSRF válido.
- Método `POST`.
- `form_action` permitido.
- `quote_date` válida si se informa; recomendable como obligatoria mínima.
- `client_name` recomendado como obligatorio mínimo para identificar el borrador.
- `valid_until` no anterior a `quote_date` cuando ambas fechas existan.
- `contact_email` con formato válido si se informa.
- Ítems opcionales para borrador, siempre que se permita guardar una cabecera inicial.
- Si se informa un ítem parcial, debe validarse como línea incompleta y generar advertencia o error.
- `quantity` mayor que cero cuando exista una línea válida.
- `unit_price_net` mayor o igual a cero cuando exista una línea válida.
- `discount_amount` mayor o igual a cero cuando exista una línea válida.

Decisión recomendada para primera versión: permitir guardar borrador con cabecera mínima y sin ítems, pero exigir ítems completos para emitir.

## Reglas para quote_number

Reglas definidas:

- `quote_number` permanece `NULL` mientras la cotización está en estado `borrador`.
- `quote_number` no se recibe desde `POST`.
- Si el navegador envía un campo `quote_number`, debe ignorarse.
- No se genera correlativo al guardar borrador.
- El correlativo se genera solo al emitir la cotización.
- Desde estado `emitida`, `quote_number` debe ser obligatorio y único.

Esto evita consumir números oficiales para borradores incompletos o descartados.

## Reglas para ítems

Reglas sugeridas:

- Ignorar líneas completamente vacías.
- Detectar líneas parcialmente completas.
- Rechazar o marcar error cuando una línea tenga descripción sin cantidad o precio, o cantidad/precio sin descripción.
- Normalizar `line_number` en backend según el orden final de líneas válidas.
- No confiar en `line_number` enviado desde frontend.
- Recalcular `line_subtotal_net` como `quantity * unit_price_net`.
- Recalcular `line_total_net` como `line_subtotal_net - discount_amount`.
- No permitir que `line_total_net` sea negativo.
- Guardar solo ítems válidos.

Para edición futura de borradores, se deberá decidir si los ítems se reemplazan completamente en cada guardado o si se actualizan por identificador.

## Reglas de totales

Reglas sugeridas:

- No confiar en totales enviados desde frontend.
- Normalizar montos antes de calcular.
- Convertir separadores visuales a formato numérico interno.
- Calcular subtotal desde ítems válidos.
- Aplicar descuento global si el formulario futuro lo incorpora.
- Calcular IVA en backend según tasa vigente o configuración definida.
- Calcular total final en backend.
- Redondear de forma consistente a dos decimales.
- Guardar totales calculados junto con el borrador para facilitar listado y revisión.

Si el borrador no tiene ítems válidos, los totales deberían guardarse como `0.00`.

## Manejo de errores

### Errores de validación

Cuando existan campos inválidos, el sistema debe volver al formulario con mensajes asociados a campos o secciones.

Ejemplos:

- Cliente requerido.
- Fecha inválida.
- Fecha de validez anterior a la fecha de cotización.
- Ítem incompleto.
- Cantidad o precio inválido.

### Datos inválidos

Los datos con formato inesperado deben normalizarse o rechazarse. El backend no debe asumir que el navegador envía lo que muestra la interfaz.

### CSRF inválido

Si el token CSRF es inválido o falta, se debe rechazar la solicitud y mostrar un mensaje genérico. No se debe guardar ningún dato.

### Sesión expirada

Si la sesión expiró, el usuario debe ser redirigido a `login.php`, manteniendo el comportamiento de seguridad existente.

### Errores de persistencia futura

Si falla la base de datos, la operación debe revertirse con transacción y registrar un evento técnico para revisión posterior.

## Redirecciones futuras

### Guardado correcto

Opciones posibles:

- Redirigir al detalle del borrador: `cotizacion-detalle.php?id={id}`.
- Redirigir al listado con mensaje: `cotizaciones.php?status=borrador_guardado`.

Recomendación inicial: redirigir al detalle del borrador cuando exista página de detalle real. Mientras no exista, usar listado con mensaje.

### Error de validación

Opciones posibles:

- Volver a la pantalla de formulario con errores y datos ingresados.
- Guardar errores en sesión temporal y redirigir usando POST/Redirect/GET.

Recomendación: usar redirección con datos temporales de sesión si se mantiene un enfoque simple sin framework.

### Cancelar

La acción `cancelar` no debería guardar datos.

Opciones posibles:

- Volver al listado.
- Volver al detalle si se estaba editando un borrador existente.
- Confirmar abandono si hay cambios sin guardar, cuando exista JavaScript futuro.

## Seguridad

Reglas futuras:

- Mantener sesión obligatoria para acceder y guardar borradores.
- Validar CSRF antes de procesar datos.
- Validar todo en backend.
- Escapar salida al volver a mostrar datos ingresados.
- No confiar en campos ocultos, deshabilitados o calculados por frontend.
- Ignorar cualquier `quote_number` recibido desde navegador.
- Registrar usuario creador mediante `created_by` cuando exista relación con usuarios.
- Considerar logging futuro para guardado, emisión, errores de validación repetidos y fallas de persistencia.
- Usar transacciones para guardar cabecera e ítems de forma consistente.

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
- No se implementaron cálculos reales.
- No se implementó CSRF.
- No se implementó PDF.
- No se implementó correo.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar técnicamente el flujo de emisión de cotizaciones, incluyendo validaciones estrictas, asignación segura de `quote_number`, transición de estado `borrador` a `emitida` y control de concurrencia para evitar correlativos duplicados.
