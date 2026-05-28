# 82 — Botón de descarga PDF de cotización

## Objetivo de la etapa

La etapa 7A.38 agrega el enlace visible `Descargar PDF` en la vista de detalle de cotización, usando el endpoint autenticado ya creado en `cotizacion-pdf.php`.

El botón se muestra únicamente para cotizaciones emitidas con número oficial.

## Dónde se agregó el botón

Se modificó `sistema/public/cotizacion-detalle.php`, en el bloque de acciones de la cotización.

El enlace apunta a:

```text
cotizacion-pdf.php?id={id}
```

## Condiciones de visibilidad

El botón se muestra solo si:

- `estado === 'emitida'`;
- `numero_cotizacion` no está vacío.

No se muestra en borradores ni en cotizaciones sin número oficial.

## Flujo de descarga

1. El usuario abre el detalle de una cotización emitida.
2. La vista muestra `Descargar PDF`.
3. El enlace hace una solicitud `GET` a `cotizacion-pdf.php?id={id}`.
4. El endpoint valida autenticación, estado y número oficial.
5. El endpoint genera el PDF en memoria y responde la descarga.

## Por qué se usa GET

La descarga de un archivo es una operación de lectura. No cambia estado, no modifica base de datos y no crea registros. Por eso se usa un enlace `GET`.

## Por qué no se agrega CSRF al enlace

CSRF se reserva para acciones que cambian estado o datos. Esta descarga no modifica información y el endpoint ya exige autenticación y valida que la cotización esté emitida con número oficial.

## Por qué no se muestra en borradores

Los borradores no tienen número oficial y todavía pueden editarse. El PDF comercial descargable solo corresponde a cotizaciones emitidas.

## Qué NO se implementó

- No se implementó correo.
- No se implementó almacenamiento de PDFs.
- No se implementó AJAX ni API JSON.
- No se modificó base de datos.
- No se cambió emisión.
- No se modificó `cotizacion-pdf.php`.
- No se modificó `cotizacion-imprimir.php`.
- No se modificó `cotizaciones.php`.

## Herramienta CLI creada

Se creó `sistema/tools/check-quote-pdf-download-button-contract.php`.

La herramienta verifica que:

- `cotizacion-detalle.php` contiene el botón `Descargar PDF`;
- el enlace apunta a `cotizacion-pdf.php?id=`;
- la visibilidad está condicionada por estado `emitida` y `numero_cotizacion` no vacío;
- `cotizacion-pdf.php` sigue siendo el endpoint real de descarga;
- `cotizacion-imprimir.php` y `cotizaciones.php` no incorporan el botón;
- no existen carpetas públicas de PDFs.

## Cómo ejecutar

```bash
php sistema/tools/check-quote-pdf-download-button-contract.php
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-pdf-download-button-contract.php
```

## Prueba manual recomendada

1. Iniciar sesión.
2. Abrir `cotizacion-detalle.php?id=3`.
3. Verificar que aparece `Descargar PDF`.
4. Hacer clic y verificar descarga `COT-2026-0001.pdf`.
5. Abrir un borrador y verificar que no aparece `Descargar PDF`.

## Próxima etapa recomendada

7A.39 — Revisión visual y de flujo final de acciones de cotización.
