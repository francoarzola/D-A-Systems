# 76 - Endpoint PDF simulado de cotización

## Objetivo

Crear un endpoint público autenticado para preparar la futura descarga PDF de cotizaciones, sin generar PDF real todavía y sin agregar botones de descarga en las vistas existentes.

## Por qué existe sin generar PDF real

El sistema ya tiene vista imprimible A4 y `QuotePdfService` con precondiciones internas. Esta etapa valida el flujo de seguridad y carga de datos desde navegador, pero deja la generación real para una etapa posterior cuando se defina la estrategia o librería de PDF.

## Endpoint creado

Se creó:

```text
sistema/public/cotizacion-pdf.php
```

El endpoint:

- acepta solo `GET`
- inicia sesión con `SessionManager`
- exige autenticación con `AuthGuard::requireAuth('login.php')`
- lee `id` desde `$_GET`
- valida que el `id` sea entero positivo
- carga la cotización con `QuoteService::getQuoteDetail($id)`
- usa `QuotePdfService`
- llama a `assertCanGeneratePdf($quote)`
- prepara el nombre con `buildPdfFilename($quote)`
- muestra una respuesta HTML controlada

## Flujo de validación

1. Validar método `GET`.
2. Validar sesión y autenticación.
3. Validar `id`.
4. Cargar la cotización.
5. Confirmar que existe.
6. Confirmar que está `emitida`.
7. Confirmar que tiene `numero_cotizacion`.
8. Preparar el nombre futuro del archivo.
9. Responder que PDF aún no está disponible.

## Uso de `QuotePdfService`

`cotizacion-pdf.php` usa `QuotePdfService` como capa interna para centralizar las reglas de preparación de PDF:

- `assertCanGeneratePdf()`
- `buildPdfFilename()`

Así el endpoint no duplica reglas de estado ni nombre de archivo.

## Respuesta HTTP 501

Cuando la cotización es válida para PDF, el endpoint responde con `HTTP 501 Not Implemented` y muestra:

- título `PDF no disponible todavía`
- archivo preparado, por ejemplo `COT-2026-0001.pdf`
- mensaje indicando que la generación real será implementada después
- enlace `Volver al detalle`

Esto deja claro que la ruta existe, pero que todavía no entrega un archivo PDF real.

## Qué NO se implementó

No se generó PDF real, no se usó `Content-Type: application/pdf`, no se usó `Content-Disposition`, no se escribieron archivos, no se creó carpeta de PDFs, no se usó Dompdf ni Composer, no se modificó base de datos, no se cambiaron estados ni números de cotización, no se envió correo, no se implementó AJAX ni API JSON, y no se agregó botón `Descargar PDF`.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-pdf-endpoint-simulated-contract.php
```

Comando Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-pdf-endpoint-simulated-contract.php
```

La herramienta verifica que el endpoint tenga autenticación, use `QuoteService`, use `QuotePdfService`, responda con `501` en el caso preparado y no incluya generación real de PDF, correo, AJAX ni API JSON. También confirma que no se haya agregado botón o enlace de PDF en `cotizacion-detalle.php` ni `cotizacion-imprimir.php`.

## Prueba manual recomendada

1. Iniciar sesión en el sistema interno.
2. Abrir `cotizacion-pdf.php?id=3`.
3. Verificar el mensaje `PDF no disponible todavía`.
4. Verificar el nombre preparado `COT-2026-0001.pdf`.
5. Verificar el enlace `Volver al detalle`.

## Próxima etapa recomendada

Decidir estrategia o librería de generación PDF real, manteniendo el endpoint autenticado y reutilizando `QuotePdfService` para las precondiciones.
