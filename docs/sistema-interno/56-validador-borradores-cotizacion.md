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

## Claves esperadas en español

Cabecera:

- `form_action`
- `numero_cotizacion`
- `nombre_cliente`
- `rut_cliente`
- `nombre_contacto`
- `correo_contacto`
- `telefono_contacto`
- `descripcion`
- `fecha_cotizacion`
- `valido_hasta`
- `condiciones_comerciales`
- `observaciones`
- `detalles`

Detalle:

- `descripcion`
- `cantidad`
- `unidad`
- `precio_unitario_neto`
- `descuento_monto`

## Reglas implementadas

- `form_action`, si viene informado, debe ser `guardar_borrador`.
- `numero_cotizacion` no se acepta como dato de borrador; si viene informado, genera advertencia.
- `nombre_cliente` es obligatorio.
- `fecha_cotizacion` es obligatoria y debe tener formato `Y-m-d`.
- `valido_hasta`, si viene informada, debe tener formato `Y-m-d`.
- `valido_hasta` no puede ser anterior a `fecha_cotizacion`.
- `correo_contacto`, si viene informado, debe tener formato válido.
- `detalles` debe ser una lista si viene informado.
- Líneas de detalle completamente vacías se ignoran.
- Detalles parciales generan errores.
- La descripción del detalle es obligatoria cuando la línea tiene datos.
- La cantidad debe ser mayor que cero.
- El precio unitario neto debe ser mayor o igual a cero.
- El descuento debe ser mayor o igual a cero.
- El total de línea calculado no puede ser negativo.
- La unidad faltante genera advertencia.
- Un borrador sin detalles válidos se permite, pero genera advertencia.

## Regla de redondeo decimal

Para evitar falsos errores por precisión binaria de floats, el validador redondea a 2 decimales antes de comparar el total de línea:

```php
$lineSubtotal = round($quantity * $unitPrice, 2);
$lineTotal = round($lineSubtotal - $discount, 2);
```

Luego valida que `$lineTotal` no sea negativo.

Esto permite casos como:

- `cantidad = 0.1`
- `precio_unitario_neto = 0.7`
- `descuento_monto = 0.07`

El total esperado es `0.00` y no debe generar error.

## Herramienta CLI creada

`sistema/tools/check-quote-draft-validator.php`

La herramienta:

- se ejecuta solo por CLI
- carga `QuoteDraftValidator`
- valida casos correctos e incorrectos
- no conecta a base de datos
- no guarda información

## Casos probados por CLI

La herramienta valida al menos:

1. Caso válido mínimo.
2. Caso válido con detalles.
3. Caso inválido sin nombre de cliente.
4. Caso inválido con correo incorrecto.
5. Caso inválido con descuento mayor al subtotal de línea.
6. Caso decimal sensible donde `0.1 * 0.7 - 0.07` debe quedar válido después de redondear.

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
