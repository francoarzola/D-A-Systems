# 80 — Render PDF con datos reales vía CLI

## Objetivo de la etapa

La etapa 7A.36 valida que una cotización real emitida pueda convertirse a HTML controlado y luego a PDF en memoria mediante Dompdf.

La prueba sigue siendo interna y solo por CLI. No activa descarga pública, no modifica `cotizacion-pdf.php`, no agrega botones y no guarda archivos PDF.

## Por qué aún se ejecuta solo por CLI

El endpoint público de PDF continúa en modo simulado. Antes de habilitar descarga desde navegador, esta etapa comprueba el flujo completo con datos reales:

1. cargar cotización desde base de datos;
2. validar que esté emitida y tenga número oficial;
3. cargar datos comerciales;
4. construir HTML compatible con Dompdf;
5. renderizar PDF como string en memoria;
6. descartar el resultado sin guardarlo.

## Servicio QuotePdfHtmlBuilder

Se creó `sistema/app/Services/QuotePdfHtmlBuilder.php`.

El builder recibe:

```php
build(array $quote, array $companyProfile): string
```

El array de cotización debe incluir la cabecera real y, para esta etapa, los detalles asociados bajo la clave `details`.

El HTML generado incluye:

- UTF-8;
- nombre comercial de empresa;
- razón social;
- RUT;
- giro;
- correo;
- teléfono;
- número oficial de cotización;
- estado;
- fecha;
- validez;
- datos del cliente;
- descripción;
- tabla de detalles;
- subtotal;
- descuento;
- IVA;
- total;
- condiciones comerciales;
- observaciones;
- nota de validez;
- nota de pie.

Todo dato dinámico se escapa con `htmlspecialchars`. El HTML usa CSS embebido simple, sin recursos remotos ni CSS externo.

## Relación con QuotePdfService

`QuotePdfService` sigue siendo responsable de validar la precondición de negocio:

- la cotización existe;
- el estado es `emitida`;
- `numero_cotizacion` no está vacío.

Si una cotización no cumple esas condiciones, la herramienta CLI detiene el flujo con un mensaje controlado.

## Relación con QuotePdfRenderService

`QuotePdfRenderService` recibe el HTML generado y usa Dompdf para renderizar el PDF en memoria.

Esta etapa reutiliza el servicio creado en 7A.35 y mantiene:

- tamaño A4;
- orientación vertical;
- recursos remotos desactivados;
- PHP embebido desactivado;
- salida como string mediante `output()`.

## Uso de CompanyProfile

La herramienta carga datos comerciales con:

```php
CompanyProfile::fromDefaultConfig()->all()
```

Así el PDF usa la misma fuente centralizada definida para cotizaciones, vista imprimible y futuras etapas de descarga.

## Cómo se cargan datos reales

La herramienta CLI usa:

- `DatabaseConfig::fromDefaultPath()->load()`;
- `Connection`;
- `QuoteRepository`;
- `QuoteService::getQuoteDetail($id)`.

El id puede pasarse como argumento. Si no se entrega, se usa `3` como valor local de prueba.

## Validaciones de seguridad

- Solo se ejecuta por CLI.
- No imprime el PDF en consola.
- No guarda archivos.
- No crea carpetas.
- No usa `stream()`.
- No usa `readfile`.
- No envía headers `application/pdf`.
- No modifica base de datos.
- No modifica estados ni números de cotización.
- Verifica que el endpoint público siga sin activar descarga real.
- Verifica que las vistas no tengan botón `Descargar PDF`.

## Qué NO se implementó

- No se implementó endpoint real de PDF.
- No se implementó descarga pública.
- No se agregó botón `Descargar PDF`.
- No se implementó almacenamiento de PDFs.
- No se implementó correo.
- No se implementó AJAX ni API JSON.
- No se modificó base de datos.
- No se tocó emisión.

## Cómo ejecutar

```bash
php sistema/tools/check-quote-pdf-real-data-render-contract.php 3
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-pdf-real-data-render-contract.php 3
```

## Si falta vendor/autoload.php

Ejecutar:

```bash
composer install
```

`vendor/` no se versiona en Git.

## Si la cotización id=3 no existe o no está emitida

Usar otro id de una cotización emitida con número oficial:

```bash
php sistema/tools/check-quote-pdf-real-data-render-contract.php {id}
```

## Próxima etapa recomendada

7A.37 — Activar endpoint autenticado de descarga PDF sin botón público todavía, usando el render ya validado.
