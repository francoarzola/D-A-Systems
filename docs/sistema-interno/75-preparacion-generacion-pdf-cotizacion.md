# 75 - Preparación de generación PDF de cotización

## Objetivo

Preparar una pieza interna para una futura generación PDF de cotizaciones emitidas, sin generar archivos reales, sin crear endpoint público y sin agregar botones de descarga.

## Por qué aún no se genera PDF real

La vista HTML imprimible ya funciona como documento A4 y sirve como referencia visual. Antes de incorporar una librería o un endpoint de descarga, esta etapa separa las precondiciones y reglas mínimas para que el futuro PDF solo pueda prepararse desde cotizaciones válidas.

## Qué prepara `QuotePdfService`

Se creó `sistema/app/Services/QuotePdfService.php` como servicio puro, sin sesión, sin base de datos, sin PDO y sin dependencias externas.

Trabaja con arrays de cotización ya cargados por capas existentes y expone:

- `canGeneratePdf(array $quote): bool`
- `assertCanGeneratePdf(array $quote): void`
- `buildPdfFilename(array $quote): string`

## Precondiciones para futuro PDF

Una cotización solo queda habilitada conceptualmente para PDF si:

- existe en la capa que la cargue
- tiene `estado` igual a `emitida`
- tiene `numero_cotizacion` informado

Los borradores y cotizaciones emitidas sin número oficial no pasan la validación.

## Nombre de archivo seguro

`buildPdfFilename()` valida primero las precondiciones y luego construye un nombre basado en `numero_cotizacion`.

Ejemplo:

```text
COT-2026-0001.pdf
```

El nombre se sanitiza para evitar caracteres inseguros y conservar solo letras, números, punto, guion bajo y guion medio.

## Qué NO se implementó

No se creó endpoint público, no se creó `cotizacion-pdf.php`, no se agregó botón `Descargar PDF`, no se generaron archivos `.pdf`, no se creó carpeta de PDFs, no se modificó base de datos, no se ejecutó SQL, no se modificó emisión, no se envió correo, no se agregó AJAX ni API JSON.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-pdf-preparation-contract.php
```

Comando Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-pdf-preparation-contract.php
```

La herramienta verifica:

- existencia del servicio
- métodos requeridos
- precondiciones de cotización emitida con número oficial
- nombre `COT-2026-0001.pdf`
- ausencia de endpoint público
- ausencia de texto `Descargar PDF` en vistas públicas
- ausencia de operaciones de base de datos, escritura de archivos, correo, AJAX o API JSON en el servicio

## Próxima etapa recomendada

Implementar generación PDF controlada mediante endpoint autenticado, reutilizando estas precondiciones y el HTML imprimible ya validado, recién cuando se decida la librería o estrategia de renderizado.
