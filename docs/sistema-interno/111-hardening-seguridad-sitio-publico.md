# 7F.12 - Hardening de seguridad del sitio publico

## Problema detectado

Antes del despliegue en cPanel se confirmaron puntos reales de robustecimiento: prevenir listado de directorios, bloquear acceso HTTP a archivos sensibles, evitar indexacion de rutas internas, validar estrictamente el asunto del formulario y no exponer respuestas internas de la libreria de correo al usuario.

## Hallazgos reales confirmados

- `.htaccess` no tenia `Options -Indexes`.
- `robots.txt` no declaraba rutas internas como no indexables.
- `forms/contact.php` aceptaba `subject` sanitizado, pero sin whitelist estricta.
- El formulario podia devolver `$send_result` directamente al usuario si fallaba el envio principal.

## Hallazgos descartados del informe anterior

No se confirmo una necesidad inmediata de cambiar estructura HTML, formulario publico, CSS, JavaScript, imagenes, sitemap ni configuracion SMTP.

Tampoco se aplico CSP ni HSTS porque requieren validacion de produccion y HTTPS estable.

## Cambios aplicados

En `.htaccess` se agrego:

- `Options -Indexes`
- `DirectoryIndex index.html index.php`
- bloqueo de `.env`, `composer.json`, `composer.lock`
- bloqueo de extensiones sensibles
- bloqueo de rutas internas: `docs/`, `storage/`, `sistema/tools/`, `sistema/config/`, `config/`

En `robots.txt` se agregaron reglas preventivas `Disallow` para rutas internas.

En `forms/contact.php` se agrego whitelist estricta para los asuntos permitidos y respuesta generica ante fallos de envio.

## Que no se toco

No se modificaron Laravel, base de datos, paginas HTML publicas, CSS/SCSS, JavaScript, imagenes, `config/contact.php`, sitemap, Composer, vendor ni assets de terceros.

## Por que no se agrego CSP todavia

Una CSP estricta puede romper recursos de BootstrapMade, fuentes, estilos inline, scripts de vendor o integraciones futuras si no se prueba en modo reporte. Se deja fuera hasta una etapa especifica de CSP.

## Por que no se activo HSTS todavia

HSTS debe activarse solo cuando HTTPS este funcionando de forma estable en produccion. Activarlo antes puede bloquear acceso si hay problemas de certificado, subdominios o configuracion del hosting.

## Validaciones ejecutadas

Validaciones definidas para esta etapa:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l forms/contact.php
& $php -l config/contact.php
& $php -l forms/csrf-token.php
& $php -l sistema/tools/check-public-security-hardening-contract.php

& $php sistema/tools/check-public-security-hardening-contract.php
& $php sistema/tools/check-public-contact-anchor-contract.php
& $php sistema/tools/check-public-image-optimization-contract.php
& $php sistema/tools/check-public-site-hero-about-images-contract.php
& $php sistema/tools/check-public-site-image-audit-contract.php
& $php sistema/tools/check-public-editorial-polish-contract.php
& $php sistema/tools/check-public-hero-card-contract.php
& $php sistema/tools/check-public-site-heading-contract.php
& $php sistema/tools/check-public-site-navigation-contract.php
& $php sistema/tools/check-public-legal-pages-contract.php
& $php sistema/tools/check-public-site-seo-contract.php
& $php sistema/tools/check-cpanel-deployment-readiness-contract.php
& $php sistema/tools/check-cpanel-production-package-contract.php
```

## Confirmaciones

Laravel no fue tocado.

El formulario mantiene CSRF, honeypot, `privacy_consent` y rate limit.

## Advertencia pendiente

`sistema/config/company.php` contiene valores `Pendiente` para cotizaciones oficiales. Esta advertencia no bloquea el sitio publico, pero debe resolverse antes de usar datos comerciales definitivos.
