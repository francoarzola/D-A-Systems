# Etapa 7A.7 - Listado de cotizaciones con lectura real

## Objetivo

Conectar la pagina interna `cotizaciones.php` al repositorio de solo lectura para mostrar registros reales desde la tabla `cotizaciones`, sin implementar creacion, edicion, eliminacion, `POST` ni CRUD completo.

## Archivos modificados

- `sistema/public/cotizaciones.php`

## Archivos creados

- `docs/sistema-interno/51-listado-cotizaciones-lectura-real.md`

## Integracion con QuoteRepository

La pagina `cotizaciones.php` mantiene `InternalPage::render()`.

La lectura de base de datos ocurre dentro del callback de contenido, por lo que el flujo sigue siendo:

1. iniciar sesion
2. exigir autenticacion
3. preparar contenido de la pagina
4. cargar layout interno

Dentro del callback se cargan:

- `DatabaseConfig`
- `Connection`
- `QuoteRepository`

La pagina usa:

- `countAll()` para mostrar el total de cotizaciones registradas.
- `findRecent(10)` para mostrar hasta 10 cotizaciones recientes.

## Datos reales mostrados

El listado real muestra:

- numero de cotizacion o `Sin emitir`
- nombre del cliente
- fecha de cotizacion
- fecha de validez
- estado
- total
- accion visual no funcional `Ver detalle futuro`

Toda la salida dinamica se escapa con `htmlspecialchars`.

Los montos se formatean de forma simple con separador de miles.

## Estado vacio

Si la tabla existe pero no hay registros, la pagina muestra:

`Aun no hay cotizaciones registradas.`

No se generan datos ficticios para llenar el listado real.

## Manejo de errores

Si falla la conexion o lectura de cotizaciones, se muestra un mensaje generico:

`No fue posible cargar el listado de cotizaciones.`

No se imprimen credenciales, host, nombre de base de datos, stack trace ni detalles tecnicos en pantalla.

El error no rompe el layout completo.

## Que sigue siendo maqueta

Se mantiene como referencia visual/no funcional:

- la seccion `Nueva cotizacion`
- los botones visuales `Guardar borrador`, `Emitir cotizacion` y `Cancelar`
- la vista de referencia del detalle
- la tabla de items de referencia
- las acciones del listado

## Que NO se implemento

- No se insertan datos.
- No se actualizan datos.
- No se eliminan datos.
- No se crean formularios funcionales.
- No se implementa `POST`.
- No se crea endpoint de guardado.
- No se implementa guardar borrador.
- No se implementa emision.
- No se implementan cambios de estado.
- No se implementa PDF.
- No se implementa correo.
- No se crean controllers.
- No se crean services.
- No se modifica base de datos.
- No se ejecuta SQL.
- No se modifican login, logout, AuthGuard, SessionManager ni timeout.

## Proxima etapa recomendada

La siguiente etapa recomendada es conectar una vista de detalle de solo lectura para una cotizacion especifica, manteniendo acciones no funcionales hasta definir la capa de servicios y validaciones de escritura.
