# Etapa 7A.10 - Helpers de formato reutilizables

## Objetivo

Extraer helpers de escape y formato visual a una clase reutilizable para evitar duplicación entre el listado y el detalle de cotizaciones, sin cambiar funcionalidad ni diseño visual.

## Archivo helper creado

`sistema/app/Support/ViewFormatter.php`

Namespace:

`DAndASystems\Internal\Support`

La clase es `final` y expone métodos estáticos para uso simple desde vistas PHP internas.

## Métodos disponibles

- `e(mixed $value): string`
- `text(mixed $value): string`
- `quoteNumber(mixed $value): string`
- `quoteDate(mixed $value): string`
- `quoteStatus(mixed $value): string`
- `money(mixed $value): string`
- `percent(mixed $value): string`
- `quantity(mixed $value): string`

## Reglas de formato

- `e()` escapa con `htmlspecialchars`, `ENT_QUOTES` y `UTF-8`.
- `text(null)` devuelve `Pendiente`.
- `quoteNumber(null)` devuelve `Sin emitir`.
- `quoteDate(null)` devuelve `Pendiente`.
- `quoteStatus(null)` devuelve `Sin estado`.
- `money(null)` devuelve `$0`.
- `percent(null)` devuelve `0%`.
- `quantity(null)` devuelve `0`.

## Archivos modificados

- `sistema/public/cotizaciones.php`
- `sistema/public/cotizacion-detalle.php`

Ambas páginas cargan `ViewFormatter` y reemplazan las funciones locales por llamadas estáticas al helper.

## Duplicación eliminada

Se eliminaron funciones locales repetidas como:

- `e()`
- `formatQuoteNumber()`
- `formatQuoteDate()`
- `formatQuoteStatus()`
- `formatQuoteMoney()`
- `formatPercent()`
- `formatQuantity()`
- `formatText()`

La salida dinámica sigue escapada antes de imprimirse.

## Qué NO se implementó

- No se cambiaron consultas.
- No se modificó `QuoteRepository`.
- No se modificó base de datos.
- No se ejecutó SQL.
- No se insertaron datos.
- No se actualizaron datos.
- No se eliminaron datos.
- No se implementó `POST`.
- No se implementó CRUD.
- No se crearon formularios funcionales.
- No se implementó edición.
- No se implementó emisión.
- No se implementó PDF.
- No se implementó correo.
- No se crearon controllers.
- No se crearon services.
- No se modificaron login, logout, AuthGuard, SessionManager ni timeout.

## Próxima etapa recomendada

La siguiente etapa recomendada es evaluar si conviene extraer componentes de vista simples para bloques repetidos, como tablas de cotizaciones o resumen de totales, manteniendo el sistema sin framework.
