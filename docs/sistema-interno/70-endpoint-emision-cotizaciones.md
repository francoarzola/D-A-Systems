# 70. Endpoint de emision de cotizaciones

## Objetivo

Crear la entrada HTTP protegida para emitir una cotizacion en estado `borrador` y agregar el boton de emision en la vista de detalle, sin implementar PDF, correo, anulacion, AJAX ni API JSON.

Esta etapa conecta la interfaz con el nucleo backend creado en la etapa 7A.25.

## Endpoint creado

Se creo:

```text
sistema/public/cotizacion-emitir.php
```

El endpoint acepta solo `POST`, valida sesion, valida CSRF y llama a `QuoteService::issueDraft()`.

## Flujo POST/Redirect/GET

El flujo usa POST/Redirect/GET:

1. El usuario abre `cotizacion-detalle.php?id={id}`.
2. Si la cotizacion esta en `borrador`, se muestra el formulario de emision.
3. El formulario envia `POST` a `cotizacion-emitir.php`.
4. El endpoint procesa la emision.
5. Siempre redirige con HTTP `303`.
6. El navegador vuelve al detalle o al listado con un mensaje flash.

## Autenticacion

El endpoint inicia sesion con `SessionManager` y exige autenticacion con:

```php
$guard = new AuthGuard();
$guard->requireAuth('login.php');
```

No se modificaron `login`, `logout`, `AuthGuard`, `SessionManager` ni timeout.

## CSRF

La accion usa la clave:

```text
quote_issue
```

El detalle genera el campo con:

```php
CsrfToken::inputField('quote_issue')
```

El endpoint valida el token recibido antes de llamar al servicio.

## Llamada al servicio

El endpoint construye las dependencias normales de base de datos y llama:

```php
$result = $service->issueDraft($quoteId);
```

El endpoint no acepta estado, numero oficial ni totales desde el navegador. La regla de negocio se resuelve dentro de `QuoteService` y `QuoteRepository`.

## Que ocurre al emitir

Si la emision es exitosa:

- Se asigna `numero_cotizacion`.
- El estado cambia a `emitida`.
- Se guarda `actualizado_en = CURRENT_TIMESTAMP`.
- El usuario vuelve a `cotizacion-detalle.php?id={id}`.
- Se muestra mensaje flash de exito.

Si no se puede emitir por validacion o control:

- No se expone detalle tecnico.
- Se muestra mensaje controlado.
- Se redirige con HTTP `303`.

Si ocurre un error tecnico:

- No se muestra stack trace.
- Se muestra mensaje generico.
- Se redirige al detalle.

## Boton en detalle

Se modifico:

```text
sistema/public/cotizacion-detalle.php
```

El boton `Emitir cotizacion` aparece solo cuando:

```php
($quote['estado'] ?? null) === 'borrador'
```

El formulario usa:

```html
<form method="post" action="cotizacion-emitir.php">
```

Incluye:

- `csrf_token` generado para `quote_issue`.
- `cotizacion_id` con el ID real de la cotizacion.

## Por que solo aparece en borradores

La emision consume un correlativo oficial y convierte el borrador en cotizacion oficial. Por eso la interfaz no ofrece emision si la cotizacion ya esta emitida u otro estado.

El backend tambien valida el estado real bloqueando la fila, por lo que no depende de la visibilidad del boton.

## Datos no aceptados desde POST

El endpoint solo lee:

- `cotizacion_id`
- `csrf_token`

No acepta desde POST:

- `numero_cotizacion`
- `estado`
- `subtotal_neto`
- `iva_porcentaje`
- `iva_monto`
- `total`
- URL de retorno

## Herramienta CLI

Se creo:

```bash
php sistema/tools/check-quote-issue-endpoint-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-issue-endpoint-contract.php
```

La herramienta valida el contrato leyendo archivos fuente. No usa base de datos, no modifica archivos y no emite una cotizacion real.

## Prueba manual recomendada

1. Crear un borrador.
2. Abrir el detalle.
3. Verificar el boton `Emitir cotizacion`.
4. Emitir.
5. Verificar redireccion al detalle.
6. Verificar estado `emitida`.
7. Verificar `numero_cotizacion` asignado.
8. Verificar que ya no aparece boton de editar ni emitir.

## Que NO se implemento

- No se implemento PDF.
- No se implemento correo.
- No se implemento anulacion.
- No se implemento aceptacion ni rechazo.
- No se implemento envio al cliente.
- No se creo AJAX.
- No se creo API JSON.
- No se uso JavaScript.
- No se creo modal.
- No se modifico estructura de base de datos.
- No se ejecuto SQL DDL.

## Proxima etapa recomendada

La siguiente etapa recomendada es validar manualmente el flujo completo en ambiente local y luego preparar la etapa de salida documental o vista imprimible, manteniendo separada la emision oficial del envio al cliente.
