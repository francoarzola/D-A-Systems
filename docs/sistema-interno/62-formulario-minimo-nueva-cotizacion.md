# 62. Formulario mínimo de nueva cotización

## Objetivo

Integrar en `cotizaciones.php` un formulario mínimo real para crear borradores desde navegador, reutilizando el endpoint protegido `cotizaciones-guardar.php`.

Esta etapa conecta solo una captura básica. La maqueta visual avanzada se mantiene como referencia y no se convierte completa en formulario funcional.

## Archivo modificado

- `sistema/public/cotizaciones.php`

## Qué se modificó en cotizaciones.php

Se agregaron:

- carga de `CsrfToken`
- carga de `FlashMessage`
- lectura y consumo de mensajes flash
- sección real `Crear borrador de cotización`
- formulario `method="post"` con `action="cotizaciones-guardar.php"`
- token CSRF con `CsrfToken::inputField('quote_draft')`
- hidden `form_action=guardar_borrador`

El listado real de cotizaciones se mantiene.

## Mensajes flash

Si existe un mensaje flash, `cotizaciones.php` lo consume con `pull()` y lo muestra arriba del módulo.

La salida del tipo y mensaje se escapa con `ViewFormatter::e()` para evitar HTML no confiable desde sesión.

## Campos mínimos del formulario

Cabecera:

- `nombre_cliente`
- `rut_cliente`
- `nombre_contacto`
- `correo_contacto`
- `telefono_contacto`
- `descripcion`
- `fecha_cotizacion`
- `valido_hasta`
- `condiciones_comerciales`
- `observaciones`

Detalle único:

- `detalles[0][descripcion]`
- `detalles[0][cantidad]`
- `detalles[0][unidad]`
- `detalles[0][precio_unitario_neto]`
- `detalles[0][descuento_monto]`

## Uso de CSRF

El formulario incluye:

```php
CsrfToken::inputField('quote_draft')
```

El endpoint `cotizaciones-guardar.php` valida el mismo token antes de llamar a `QuoteService::createDraft()`.

## Ruta POST usada

```text
cotizaciones-guardar.php
```

## Datos que NO se envían desde navegador

El formulario no envía:

- `numero_cotizacion`
- `estado`
- `subtotal_neto`
- descuento de cabecera
- `iva_porcentaje`
- `iva_monto`
- `total`

Los totales se calculan en backend mediante `QuoteService` y `QuoteTotalsCalculator`.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-minimal-form-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-minimal-form-contract.php
```

La herramienta verifica de forma estática que `cotizaciones.php` contenga el formulario mínimo esperado y que no incluya campos calculados como inputs.

## Prueba manual recomendada

1. Iniciar sesión en el sistema interno.
2. Abrir `cotizaciones.php`.
3. Completar el formulario mínimo.
4. Guardar borrador.
5. Verificar redirección al detalle.
6. Verificar que el borrador aparece en el listado.

## Qué NO se implementó

- No se implementó edición.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se creó AJAX.
- No se creó API JSON.
- No se crearon controllers.
- No se implementaron múltiples líneas dinámicas.
- No se calcularon totales en frontend.

## Próxima etapa recomendada

La próxima etapa recomendada es mejorar la experiencia del formulario real, manteniendo el backend como fuente única de validación y cálculo.
