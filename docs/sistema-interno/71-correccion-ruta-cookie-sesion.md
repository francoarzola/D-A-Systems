# 71. Corrección de ruta de cookie de sesión

## Problema detectado

El login funcionaba con credenciales correctas, pero fallaba en navegador con el mensaje:

```text
No fue posible procesar la solicitud.
```

El problema aparecía al levantar el servidor local con:

```bash
php -S 127.0.0.1:8080 -t sistema/public
```

Y abrir:

```text
http://127.0.0.1:8080/login.php
```

## Causa

`SessionManager` usaba una ruta fija para la cookie de sesión:

```php
private string $path = '/sistema';
```

Cuando el servidor local sirve directamente desde `sistema/public`, la página visible es:

```text
/login.php
```

Pero la cookie estaba limitada a:

```text
/sistema
```

El navegador no enviaba esa cookie al hacer POST hacia `/login.php`, por lo que la sesión no conservaba el token CSRF entre GET y POST.

## Relación con CSRF

El token CSRF del login se guarda en sesión. Si la cookie de sesión no vuelve en el POST, el servidor ve una sesión distinta o vacía, y la validación CSRF falla aunque el usuario y la contraseña sean correctos.

## Cambio aplicado

Se modificó:

```text
sistema/app/Core/SessionManager.php
```

Ahora `SessionManager` resuelve dinámicamente el path de cookie usando `SCRIPT_NAME`.

Se agregó:

```php
private function resolveCookiePath(): string
```

La ruta fija `/sistema` fue reemplazada por un fallback seguro:

```php
private string $fallbackPath = '/';
```

## Comportamiento esperado

Si `SCRIPT_NAME` es:

```text
/login.php
```

El cookie path será:

```text
/
```

Si `SCRIPT_NAME` es:

```text
/sistema/public/login.php
```

El cookie path será:

```text
/sistema/public
```

Si `SCRIPT_NAME` es:

```text
/sistema/public/cotizaciones.php
```

El cookie path será:

```text
/sistema/public
```

Si no hay `SCRIPT_NAME` válido, se usa `/`.

## Qué no se cambió

- No se cambió `login.php`.
- No se cambió `CsrfService`.
- No se cambió `AuthGuard`.
- No se cambió el nombre de sesión `DA_SYSTEMS_INTERNAL_SESSION`.
- No se desactivó CSRF.
- No se modificó base de datos.
- No se modificó Cotizaciones.
- No se tocó emisión.
- No se crearon endpoints nuevos.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-session-cookie-path-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-session-cookie-path-contract.php
```

La herramienta verifica que `SessionManager` use `resolveCookiePath()`, `SCRIPT_NAME`, `session_set_cookie_params`, `session_regenerate_id`, `session_destroy`, `httponly`, `SameSite=Lax` y que no conserve la ruta fija antigua `/sistema`.

## Pruebas realizadas

Validaciones esperadas:

```bash
php -l sistema/app/Core/SessionManager.php
php -l sistema/tools/check-session-cookie-path-contract.php
php sistema/tools/check-session-cookie-path-contract.php
```

## Servidor local recomendado

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe -S 127.0.0.1:8080 -t sistema/public
```

URL recomendada:

```text
http://127.0.0.1:8080/login.php
```

## Prueba manual recomendada

1. Levantar el servidor local.
2. Abrir `http://127.0.0.1:8080/login.php`.
3. Ingresar con usuario local válido.
4. Verificar redirección a `dashboard.php`.
5. Confirmar que el navegador conserva la cookie de sesión al enviar el POST.
