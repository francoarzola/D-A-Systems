# 81 — Endpoint de descarga PDF de cotización

## Objetivo de la etapa

La etapa 7A.37 activa `sistema/public/cotizacion-pdf.php` como endpoint autenticado para descargar el PDF real de una cotización emitida.

La descarga queda disponible por URL directa y autenticada, pero todavía no se agrega botón público en las vistas. La exposición visual se reserva para una etapa posterior.

## Qué cambió en cotizacion-pdf.php

El endpoint deja de responder HTTP 501 y ahora:

- acepta solo solicitudes `GET`;
- exige sesión y autenticación con `AuthGuard::requireAuth('login.php')`;
- valida el parámetro `id`;
- carga la cotización con `QuoteService::getQuoteDetail($id)`;
- valida precondiciones con `QuotePdfService`;
- construye HTML con `QuotePdfHtmlBuilder`;
- renderiza PDF en memoria con `QuotePdfRenderService`;
- responde con headers de descarga PDF;
- imprime el contenido PDF con `echo`;
- termina con `exit`.

En caso de error, muestra una página HTML controlada, sin stack trace ni detalles técnicos.

## Flujo de generación PDF

1. Usuario autenticado solicita `cotizacion-pdf.php?id={id}`.
2. El endpoint valida método e id.
3. Se carga la cotización y sus detalles desde la base de datos.
4. `QuotePdfService::assertCanGeneratePdf()` confirma que esté emitida y tenga número oficial.
5. `CompanyProfile` entrega los datos comerciales centralizados.
6. `QuotePdfHtmlBuilder` construye HTML autónomo para Dompdf.
7. `QuotePdfRenderService` genera el PDF como string en memoria.
8. `QuotePdfService::buildPdfFilename()` construye el nombre seguro del archivo.
9. El endpoint responde `Content-Type: application/pdf` y `Content-Disposition: attachment`.

## Autenticación requerida

El endpoint usa `SessionManager` y `AuthGuard`. No se permite descargar PDF sin sesión válida.

## Validaciones de id

El parámetro `id` se lee desde `$_GET` y debe ser entero positivo.

- Método distinto de GET: HTTP 405.
- Id inválido: HTTP 400.
- Cotización no encontrada: HTTP 404.

## Validaciones de estado y número

Solo se permite PDF si:

- la cotización existe;
- `estado` es `emitida`;
- `numero_cotizacion` no está vacío.

Si no cumple, el endpoint responde con mensaje controlado y no genera PDF.

## Uso de QuotePdfService

`QuotePdfService` mantiene la regla de negocio para validar si una cotización puede generar PDF y para construir el nombre del archivo.

## Uso de QuotePdfHtmlBuilder

`QuotePdfHtmlBuilder` transforma los datos reales de cotización, detalles y empresa en HTML autónomo compatible con Dompdf.

## Uso de QuotePdfRenderService

`QuotePdfRenderService` renderiza el HTML a PDF en memoria. No guarda archivos ni usa `stream()`.

## Uso de CompanyProfile

`CompanyProfile` entrega los datos comerciales de D&A Systems desde la configuración centralizada.

## Por qué no se guarda archivo

Esta etapa genera el PDF en memoria y lo entrega en la respuesta HTTP. No se crea carpeta de PDFs ni se escribe en disco para evitar problemas de permisos, limpieza, exposición accidental y retención de archivos.

## Por qué todavía no se agrega botón público

La descarga queda técnicamente activa, pero el botón visible se implementará después. Esto permite validar el endpoint por URL directa antes de incorporarlo al flujo de usuario.

## Qué NO se implementó

- No se agregó botón `Descargar PDF`.
- No se implementó correo.
- No se implementó almacenamiento de PDFs.
- No se implementó AJAX ni API JSON.
- No se modificó base de datos.
- No se cambió emisión.
- No se modificaron vistas públicas.

## Herramienta CLI creada

Se creó `sistema/tools/check-quote-pdf-download-endpoint-contract.php`.

La herramienta verifica que el endpoint contenga las piezas necesarias para la descarga autenticada y que no use operaciones fuera de alcance como escritura de archivos, `readfile`, `stream()`, correo, AJAX o API JSON.

También verifica que las vistas no contengan botón `Descargar PDF` ni enlaces a `cotizacion-pdf.php`, y que no existan carpetas públicas de PDFs.

## Cómo ejecutar

```bash
php sistema/tools/check-quote-pdf-download-endpoint-contract.php
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-pdf-download-endpoint-contract.php
```

## Prueba manual recomendada

1. Iniciar sesión en el sistema interno.
2. Abrir `http://127.0.0.1:8080/cotizacion-pdf.php?id=3`.
3. Verificar que descarga `COT-2026-0001.pdf`.
4. Abrir el PDF y revisar número, cliente, detalle y total.

## Próxima etapa recomendada

7A.38 — Agregar botón Descargar PDF solo para cotizaciones emitidas.
