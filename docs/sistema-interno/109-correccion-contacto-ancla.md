# 7F.10 - Correccion de visibilidad de contacto al navegar por ancla

## Problema detectado

Al navegar hacia `#contact` desde el menu superior o desde enlaces internos, el navegador llegaba a la seccion Contacto, pero en algunos casos quedaba visible solo el titulo y una zona blanca. El formulario aparecia despues de un pequeno scroll manual.

## Causa probable

La seccion Contacto mantenia animaciones AOS en el contenedor funcional principal y en las dos columnas del formulario/sidebar. Al llegar por ancla, esos bloques podian quedar pendientes de activacion hasta que AOS recibiera un nuevo evento de scroll.

## Correccion aplicada

En `index.html` se quitaron `data-aos` y `data-aos-delay` del contenedor principal de contacto:

```html
<div class="container">
```

Tambien se quitaron los atributos AOS de las dos columnas principales:

```html
<div class="col-lg-7 order-lg-1 order-2">
<div class="col-lg-5 order-lg-2 order-1">
```

Se mantuvo AOS en el titulo de seccion, porque el problema afectaba al bloque funcional del formulario y a la informacion lateral.

## Por que no se toco main.js

No fue necesario modificar `assets/js/main.js`. La logica de BootstrapMade para hash, scrollspy, menu movil y smooth scroll puede mantenerse intacta. El problema se resolvio evitando que el contenido funcional de contacto dependiera de animaciones de entrada.

## Que se mantuvo intacto

Se mantuvieron intactos el formulario, `action`, `method`, campos, `csrf_token`, honeypot `website`, `privacy_consent`, clases Bootstrap, navegacion, SEO tecnico, imagenes, CSS/SCSS, sitemap, robots y paginas legales.

## Validaciones ejecutadas

Validaciones definidas para esta etapa:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-public-contact-anchor-contract.php
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
```

## Confirmacion de alcance

Laravel no fue tocado. No se modifico base de datos, formulario PHP, `config/contact.php`, CSS/SCSS, imagenes, SEO tecnico, sitemap, robots, paginas legales, navegacion ni H1 principales.
