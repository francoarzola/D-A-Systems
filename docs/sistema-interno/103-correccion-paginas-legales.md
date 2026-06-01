# 103 - Corrección de páginas legales públicas

## Problema detectado

En `politica-privacidad.html` y `terminos-condiciones.html` existía una duplicación visual en el bloque inicial. Ambas páginas ya tenían una cabecera `page-title` con breadcrumb, título principal e introducción, pero luego el contenido legal comenzaba con otro bloque introductorio que repetía el título o reforzaba la misma jerarquía.

Esto generaba una lectura redundante y, en el caso de privacidad, dejaba más de un `<h1>` en la misma página.

## Criterio de corrección

Se mantuvo una sola cabecera inicial clara por página:

- `page-title` conserva el único `<h1>`.
- Los breadcrumbs se mantienen porque aportan navegación.
- El bloque interno de introducción se conserva como apoyo contextual, pero sin repetir el título principal.
- Se mantiene la estructura semántica general: header global, main, page-title, contenido legal principal y footer.
- Se normalizaron textos con mojibake para conservar acentos y comillas legibles.

## Archivos modificados

- `politica-privacidad.html`
- `terminos-condiciones.html`

## Archivos creados

- `sistema/tools/check-public-legal-pages-contract.php`
- `docs/sistema-interno/103-correccion-paginas-legales.md`

## Qué se mantuvo intacto

- Canonical de cada página.
- Favicon `favicon-dasystems.png`.
- Apple touch icon `apple-touch-icon.png`.
- Correo `contacto@dasystems.cl`.
- Header global.
- Footer.
- Breadcrumbs.
- Contenido legal de fondo.
- Estructura visual BootstrapMade.
- Formularios y configuración de contacto.
- Laravel y cualquier lógica backend.

## Validaciones ejecutadas

La etapa define ejecutar:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-public-legal-pages-contract.php
& $php sistema/tools/check-public-legal-pages-contract.php

& $php sistema/tools/check-public-site-seo-contract.php
& $php sistema/tools/check-cpanel-deployment-readiness-contract.php
& $php sistema/tools/check-public-site-image-audit-contract.php

git diff --check
git status
```

## Confirmación de alcance

No se tocó Laravel. No se tocaron formularios, `config/contact.php`, imágenes, CSS/SCSS, `index.html`, `nosotros.html`, `servicios-ti.html` ni `soluciones-ti.html`.

Tampoco se reintrodujo `D&A Systems SpA`, no se cambió el correo público y no se modificaron los metadatos SEO/canonical existentes.
