# 7F.14 - Configuracion SMTP privada

## Objetivo

Permitir que el formulario publico de D&A Systems use una configuracion SMTP privada ubicada fuera de `public_html`, sin versionar claves reales ni exponer secretos en `.htaccess`.

## Por que no guardar la clave SMTP en .htaccess

`.htaccess` pertenece al arbol publico del sitio. Aunque Apache lo interpreta como configuracion, no es el lugar adecuado para guardar secretos. Si se copia, respalda o comparte el paquete web, la clave podria quedar expuesta.

## Por que no guardar la clave dentro de public_html

Los archivos dentro de `public_html` forman parte del document root publico. Aunque existan reglas de bloqueo, una configuracion incorrecta del hosting podria exponer archivos sensibles. Por eso la clave SMTP real debe vivir fuera del document root.

## Ruta recomendada en cPanel

Crear el archivo privado en:

```text
/home/jjvxkghg/private/dasystems/contact.php
```

Permisos recomendados:

```text
private/ 700
private/dasystems/ 700
contact.php 600
```

## Contenido ejemplo del archivo privado

Este archivo es solo ejemplo y no debe subirse a GitHub:

```php
<?php
return [
    'receiving_email_address' => 'contacto@dasystems.cl',
    'smtp' => [
        'host' => 'mail.dasystems.cl',
        'username' => 'contacto@dasystems.cl',
        'password' => 'CLAVE_REAL_DEL_CORREO',
        'port' => 465,
        'encryption' => 'ssl',
        'mailer' => 'contacto@dasystems.cl',
    ],
    'auto_reply_enabled' => true,
];
```

`CLAVE_REAL_DEL_CORREO` es un placeholder. La clave real se escribe solo en el archivo privado del hosting.

## Ruta alternativa por variable de entorno

Si se necesita cambiar la ruta privada, se puede definir:

```text
DA_SYSTEMS_PRIVATE_CONTACT_CONFIG=/home/jjvxkghg/private/dasystems/contact.php
```

Si esa variable no existe, `config/contact.php` intenta cargar por defecto:

```text
dirname(__DIR__, 2) . '/private/dasystems/contact.php'
```

## Fallback seguro

Si no existe archivo privado, el sistema mantiene compatibilidad con variables `DA_SYSTEMS_*`, incluyendo `DA_SYSTEMS_SMTP_HOST`, `DA_SYSTEMS_SMTP_USERNAME`, `DA_SYSTEMS_SMTP_PASSWORD`, `DA_SYSTEMS_SMTP_PORT`, `DA_SYSTEMS_SMTP_ENCRYPTION`, `DA_SYSTEMS_SMTP_MAILER`, `DA_SYSTEMS_RECEIVING_EMAIL` y `DA_SYSTEMS_AUTO_REPLY_ENABLED`.

Si no existe archivo privado ni variables, el correo receptor queda como `contacto@dasystems.cl` y `auto_reply_enabled` queda en `false`.

## Como probar sin imprimir contrasena

Ejecutar una prueba PHP que cargue `config/contact.php` y revise solo claves, nunca valores sensibles:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php -r "$config = require 'config/contact.php'; echo isset($config['smtp']) ? 'smtp configurado' : 'smtp no configurado';"
```

No imprimir `password`.

## Como probar el formulario

1. Crear el archivo privado fuera de `public_html`.
2. Revisar permisos.
3. Abrir el sitio publico.
4. Enviar un mensaje de prueba.
5. Confirmar recepcion en `contacto@dasystems.cl`.
6. Confirmar que Reply-To corresponda al correo ingresado por el cliente.

## Password antiguo de Gmail

Si se uso un password de aplicacion de Gmail durante pruebas, revocarlo desde la cuenta Google cuando ya no se use. El remitente SMTP recomendado para produccion es `contacto@dasystems.cl`.

## Remitente y Reply-To

El remitente SMTP recomendado es `contacto@dasystems.cl`.

El Reply-To debe seguir siendo el correo del cliente para responder directamente desde el gestor de correo.

## Hardening post-despliegue

Se sincronizo el bloqueo de `/vendor/` en `.htaccess` para evitar acceso HTTP directo a dependencias PHP.

## Confirmacion

No se agregaron claves reales al repositorio. No se modifico HTML publico, CSS, JavaScript, imagenes, formulario visual, sitemap, robots, Composer, vendor, Laravel, base de datos ni `build/`.
