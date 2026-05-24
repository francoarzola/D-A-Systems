# 61. Endpoint POST para guardar borrador de cotización

## Objetivo

Crear un endpoint POST protegido para guardar borradores de cotización desde navegador usando sesión, autenticación, CSRF, mensajes flash, `QuoteService::createDraft()` y el patrón POST/Redirect/GET.

Esta etapa no convierte todavía la maqueta visual completa de `cotizaciones.php` en un formulario funcional.

## Endpoint creado

Archivo:

```text
sistema/public/cotizaciones-guardar.php
```

El endpoint:

- acepta solo método POST
- inicia sesión con `SessionManager`
- exige autenticación con `AuthGuard::requireAuth('login.php')`
- valida CSRF con clave `quote_draft`
- construye datos compatibles con `QuoteDraftValidator`
- usa `QuoteService::createDraft()`
- usa `FlashMessage`
- redirige con POST/Redirect/GET usando HTTP 303 See Other
- no imprime HTML
- no muestra errores técnicos

## Flujo POST/Redirect/GET

```text
POST cotizaciones-guardar.php
  -> sesión y autenticación
  -> validación CSRF
  -> normalización de datos POST permitidos
  -> QuoteService::createDraft()
  -> FlashMessage::set(...)
  -> redirección interna fija
```

Si el borrador se guarda correctamente, redirige a:

```text
cotizacion-detalle.php?id={quote_id}
```

Si falla validación, CSRF, método o persistencia, redirige a:

```text
cotizaciones.php
```

Todas las redirecciones del endpoint usan código HTTP `303` para evitar que el navegador reintente el POST al refrescar o volver a cargar.

## Validación de autenticación

El endpoint usa el mecanismo existente:

```php
$guard->requireAuth('login.php');
```

No modifica login, logout, timeout, `AuthGuard` ni `SessionManager`.

## Validación CSRF

El endpoint usa:

```php
CsrfToken::validate(..., 'quote_draft')
```

Si el token no es válido, define mensaje flash:

```text
La sesión del formulario expiró. Intente nuevamente.
```

## Uso de FlashMessage

Mensajes definidos:

- solicitud inválida: `La solicitud no es válida.`
- CSRF inválido: `La sesión del formulario expiró. Intente nuevamente.`
- validación de negocio fallida: `No fue posible guardar el borrador. Revise los datos ingresados.`
- guardado correcto: `Borrador de cotización guardado correctamente.`
- error técnico: `No fue posible guardar el borrador de cotización.`

## Uso de QuoteService

El endpoint instancia:

- `DatabaseConfig`
- `Connection`
- `QuoteRepository`
- `QuoteDraftValidator`
- `QuoteTotalsCalculator`
- `QuoteService`

Luego llama:

```php
$service->createDraft($draftData, $guard->userId());
```

## Datos aceptados desde POST

Cabecera:

- `form_action`
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

Detalles:

- `detalles[index][descripcion]`
- `detalles[index][cantidad]`
- `detalles[index][unidad]`
- `detalles[index][precio_unitario_neto]`
- `detalles[index][descuento_monto]`

## Datos ignorados o no aceptados desde navegador

El endpoint no acepta desde POST:

- `numero_cotizacion`
- `estado`
- `subtotal_neto`
- `iva_monto`
- `total`
- campos calculados de línea
- URL de retorno

Los totales son calculados por `QuoteTotalsCalculator` a través de `QuoteService`.

## Manejo de errores

- Método distinto a POST: mensaje flash de error y redirección a `cotizaciones.php`.
- CSRF inválido: mensaje flash de error y redirección a `cotizaciones.php`.
- Validación de negocio fallida: mensaje flash de warning y redirección a `cotizaciones.php`.
- Error técnico: mensaje flash genérico y redirección a `cotizaciones.php`.

El endpoint no muestra stack trace, credenciales ni detalles técnicos al navegador.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-save-endpoint-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-save-endpoint-contract.php
```

La herramienta verifica de forma estática que el endpoint exista y contenga las referencias esperadas, sin ejecutar POST real y sin tocar la base de datos.

## Qué NO se implementó

- No se convirtió la maqueta completa en formulario funcional.
- No se implementó edición.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se crearon controllers.
- No se creó AJAX.
- No se creó API JSON.
- No se modificó la estructura de base de datos.
- No se ejecutó SQL DDL.

## Próxima etapa recomendada

La próxima etapa recomendada es integrar gradualmente la sección visual de nueva cotización con un formulario real que apunte a `cotizaciones-guardar.php`, usando `CsrfToken::inputField('quote_draft')`.
