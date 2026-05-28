# 79 — Prototipo de render PDF en memoria

## Objetivo de la etapa

La etapa 7A.35 crea un prototipo interno para validar que Dompdf puede convertir HTML controlado a PDF como string en memoria.

El objetivo es comprobar la viabilidad técnica de la generación PDF sin activar todavía descarga pública, sin modificar endpoints, sin guardar archivos y sin usar base de datos.

## Por qué se genera PDF solo en memoria

El flujo de cotizaciones ya cuenta con vista imprimible HTML A4 y con un endpoint PDF simulado. Antes de convertir ese endpoint en descarga real, esta etapa valida el motor Dompdf en un contexto aislado y reversible.

Generar el PDF solo en memoria permite verificar:

- que `vendor/autoload.php` carga correctamente Dompdf;
- que `Dompdf\Dompdf` puede renderizar HTML con UTF-8;
- que el resultado comienza con `%PDF`;
- que no se escribe ningún archivo en el servidor;
- que no se envía ningún header público de descarga.

## Servicio creado

Se creó `sistema/app/Services/QuotePdfRenderService.php`.

El servicio expone:

```php
renderHtmlToPdf(string $html): string
```

El método recibe HTML, valida que no esté vacío, configura Dompdf y retorna el PDF como string generado en memoria mediante `output()`.

Configuración aplicada:

- tamaño de papel A4;
- orientación vertical;
- `isRemoteEnabled` desactivado;
- `isPhpEnabled` desactivado;
- fuente base `DejaVu Sans` para mejorar compatibilidad con acentos.

El servicio no depende de sesión, base de datos, `PDO`, `$_GET` ni `$_POST`.

## Herramienta CLI creada

Se creó `sistema/tools/check-quote-pdf-render-memory-contract.php`.

La herramienta:

- se ejecuta solo por CLI;
- carga `vendor/autoload.php`;
- carga `QuotePdfRenderService`;
- verifica que `Dompdf\Dompdf` esté disponible;
- renderiza un HTML mínimo de cotización de prueba;
- valida que el resultado sea string, comience con `%PDF` y supere 1000 bytes;
- verifica que no se haya activado PDF real en `cotizacion-pdf.php`;
- verifica que no se haya agregado botón `Descargar PDF` ni enlaces a `cotizacion-pdf.php` en las vistas.

## Qué valida la prueba

El HTML de prueba incluye:

- UTF-8;
- título `Cotización de prueba`;
- número `COT-2026-0001`;
- cliente de prueba;
- tabla simple;
- total `$11.900`.

El resultado esperado es un PDF válido en memoria. La herramienta no guarda el contenido generado.

## Por qué no se expone todavía en cotizacion-pdf.php

`cotizacion-pdf.php` sigue siendo un endpoint simulado que responde HTTP 501. Esta etapa no cambia su comportamiento porque todavía no se ha integrado el render con datos reales de cotización ni se ha definido la respuesta final de descarga.

La activación pública del PDF debe quedar para una etapa posterior, una vez validado el render con datos reales y con controles de autenticación, estado y número oficial.

## Por qué no se guarda archivo

No se guarda archivo porque la primera implementación segura debe probar generación en memoria. Guardar PDFs en disco introduce decisiones adicionales:

- ruta de almacenamiento;
- permisos de escritura;
- limpieza de archivos;
- exposición accidental;
- política de retención.

Esas decisiones quedan fuera de esta etapa.

## Consideraciones de seguridad

- No se permiten recursos remotos en Dompdf.
- No se permite PHP embebido en el HTML.
- No se envían headers públicos de PDF.
- No se usa base de datos.
- No se escribe en disco.
- No se modifica ningún endpoint público.
- No se agrega botón de descarga.

## Qué NO se implementó

- No se implementó descarga PDF.
- No se activó endpoint PDF real.
- No se agregó botón `Descargar PDF`.
- No se implementó correo.
- No se implementó almacenamiento de PDFs.
- No se modificó base de datos.
- No se tocó emisión.
- No se implementó AJAX ni API JSON.

## Cómo ejecutar

```bash
php sistema/tools/check-quote-pdf-render-memory-contract.php
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-pdf-render-memory-contract.php
```

## Si falta vendor/autoload.php

Ejecutar:

```bash
composer install
```

`vendor/` no se versiona en Git. Debe generarse localmente desde `composer.json` y `composer.lock`.

## Próxima etapa recomendada

7A.36 — Render PDF real con datos de cotización, todavía vía CLI o servicio interno, sin activar descarga pública.
