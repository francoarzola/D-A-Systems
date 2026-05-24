# 63. Feedback visual del formulario de cotización

## Objetivo

Mejorar la presentación visual de mensajes flash en `cotizaciones.php`, manteniendo seguridad, escape de salida y sin cambiar la lógica de guardado de borradores.

## Qué cambió en cotizaciones.php

La página sigue consumiendo mensajes con:

```php
FlashMessage::pull()
```

Ahora el bloque de mensaje usa clases específicas:

- `flash-message`
- `flash-message-success`
- `flash-message-error`
- `flash-message-warning`
- `flash-message-info`

También se agregaron funciones locales para normalizar el tipo y mostrar un título visible en español.

## Estilos agregados

En `sistema/public/assets/css/internal.css` se agregaron estilos mínimos para:

- contenedor del mensaje
- título del mensaje
- texto del mensaje
- variantes `success`, `error`, `warning` e `info`

El diseño mantiene una presentación discreta y coherente con las tarjetas existentes.

## Tipos soportados

- `success`
- `error`
- `warning`
- `info`

## Normalización del tipo

Antes de usar el tipo en la clase CSS, `cotizaciones.php` lo pasa por `normalizeFlashType()`.

Si el tipo no es uno de los permitidos, se usa `info`.

## Escape del mensaje

El tipo, título y mensaje se imprimen usando:

```php
ViewFormatter::e(...)
```

No se imprime HTML crudo desde sesión.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-flash-visual-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-flash-visual-contract.php
```

La herramienta verifica que `cotizaciones.php` normalice el tipo, escape la salida y use clases flash, y que `internal.css` contenga las variantes visuales esperadas.

## Prueba manual recomendada

1. Guardar un borrador correctamente.
2. Provocar una validación fallida.
3. Verificar que los mensajes se diferencien visualmente.
4. Confirmar que el listado real sigue visible.
5. Confirmar que el formulario mínimo sigue enviando a `cotizaciones-guardar.php`.

## Qué NO se implementó

- No se cambió la lógica de guardado.
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
- No se agregó cálculo frontend.
- No se agregaron múltiples líneas dinámicas.

## Próxima etapa recomendada

La próxima etapa recomendada es mejorar la experiencia del formulario real con persistencia de valores ingresados ante errores de validación, manteniendo el backend como fuente única de validación y cálculo.
