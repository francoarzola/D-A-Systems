# 105 - Corrección H1 en soluciones TI

## Problema detectado

La auditoría integral del sitio público detectó que `soluciones-ti.html` tenía dos etiquetas `<h1>`:

1. `Soluciones TI para empresas`, como título principal de la página.
2. `Convertimos problemas tecnológicos en acciones claras y documentadas`, dentro del bloque con clase `project-title`.

Esto generaba una jerarquía semántica incorrecta para SEO técnico y accesibilidad, porque la página debe tener un solo encabezado principal.

## Corrección aplicada

Se cambió únicamente la etiqueta del segundo encabezado:

```html
<h1 class="project-title">Convertimos problemas tecnológicos en acciones claras y documentadas</h1>
```

por:

```html
<h2 class="project-title">Convertimos problemas tecnológicos en acciones claras y documentadas</h2>
```

La clase `project-title` se mantuvo intacta para conservar el diseño visual actual.

## Archivos modificados

- `soluciones-ti.html`

## Archivos creados

- `sistema/tools/check-public-site-heading-contract.php`
- `docs/sistema-interno/105-correccion-h1-soluciones-ti.md`

## Qué se mantuvo intacto

- Texto comercial.
- Clase CSS `project-title`.
- Diseño visual.
- SEO/meta/canonical.
- Navegación.
- Imágenes.
- Formulario.
- `config/contact.php`.
- Sitemap y robots.
- Laravel y cualquier estructura futura del portal.

## Validaciones ejecutadas

La etapa define ejecutar:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-public-site-heading-contract.php
& $php sistema/tools/check-public-site-heading-contract.php

& $php sistema/tools/check-public-site-navigation-contract.php
& $php sistema/tools/check-public-legal-pages-contract.php
& $php sistema/tools/check-public-site-seo-contract.php
& $php sistema/tools/check-cpanel-deployment-readiness-contract.php
& $php sistema/tools/check-public-site-image-audit-contract.php
& $php sistema/tools/check-public-site-hero-about-images-contract.php

git diff --check
git status
```

## Confirmación de alcance

No se tocó Laravel, no se creó `laravel-app`, no se modificó base de datos, formulario, `config/contact.php`, imágenes, CSS/SCSS, sitemap, robots ni páginas públicas fuera de `soluciones-ti.html`.
