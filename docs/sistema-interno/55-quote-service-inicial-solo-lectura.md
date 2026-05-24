# Etapa 7A.11 - QuoteService inicial de solo lectura

## Objetivo

Crear una capa de servicio inicial para Cotizaciones, de solo lectura, que concentre la lógica de aplicación entre las páginas públicas internas y `QuoteRepository`.

Esta etapa mantiene el sistema sin framework y no agrega acciones de escritura.

## Archivo service creado

`sistema/app/Services/QuoteService.php`

Namespace:

`DAndASystems\Internal\Services`

El servicio recibe `QuoteRepository` por constructor.

## Métodos disponibles

### countQuotes(): int

Devuelve el total de cotizaciones usando `QuoteRepository::countAll()`.

### getRecentQuotes(int $limit = 10): array

Devuelve cotizaciones recientes usando `QuoteRepository::findRecent()`.

### getQuoteDetail(int $id): ?array

Valida que el ID sea positivo, carga la cabecera con `findById()` y carga los detalles con `findDetailsByQuoteId()`.

Cuando encuentra datos, devuelve:

```php
[
    'quote' => $quote,
    'details' => $details,
]
```

Si el ID no es válido o no existe la cotización, devuelve `null`.

## Herramienta CLI creada

`sistema/tools/check-quote-service.php`

La herramienta:

- se ejecuta solo por CLI
- carga `DatabaseConfig`
- crea `Connection`
- crea `QuoteRepository`
- crea `QuoteService`
- ejecuta `countQuotes()`
- ejecuta `getRecentQuotes(5)`
- si hay registros, intenta leer el detalle del primero

No expone credenciales ni stack trace.

## Archivos modificados

- `sistema/public/cotizaciones.php`
- `sistema/public/cotizacion-detalle.php`

Ambas páginas siguen protegidas con `InternalPage::render()`, pero ahora llaman a `QuoteService` en vez de usar `QuoteRepository` directamente.

## Qué se simplificó

La lógica de aplicación de lectura queda concentrada en el servicio:

- conteo de cotizaciones
- listado reciente
- detalle compuesto por cabecera y detalles

El repositorio sigue siendo la capa de acceso a datos.

## Qué NO se implementó

- No se cambiaron consultas SQL.
- No se modificó estructura de base de datos.
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
- No se modificaron login, logout, AuthGuard, SessionManager ni timeout.

## Próxima etapa recomendada

La siguiente etapa recomendada es comenzar a preparar una capa de validación para futuras acciones de borrador, sin activar todavía escritura desde páginas públicas.
