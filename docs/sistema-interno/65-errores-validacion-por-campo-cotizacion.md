# 65. Errores de validación por campo en cotización

## Objetivo

Mejorar la experiencia del formulario mínimo de cotización mostrando los errores de validación junto a los campos correspondientes, sin cambiar las reglas de validación existentes ni implementar nuevas acciones.

## Qué se guarda temporalmente

Cuando falla una validación de negocio al intentar guardar un borrador, el endpoint conserva temporalmente:

- los datos ingresados del formulario en `quote_draft`
- los mensajes de validación en `quote_draft_errors`

Ambos valores viven solo en sesión y se consumen después de la redirección a `cotizaciones.php`.

## Clave de errores

Los errores se guardan con la clave:

```text
quote_draft_errors
```

Esta clave está separada de `quote_draft` para mantener independientes los valores del formulario y los mensajes de validación.

## Integración con cotizaciones-guardar.php

Si `QuoteService::createDraft()` devuelve `success => false`, el endpoint:

1. Guarda `$draftData` en `FormState` con clave `quote_draft`.
2. Guarda los errores de validación con clave `quote_draft_errors`.
3. Define un mensaje flash de advertencia.
4. Redirige a `cotizaciones.php` usando POST/Redirect/GET.

Si el guardado es exitoso, si el método no es POST, si el CSRF es inválido o si ocurre un error técnico, se limpian `quote_draft` y `quote_draft_errors`.

## Integración con cotizaciones.php

La página consume los errores con:

```php
FormState::pull('quote_draft_errors')
```

Luego muestra:

- un resumen de errores sobre el formulario
- mensajes bajo campos específicos
- estilos visuales para campos con error

## Campos con errores visibles

La vista muestra errores junto a:

- `nombre_cliente`
- `correo_contacto`
- `fecha_cotizacion`
- `valido_hasta`
- `detalles[0][descripcion]`
- `detalles[0][cantidad]`
- `detalles[0][precio_unitario_neto]`
- `detalles[0][descuento_monto]`

Como el validador actual devuelve mensajes planos, `cotizaciones.php` usa funciones locales para asociar mensajes conocidos con campos del formulario.

## Escape de errores

Todos los mensajes se imprimen con:

```php
ViewFormatter::e()
```

No se imprimen arrays completos, HTML crudo desde sesión, stack traces ni detalles técnicos.

## Estilos agregados

Se agregaron estilos mínimos en:

```text
sistema/public/assets/css/internal.css
```

Clases nuevas:

- `.field-error`
- `.field-has-error`
- `.form-error-summary`
- `.form-error-summary ul`

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-field-errors-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-field-errors-contract.php
```

La herramienta revisa que el endpoint guarde y limpie `quote_draft_errors`, que la vista consuma y muestre errores por campo, y que no se agreguen campos prohibidos al formulario.

## Prueba manual recomendada

1. Iniciar sesión.
2. Abrir `cotizaciones.php`.
3. Enviar el formulario con cliente vacío o correo inválido.
4. Confirmar que aparece el mensaje flash general.
5. Confirmar que los datos ingresados se conservan.
6. Confirmar que se muestra resumen de errores.
7. Confirmar que los errores aparecen bajo los campos correspondientes.
8. Corregir los datos y guardar correctamente.

## Qué NO se implementó

- No se implementó edición.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se implementó PDF.
- No se implementó correo.
- No se implementó AJAX.
- No se implementó API JSON.
- No se agregaron múltiples líneas dinámicas.
- No se agregó cálculo en frontend.
- No se cambiaron reglas de validación.

## Próxima etapa recomendada

La siguiente etapa recomendada es mejorar progresivamente el formulario real, manteniendo siempre validación backend, CSRF, POST/Redirect/GET y cálculo centralizado en servidor.
