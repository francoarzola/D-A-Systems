# 78 - Preparación de dependencia Dompdf

## Objetivo

Preparar la dependencia `dompdf/dompdf` de forma controlada para una futura generación PDF de cotizaciones, sin activar todavía descarga real, sin modificar el endpoint público y sin generar archivos PDF.

## Por qué se eligió Dompdf

La evaluación técnica de la etapa anterior recomendó Dompdf como primera alternativa porque:

- es una dependencia PHP integrable sin binarios externos
- es más viable para hosting compartido/cPanel que `wkhtmltopdf`
- sirve para documentos HTML controlados y relativamente simples
- permite generar PDF en memoria en una etapa posterior

`mPDF` queda como alternativa si Dompdf presenta problemas de tablas, paginación o formato.

## Archivos creados o modificados

Se creó:

- `composer.json`
- `composer.lock`
- `sistema/tools/check-dompdf-dependency-contract.php`
- `docs/sistema-interno/78-preparacion-dependencia-dompdf.md`

`vendor/` no se versiona en Git. Queda excluido mediante `.gitignore` y debe generarse en cada entorno con Composer.

`composer.json` declara:

```json
{
    "require": {
        "php": ">=8.3",
        "dompdf/dompdf": "^3.1"
    }
}
```

## Instalación de dependencia

En un entorno con Composer disponible:

```bash
composer install
```

Este comando instala las dependencias declaradas en `composer.lock` y genera `vendor/autoload.php`.

Si se necesita agregar la dependencia desde cero:

```bash
composer require dompdf/dompdf
```

En este entorno la dependencia se preparó usando Composer de Laragon con PHP 8.3, pero `vendor/` no se mantiene versionado.

## Validación

Ejecutar:

```bash
php sistema/tools/check-dompdf-dependency-contract.php
```

Comando Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-dompdf-dependency-contract.php
```

La herramienta verifica:

- existencia de `composer.json`
- presencia de `dompdf/dompdf`
- ausencia de dependencias innecesarias evidentes como `mpdf/mpdf` o `wkhtmltopdf`
- existencia de `vendor/autoload.php`
- disponibilidad de `Dompdf\Dompdf`
- que `cotizacion-pdf.php` no entregue PDF real todavía
- que no exista botón `Descargar PDF` en las vistas públicas

## Consideraciones para cPanel

En hosting compartido/cPanel:

- si no se puede ejecutar Composer en servidor, se debe subir `vendor/` como parte del despliegue generado localmente
- `vendor/` no se mantiene en Git
- revisar que la versión PHP sea compatible con el proyecto
- revisar límites de memoria y tiempo de ejecución
- probar con cotizaciones largas antes de activar descarga PDF real
- evitar escritura de archivos hasta definir una política de almacenamiento

## Qué NO se implementó

No se generó PDF real, no se modificó `cotizacion-pdf.php`, no se agregó botón `Descargar PDF`, no se modificó base de datos, no se cambió emisión, no se enviaron correos, no se implementó AJAX ni API JSON.

## Próxima etapa recomendada

7A.35 — Prototipo interno de render PDF en memoria sin activar descarga pública.
