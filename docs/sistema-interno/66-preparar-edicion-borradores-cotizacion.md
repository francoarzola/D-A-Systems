# 66. Preparar edición de borradores de cotización

## Objetivo

Preparar la edición de borradores existentes mostrando datos reales de una cotización en estado `borrador` dentro de un formulario, sin implementar todavía guardado, actualización ni endpoint POST de edición.

## Página creada

Se creó:

```text
sistema/public/cotizacion-editar.php
```

La página usa el layout interno protegido y carga la cotización mediante `QuoteService` y `QuoteRepository`.

## Flujo de carga por GET

La página recibe:

```text
cotizacion-editar.php?id={id}
```

El `id` se valida como entero positivo antes de consultar datos. Si el valor no es válido, se muestra un mensaje controlado y no se presenta formulario.

## Validación de estado borrador

Después de cargar la cotización, la página revisa que:

```text
estado = borrador
```

Si la cotización no existe o su estado no es `borrador`, la edición se bloquea visualmente. No se muestran stack traces ni detalles técnicos.

## Campos mostrados

El formulario preparatorio muestra:

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
- primer detalle:
  - `descripcion`
  - `cantidad`
  - `unidad`
  - `precio_unitario_neto`
  - `descuento_monto`

Toda salida dinámica se imprime con `ViewFormatter::e()`.

## Enlaces agregados

En `cotizaciones.php`:

- Se mantiene el enlace `Ver detalle`.
- Se agrega `Editar` solo para cotizaciones con estado `borrador`.

En `cotizacion-detalle.php`:

- Se agrega `Editar borrador` solo cuando la cotización está en estado `borrador`.
- No se muestra enlace de edición para estados emitidos o posteriores.

## Uso preparatorio de CSRF

La página de edición incluye un token CSRF con clave:

```text
quote_draft_edit
```

Esto deja preparada la vista para un futuro endpoint de actualización, pero en esta etapa no se procesa POST real.

La vista de edición es solo visual/preparatoria: no usa `method="post"` ni `action="cotizacion-actualizar.php"`. Esto evita que el navegador envíe accidentalmente el formulario al presionar Enter dentro de un campo mientras el endpoint real todavía no existe.

## Qué NO se implementó

- No se creó `cotizacion-actualizar.php`.
- No se implementó POST de edición.
- No existe acción POST activa desde la vista preparatoria.
- No se guardan cambios.
- No se actualiza base de datos.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se implementó AJAX.
- No se implementó API JSON.
- No se agregó cálculo en frontend.
- No se agregaron múltiples líneas dinámicas.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-edit-draft-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-edit-draft-contract.php
```

La herramienta valida que exista la página de edición, que listado y detalle enlacen a ella solo como preparación, que la vista no use `method="post"` ni `action="cotizacion-actualizar.php"`, que no exista `cotizacion-actualizar.php` y que no se hayan agregado operaciones de escritura.

## Prueba manual recomendada

1. Crear o ubicar una cotización en estado `borrador`.
2. Abrir `cotizaciones.php`.
3. Confirmar que el listado muestra enlace `Editar` solo para ese borrador.
4. Abrir `cotizacion-editar.php?id={id}`.
5. Confirmar que los datos reales se cargan en el formulario.
6. Abrir un `id` inválido y confirmar mensaje controlado.
7. Abrir una cotización no borrador y confirmar que la edición queda bloqueada.
8. Confirmar que no existe guardado real.

## Próxima etapa recomendada

Implementar el endpoint protegido de actualización de borradores, reutilizando validación backend, CSRF, POST/Redirect/GET y reglas que permitan actualizar solo cotizaciones en estado `borrador`.
