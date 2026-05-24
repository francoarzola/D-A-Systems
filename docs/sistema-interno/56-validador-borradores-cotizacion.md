# Etapa 7A.12 - Validador de borradores de cotización

## Objetivo

Crear una clase validadora para datos de borrador de cotización, sin guardar información y sin conectar formularios todavía.

La validación queda fuera de páginas públicas y prepara una futura acción de guardar borrador.

## Archivo creado

`sistema/app/Validation/QuoteDraftValidator.php`

Namespace:

`DAndASystems\Internal\Validation`

## Método principal

`validateDraft(array $data): array`

Devuelve una estructura simple:

```php
[
    'valid' => true,
    'errors' => [],
    'warnings' => [],
]
```

## Reglas implementadas

- `form_action`, si viene informado, debe ser `guardar_borrador`.
- `quote_number` no se acepta como dato de borrador; si viene informado, genera advertencia.
- `client_name` es obligatorio.
- `quote_date` es obligatoria y debe tener formato `Y-m-d`.
- `valid_until`, si viene informada, debe tener formato `Y-m-d`.
- `valid_until` no puede ser anterior a `quote_date`.
- `contact_email`, si viene informado, debe tener formato válido.
- `items` debe ser una lista si viene informado.
- Líneas de ítem completamente vacías se ignoran.
- Ítems parciales generan errores.
- La descripción del ítem es obligatoria cuando la línea tiene datos.
- La cantidad debe ser mayor que cero.
- El precio unitario neto debe ser mayor o igual a cero.
- El descuento debe ser mayor o igual a cero.
- El total de línea calculado no puede ser negativo.
- La unidad faltante genera advertencia.
- Un borrador sin ítems válidos se permite, pero genera advertencia.

## Herramienta CLI creada

`sistema/tools/check-quote-draft-validator.php`

La herramienta:

- se ejecuta solo por CLI
- carga `QuoteDraftValidator`
- valida un caso correcto
- valida un caso incorrecto
- confirma que el caso correcto sea aceptado
- confirma que el caso incorrecto genere errores

No conecta a base de datos y no guarda información.

## Qué NO se implementó

- No se modificaron páginas públicas.
- No se modificó `QuoteService`.
- No se modificó `QuoteRepository`.
- No se modificó base de datos.
- No se ejecutó SQL.
- No se insertaron datos.
- No se actualizaron datos.
- No se eliminaron datos.
- No se implementó `POST`.
- No se implementó CRUD.
- No se crearon formularios funcionales.
- No se implementó guardar borrador.
- No se implementó emisión.
- No se implementó PDF.
- No se implementó correo.
- No se crearon controllers.
- No se crearon services nuevos.
- No se modificaron login, logout, AuthGuard, SessionManager ni timeout.

## Próxima etapa recomendada

La siguiente etapa recomendada es integrar este validador en una futura acción controlada de guardado de borrador, manteniendo primero una herramienta o endpoint protegido sin exponer formularios funcionales al usuario final.
