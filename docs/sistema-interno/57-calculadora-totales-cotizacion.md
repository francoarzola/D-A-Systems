# 57. Calculadora de totales de cotización

## Objetivo

Crear una calculadora reutilizable para centralizar el cálculo de montos de cotizaciones antes de implementar guardado real, POST, CRUD o lógica de persistencia.

La etapa prepara una pieza de servicio que podrá ser usada más adelante por el flujo de borradores y emisión, manteniendo los cálculos fuera de las páginas públicas.

## Archivo creado

- `sistema/app/Services/QuoteTotalsCalculator.php`
- `sistema/tools/check-quote-totals-calculator.php`

## Método principal

La clase `QuoteTotalsCalculator` expone:

```php
calculate(array $details, float $headerDiscount = 0.00, float $taxRate = 19.00): array
```

Recibe una lista de detalles con claves en español:

- `descripcion`
- `cantidad`
- `unidad`
- `precio_unitario_neto`
- `descuento_monto`

También recibe opcionalmente:

- descuento de cabecera
- porcentaje de IVA

## Estructura devuelta

La respuesta contiene:

- `details`
- `subtotal_neto`
- `descuento_monto`
- `iva_porcentaje`
- `iva_monto`
- `total`

Cada detalle calculado incluye:

- `numero_linea`
- `descripcion`
- `cantidad`
- `unidad`
- `precio_unitario_neto`
- `descuento_monto`
- `subtotal_linea_neto`
- `total_linea_neto`

## Reglas de cálculo

- Las líneas vacías se ignoran.
- El número de línea se normaliza de forma secuencial.
- El subtotal de línea se calcula como `cantidad * precio_unitario_neto`.
- El total de línea se calcula como `subtotal_linea_neto - descuento_monto`.
- El subtotal neto de cabecera corresponde a la suma de los totales de línea.
- El descuento de cabecera se resta del subtotal neto.
- El IVA se calcula sobre la base neta después del descuento de cabecera.
- Si el descuento de cabecera es mayor que el subtotal neto, la base imponible para IVA y total se limita a mínimo `0.00`.
- El total se calcula como base neta más IVA.
- Los montos se redondean a 2 decimales.
- Las cantidades se redondean a 4 decimales.

## Relación con el validador

La calculadora no reemplaza a `QuoteDraftValidator`.

El validador sigue siendo responsable de rechazar datos incompletos o inválidos. La calculadora solo normaliza y calcula montos a partir de los datos recibidos.

El descuento de cabecera puede ser mayor al subtotal desde el punto de vista de cálculo. La calculadora no valida si esa regla comercial debe permitirse; solo evita generar IVA o total negativos. La decisión de permitir o rechazar ese descuento corresponde a `QuoteDraftValidator` o a futuras reglas de negocio.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-totals-calculator.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-totals-calculator.php
```

La herramienta prueba:

- cotización de prueba con dos detalles
- descuento de cabecera
- descuento de cabecera mayor que el subtotal neto
- líneas vacías ignoradas
- caso decimal sensible con `0.1 * 0.7 - 0.07`

## Qué NO se implementó

- No se implementó POST.
- No se implementó formulario funcional.
- No se implementó guardado de borradores.
- No se implementó emisión.
- No se insertaron datos.
- No se modificó la base de datos.
- No se ejecutó SQL.
- No se implementó CRUD.
- No se modificaron páginas públicas.

## Próxima etapa recomendada

La próxima etapa recomendada es integrar esta calculadora en el futuro flujo de guardado de borradores, junto con `QuoteDraftValidator`, antes de persistir datos en la base.
