# Etapa 7B.05 — Orden visual del detalle de cotización

## Objetivo

Mejorar el orden visual de `cotizacion-detalle.php` para presentar primero un resumen ejecutivo y las acciones principales, dejando el detalle completo y bloques secundarios más abajo.

## Problema detectado

El detalle funcionaba correctamente pero las acciones principales y el resumen estaban ubicados lejos del inicio de la página, obligando a usuarios a hacer scroll para acciones frecuentes.

## Qué se ordenó en `cotizacion-detalle.php`

- Se añadió un encabezado operativo `Detalle de cotización` con texto contextual.
- Se creó un resumen superior con los datos clave: número, estado, cliente, fecha, validez y total.
- Se movieron las acciones principales (Volver, Editar, Emitir, Vista imprimible, Descargar PDF) cerca del resumen.
- Los bloques secundarios (`Datos generales`, `Cliente`, `Resumen`) y `Descripción y condiciones`, `Registro` y la tabla de `Detalles` quedaron debajo del resumen.
- Se mantuvieron todas las reglas de visibilidad y los formularios/CSRF existentes.

## Jerarquía visual resultante

1. Topbar interna (existente)
2. Encabezado operativo: `Detalle de cotización`
3. Resumen ejecutivo (número, estado, cliente, fecha, validez, total)
4. Acciones principales (Volver, Editar, Emitir, Vista imprimible, Descargar PDF)
5. Bloques de información (Datos generales, Cliente, Resumen)
6. Descripción y condiciones
7. Tabla de líneas (Detalles)
8. Totales y secciones finales

## Reglas de acciones por estado

- Borrador:
  - Mostrar: `Editar borrador`, `Emitir cotización`, `Volver al listado`.
  - No mostrar: `Vista imprimible`, `Descargar PDF`.
- Emitida con número oficial (campo `numero_cotizacion` no vacío):
  - Mostrar: `Vista imprimible`, `Descargar PDF`, `Volver al listado`.
  - No mostrar: `Editar`, `Emitir`.
- Emitida sin número oficial:
  - Comportamiento intacto según reglas actuales (sin descargar/print si no tiene número).

## Qué se mantuvo funcionalmente intacto

- Lectura real de la cotización desde los servicios/repositorios.
- Todas las URLs actuales y actions:
  - `cotizacion-editar.php?id=`
  - `cotizacion-emitir.php` (form POST con CSRF)
  - `cotizacion-imprimir.php?id=`
  - `cotizacion-pdf.php?id=`
  - `cotizaciones.php`
- CSRF y tokens del formulario de emisión.
- Escape de salida con `ViewFormatter::e()` y utilidades de `ViewFormatter`.
- Tabla de detalles y campos calculados.

## Cambios en `internal.css`

- Añadidas clases para soportar la nueva jerarquía visual:
  - `quote-detail-heading`
  - `quote-detail-intro`
  - `quote-detail-summary`
  - `quote-detail-summary-item`
  - `quote-detail-actions`
  - `quote-inline-form`

Esos estilos son modestos, coherentes con la estética existente y responden en pantallas pequeñas.

## Decisiones visuales

- Mantener una apariencia sobria y corporativa acorde al resto de la intranet.
- El resumen superior se diseñó como tarjetas pequeñas para facilitar lectura rápida.
- Las acciones permanecen como botones tipo "píldora" para coherencia con el resto del UI.

## Riesgos controlados

- No se tocaron servicios, repositorios ni lógica de emisión; por tanto no hay riesgo de alterar datos.
- No se añadieron scripts ni dependencias externas.

## Qué NO se implementó

- No se realizó rediseño completo del módulo.
- No se cambiaron flujos de emisión, PDF o impresión.
- No se añadieron llamadas a APIs ni AJAX.

## Herramienta CLI de verificación

- Se agregó `sistema/tools/check-quote-detail-visual-order-contract.php` que valida la presencia de elementos clave en `cotizacion-detalle.php` y `internal.css` y asegura que no se hayan introducido llamadas NO permitidas (fetch, XMLHttpRequest, file_put_contents, etc.).

### Cómo ejecutar

Desde CLI:

```
php sistema/tools/check-quote-detail-visual-order-contract.php
```

Con Laragon (ejemplo):

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-detail-visual-order-contract.php
```

## Pruebas recomendadas (manuales)

1. Iniciar sesión en la intranet.
2. Abrir `cotizacion-detalle.php?id=<id de ejemplo>` para una cotización emitida con número y verificar:
   - Resumen superior con número, estado, cliente, fecha, validez y total.
   - Acciones visibles: `Vista imprimible`, `Descargar PDF`, `Volver al listado`.
3. Abrir una cotización en estado `borrador` y verificar:
   - Acciones visibles: `Editar borrador`, `Emitir cotización`, `Volver al listado`.
   - No aparece `Descargar PDF`.
4. Revisar que la tabla de detalles muestra las columnas: `cantidad`, `precio_unitario_neto`, `total_linea_neto`.
5. Ejecutar sintaxis PHP:

```
php -l sistema/public/cotizacion-detalle.php
php -l sistema/tools/check-quote-detail-visual-order-contract.php
```

## Próxima etapa recomendada

- 7B.06 — Orden visual del formulario de edición de cotización.

*** Fin de la documentación de la etapa 7B.05
