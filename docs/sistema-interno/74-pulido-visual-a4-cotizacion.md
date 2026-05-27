# 74 - Pulido visual A4 de vista imprimible de cotización

## Objetivo

Pulir la vista imprimible HTML de cotizaciones emitidas para que se vea como un documento comercial A4 más profesional, manteniendo el comportamiento existente de solo lectura.

## Qué se mejoró visualmente

Se ajustó la vista `sistema/public/cotizacion-imprimir.php` para mejorar la jerarquía y lectura del documento:

- encabezado más compacto con marca, título, número oficial, estado y fecha
- bloque de datos de empresa más ordenado
- bloque de cliente más claro
- títulos de sección consistentes
- tabla de detalles más legible
- montos con alineación y numeración tabular
- resumen de totales más destacado
- total final visualmente más importante
- pie de documento discreto

## Archivos modificados

- `sistema/public/cotizacion-imprimir.php`
- `sistema/public/assets/css/internal.css`

## Comportamiento solo lectura

La vista sigue cargando una cotización existente mediante `QuoteService::getQuoteDetail()` y mantiene la restricción de impresión solo para cotizaciones emitidas con `numero_cotizacion`.

No se agregaron formularios, POST, cambios de estado ni acciones de escritura.

## Autenticación

La vista sigue protegida por `AuthGuard::requireAuth('login.php')`. No se modificó el flujo de sesión ni login.

## CompanyProfile

Los datos comerciales siguen viniendo desde `CompanyProfile`, que centraliza la configuración creada en la etapa anterior. La vista no duplica datos de empresa ni inventa datos tributarios reales.

Toda salida dinámica se mantiene escapada con `ViewFormatter::e()`.

## Comportamiento en impresión

Se agregaron reglas CSS para impresión:

- `@page` con tamaño A4 y margen prudente
- fondo blanco en impresión
- documento sin sombra, borde ni margen de pantalla
- acciones ocultas con `.print-actions { display: none; }`
- bloques y filas de tabla protegidos contra cortes innecesarios cuando sea razonable
- tabla legible en blanco y negro

En pantalla, el documento se muestra centrado con apariencia de hoja A4 sobre fondo gris suave.

## Qué NO se implementó

No se modificó base de datos, no se ejecutó SQL, no se cambió emisión, no se generó PDF, no se envió correo, no se creó AJAX, no se creó API JSON, no se implementó anulación, aceptación ni rechazo.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-print-a4-layout-contract.php
```

Comando Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-print-a4-layout-contract.php
```

La herramienta verifica que la vista conserve autenticación, `CompanyProfile`, escape con `ViewFormatter::e()`, lectura con `getQuoteDetail()`, restricción de cotización emitida y botón de impresión. También valida que el CSS tenga reglas A4 y oculte acciones al imprimir.

## Prueba manual recomendada

1. Abrir `http://127.0.0.1:8080/cotizacion-imprimir.php?id=3`.
2. Revisar la vista en pantalla.
3. Presionar `Imprimir`.
4. Revisar la vista previa de impresión.
5. Confirmar que los botones no aparecen en impresión.
6. Confirmar que número, cliente, detalles y totales siguen visibles.

## Próxima etapa recomendada

Usar esta base HTML A4 como referencia para una futura generación PDF o para preparar envío controlado por correo, manteniendo la misma fuente de datos y formato comercial.
