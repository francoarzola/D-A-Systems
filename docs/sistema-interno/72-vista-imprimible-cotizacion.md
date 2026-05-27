# 72. Vista imprimible de cotización

## Objetivo

Crear una vista HTML imprimible para cotizaciones emitidas, sin generar PDF y sin enviar correo.

## Vista creada

Se creó:

```text
sistema/public/cotizacion-imprimir.php
```

La vista es de solo lectura y se abre por GET:

```text
cotizacion-imprimir.php?id={id}
```

## Solo lectura

La página no procesa `POST`, no modifica base de datos y no cambia estados. Solo carga datos existentes mediante `QuoteService::getQuoteDetail()`.

## Restricción a cotizaciones emitidas

La vista imprimible solo se muestra si:

- la cotización existe
- `estado = emitida`
- `numero_cotizacion` no está vacío

Si la cotización no existe, el ID es inválido o la cotización no está emitida con número oficial, se muestra un mensaje controlado.

## Datos mostrados

La vista muestra:

- encabezado D&A Systems
- título “Cotización”
- número oficial
- estado
- fecha de cotización
- validez
- datos del cliente
- descripción
- tabla de detalles
- subtotal neto
- descuento
- IVA porcentaje
- IVA monto
- total
- condiciones comerciales
- observaciones

Toda salida dinámica se escapa con `ViewFormatter::e()`.

## Protección con autenticación

`cotizacion-imprimir.php` inicia sesión con `SessionManager` y exige autenticación mediante:

```php
AuthGuard::requireAuth('login.php')
```

## Enlace desde detalle

En `cotizacion-detalle.php` se agregó el enlace:

```text
Vista imprimible
```

Solo aparece cuando:

- `estado === 'emitida'`
- `numero_cotizacion` no está vacío

No aparece para borradores.

## Estilos de impresión

Se agregaron estilos en:

```text
sistema/public/assets/css/internal.css
```

Incluyen clases para el documento imprimible y una regla `@media print` para ocultar acciones como “Imprimir” y “Volver al detalle” al imprimir.

## Qué NO se implementó

- No se generó PDF.
- No se envió correo.
- No se implementó envío al cliente.
- No se implementó anulación.
- No se implementó aceptación o rechazo.
- No se implementó AJAX.
- No se implementó API JSON.
- No se cambió ningún estado.
- No se modificaron números de cotización.
- No se modificó base de datos.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-print-view-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-print-view-contract.php
```

La herramienta valida el contrato de la vista imprimible sin usar base de datos, sin ejecutar POST y sin modificar archivos.

## Prueba manual recomendada

1. Abrir una cotización emitida.
2. Presionar `Vista imprimible`.
3. Verificar número oficial, cliente, detalles y totales.
4. Presionar `Imprimir`.
5. Confirmar que acciones y navegación no aparecen en impresión.

## Próxima etapa recomendada

La próxima etapa recomendada es preparar generación PDF controlada o envío por correo, manteniendo esta vista HTML como base visual de revisión.
