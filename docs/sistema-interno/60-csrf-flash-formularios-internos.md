# 60. CSRF y mensajes flash para formularios internos

## Objetivo

Crear soporte reutilizable para proteger futuros formularios internos con token CSRF y para mostrar mensajes flash después de redirecciones, sin implementar todavía formularios funcionales ni endpoints POST de cotizaciones.

## Clase CsrfToken

Archivo:

```text
sistema/app/Security/CsrfToken.php
```

Namespace:

```php
DAndASystems\Internal\Security
```

Métodos disponibles:

- `generate(string $key = 'default'): string`
- `get(string $key = 'default'): ?string`
- `validate(?string $token, string $key = 'default'): bool`
- `inputField(string $key = 'default'): string`

## Reglas CSRF

- Trabaja con sesión PHP activa.
- Guarda tokens en `$_SESSION`.
- Permite claves separadas por formulario, por ejemplo `quote_draft`.
- Genera tokens con `random_bytes`.
- No regenera el token si ya existe uno válido para la clave.
- Normaliza el contenedor de sesión si detecta datos corruptos o no esperados en `_internal_csrf_tokens`.
- Valida con `hash_equals`.
- `inputField()` devuelve un campo hidden con valor escapado mediante `htmlspecialchars`.
- No imprime nada directamente.
- No usa base de datos.

## Clase FlashMessage

Archivo:

```text
sistema/app/Support/FlashMessage.php
```

Namespace:

```php
DAndASystems\Internal\Support
```

Métodos disponibles:

- `set(string $type, string $message): void`
- `get(): ?array`
- `pull(): ?array`
- `has(): bool`

## Reglas de mensajes flash

- Trabaja con sesión PHP activa.
- Guarda un mensaje en `$_SESSION`.
- Tipos permitidos:
  - `success`
  - `error`
  - `warning`
  - `info`
- Si se recibe un tipo no permitido, se normaliza a `info`.
- `get()` lee el mensaje sin eliminarlo.
- `pull()` lee el mensaje y lo elimina de sesión.
- No imprime HTML directamente.
- No usa base de datos.

## Uso futuro en cotizaciones

En el futuro endpoint de guardado de borradores, el flujo esperado será:

```text
formulario nueva cotización
  -> input CSRF con CsrfToken::inputField('quote_draft')
  -> endpoint POST protegido
  -> CsrfToken::validate(...)
  -> QuoteService::createDraft(...)
  -> FlashMessage::set(...)
  -> redirección a listado o detalle
```

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-csrf-and-flash.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-csrf-and-flash.php
```

La herramienta:

- inicia una sesión controlada en CLI
- prueba generación de token
- prueba validación correcta
- prueba validación incorrecta
- prueba normalización de contenedor CSRF corrupto
- prueba `inputField()`
- prueba `set`, `get`, `pull` y `has`
- confirma que `pull()` elimina el mensaje
- no carga configuración de base de datos
- no usa conexión PDO

## Qué NO se implementó

- No se implementó endpoint POST.
- No se implementó formulario público funcional.
- No se implementó guardado desde navegador.
- No se implementó edición.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se crearon controllers.
- No se modificaron páginas públicas.
- No se modificó la estructura de base de datos.
- No se ejecutó SQL DDL.

## Próxima etapa recomendada

La próxima etapa recomendada es crear el endpoint POST protegido para guardar borradores desde el navegador, reutilizando `CsrfToken`, `FlashMessage` y `QuoteService::createDraft()`.
