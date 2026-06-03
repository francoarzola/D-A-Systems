# 7F.13 - Despliegue multi-sitio en cPanel

## Objetivo

Documentar como desplegar D&A Systems en un hosting cPanel que alojara varios sitios landing page o sitios corporativos independientes.

D&A Systems debe operar como un sitio independiente, con su propio document root asignado al dominio `dasystems.cl`.

## Regla principal

El contenido de `build/cpanel-production/` debe subirse directamente a la raiz del document root del dominio `dasystems.cl`.

No se debe subir la carpeta `cpanel-production` como subcarpeta.

Correcto:

```text
/home/usuario/dasystems.cl/index.html
/home/usuario/dasystems.cl/.htaccess
/home/usuario/dasystems.cl/assets/
/home/usuario/dasystems.cl/forms/
/home/usuario/dasystems.cl/config/
```

Incorrecto:

```text
/home/usuario/dasystems.cl/cpanel-production/index.html
```

## No mezclar sitios

No se debe mezclar D&A Systems con otros sitios dentro de una raiz compartida. Cada dominio debe tener su propio document root para evitar interferencias de rutas, configuraciones y archivos publicos.

Ejemplo recomendado:

```text
/home/usuario/dasystems.cl/
/home/usuario/arzola.dev/
/home/usuario/zeiner.cl/
```

## Archivos por sitio

Cada dominio debe tener sus propios archivos y carpetas:

- `.htaccess`
- `robots.txt`
- `sitemap.xml`
- `assets/`
- `forms/`
- `config/`
- `vendor/`

## Riesgo de mezclar .htaccess

El `.htaccess` de D&A Systems contiene reglas de seguridad para bloquear rutas internas como `config/`, `storage/`, `docs/`, `sistema/tools/` y `sistema/config/`.

Si ese `.htaccess` se sube a una raiz comun compartida con otros sitios, podria bloquear carpetas usadas por otros proyectos. Por eso debe quedar solo dentro del document root propio de D&A Systems.

## robots.txt y sitemap.xml

`robots.txt` y `sitemap.xml` deben estar en la raiz del dominio.

Para `dasystems.cl`, deben quedar accesibles como:

```text
https://www.dasystems.cl/robots.txt
https://www.dasystems.cl/sitemap.xml
```

Si el sitio se sube dentro de una subcarpeta no configurada como document root, estos archivos no representaran correctamente al dominio.

## Configuracion del formulario

`config/contact.php` debe ser propio de cada sitio.

Las variables SMTP tambien deben configurarse por sitio. Para D&A Systems se usan variables `DA_SYSTEMS_SMTP_*`; otros sitios no deben reutilizarlas sin evaluar el nombre y el alcance.

## Vendor por sitio

Se recomienda mantener `vendor/` por sitio, no un `vendor/` global compartido.

Esto evita conflictos si otro dominio requiere versiones distintas de dependencias PHP o si se actualiza una libreria para un proyecto y no para otro.

## Checklist de despliegue multi-sitio

- Dominio agregado en cPanel.
- Document root propio configurado para `dasystems.cl`.
- SSL activo para `dasystems.cl` y `www.dasystems.cl`.
- Contenido de `build/cpanel-production/` subido a la raiz del document root.
- `.htaccess` presente.
- `robots.txt` presente.
- `sitemap.xml` presente.
- `assets/`, `forms/`, `config/` y `vendor/` presentes.
- Formulario probado desde el dominio real.
- Rutas internas bloqueadas por `.htaccess`.
- Correo/SMTP configurado y probado.
- `robots.txt` accesible desde la raiz del dominio.
- `sitemap.xml` accesible desde la raiz del dominio.

## Advertencia sobre company.php

`sistema/config/company.php` puede contener valores `Pendiente`. Esto no bloquea el sitio publico ni el formulario de contacto.

Antes de emitir cotizaciones oficiales desde el modulo interno, esos datos comerciales deben completarse.

## Que no se modifico

Esta etapa solo documenta y valida preparacion multi-sitio. No modifica HTML publico, CSS/SCSS, JavaScript, imagenes, formulario PHP, `config/contact.php`, `.htaccess`, `robots.txt`, `sitemap.xml`, Composer, vendor, Laravel, base de datos ni `build/`.

## Validacion

Contrato creado:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-cpanel-multisite-readiness-contract.php
& $php sistema/tools/check-cpanel-multisite-readiness-contract.php
```
