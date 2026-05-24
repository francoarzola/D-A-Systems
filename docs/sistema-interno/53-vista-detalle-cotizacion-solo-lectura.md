# Etapa 7A.9 - Vista detalle de cotización de solo lectura

## Objetivo

Crear una vista protegida para mostrar el detalle real de una cotización específica y sus detalles asociados, manteniendo el módulo en modo solo lectura.

Esta etapa conecta lectura real adicional, pero no implementa creación, edición, eliminación, emisión, `POST` ni CRUD completo.

## Archivos creados

- `sistema/public/cotizacion-detalle.php`
- `docs/sistema-interno/53-vista-detalle-cotizacion-solo-lectura.md`

## Archivos modificados

- `sistema/app/Repositories/QuoteRepository.php`
- `sistema/public/cotizaciones.php`

## Métodos agregados al repositorio

### findDetailsByQuoteId(int $quoteId): array

Obtiene los detalles asociados a una cotización desde `cotizacion_detalles`.

Reglas:

- Si `$quoteId <= 0`, retorna un arreglo vacío.
- Usa consulta preparada.
- Ordena por `numero_linea ASC, id ASC`.
- Solo ejecuta `SELECT`.

## Ajuste en findById

`findById()` mantiene su comportamiento de solo lectura, pero ahora devuelve más campos de cabecera para alimentar la vista detalle:

- contacto
- descripción
- descuentos
- IVA
- condiciones comerciales
- observaciones
- fechas de creación y actualización

## Ruta de la vista detalle

La nueva ruta es:

`sistema/public/cotizacion-detalle.php?id={id}`

La página usa `InternalPage::render()` y mantiene la navegación activa en `cotizaciones`.

## Datos reales mostrados

La vista muestra datos reales de la cabecera:

- número de cotización o `Sin emitir`
- estado
- fecha
- validez
- total
- nombre cliente
- RUT
- contacto
- correo
- teléfono
- descripción
- condiciones comerciales
- observaciones
- subtotal neto
- descuento
- IVA
- total
- creado en
- actualizado en

También muestra detalles reales:

- línea
- descripción
- cantidad
- unidad
- precio unitario neto
- descuento
- total línea

Toda salida dinámica se escapa con `htmlspecialchars`.

Los montos se muestran en formato simple de pesos chilenos, por ejemplo `$1.428.000`.

## Manejo de errores

La vista muestra mensajes profesionales sin detalles técnicos:

- Si `id` no existe o no es válido: `La cotización solicitada no es válida.`
- Si no se encuentra la cotización: `No se encontró la cotización solicitada.`
- Si falla conexión o lectura: `No fue posible cargar el detalle de la cotización.`

No se muestra stack trace, credenciales, host ni nombre de base de datos.

## Cambio en el listado

En `cotizaciones.php`, la acción visual `Ver detalle futuro` pasa a ser un enlace real:

`cotizacion-detalle.php?id={id}`

El enlace se muestra como `Ver detalle`.

## Acciones que siguen siendo visuales/no funcionales

En la vista detalle:

- `Volver al listado` es un enlace real a `cotizaciones.php`.
- `Editar futuro` es solo visual.
- `Emitir futuro` es solo visual.

No hay formularios ni acciones de escritura.

## Qué NO se implementó

- No se insertan datos.
- No se actualizan datos.
- No se eliminan datos.
- No se implementa `POST`.
- No se crean formularios funcionales.
- No se implementa guardar borrador.
- No se implementa emisión.
- No se implementan cambios de estado.
- No se implementa PDF.
- No se implementa correo.
- No se crean controllers.
- No se crean services.
- No se modifica estructura de base de datos.
- No se ejecuta SQL DDL.
- No se toca `cotizacion_correlativos`.
- No se modifican login, logout, AuthGuard, SessionManager ni timeout.

## Próxima etapa recomendada

La siguiente etapa recomendada es preparar la edición visual/controlada de borradores o una capa de servicio para separar reglas de lectura y futuras reglas de escritura antes de implementar `POST`.
