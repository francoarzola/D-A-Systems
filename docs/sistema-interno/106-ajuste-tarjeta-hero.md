# 106 - Ajuste de tarjeta flotante del Hero

## Problema detectado

La tarjeta flotante del Hero en `index.html` comunicaba correctamente la propuesta de soporte TI, pero contenía un título y una lista de tres puntos. Esa densidad visual hacía que el cuadro compitiera con la foto principal del consultor y cliente.

## Criterio visual

Se decidió mantener la tarjeta porque aporta contexto comercial, pero reducir su altura y su carga textual. La tarjeta debe apoyar la lectura del Hero, no dominar la imagen.

El criterio aplicado fue:

- Mantener el ícono existente.
- Mantener una tarjeta visible y corporativa.
- Usar un título breve.
- Usar una sola frase.
- Eliminar la lista de tres puntos.
- Reducir padding y ancho máximo de la tarjeta.
- Mantener comportamiento responsive.

## Microcopy anterior

Título:

```text
Tu tecnología, en manos responsables
```

Lista:

```text
Soporte técnico con registro de cada atención
Equipos, redes, correos y activos TI administrados
Respaldos activos y continuidad operativa real
```

## Microcopy nuevo

Título:

```text
Soporte TI claro y documentado
```

Frase:

```text
Revisamos tu situación, priorizamos riesgos y dejamos próximos pasos por escrito.
```

## Archivos modificados

- `index.html`
- `assets/scss/sections/_hero.scss`
- `assets/css/main.css`

## Archivos creados

- `sistema/tools/check-public-hero-card-contract.php`
- `docs/sistema-interno/106-ajuste-tarjeta-hero.md`

## Qué se mantuvo intacto

- H1 principal del Hero.
- CTA principal.
- CTA secundario.
- `hero-support-note`.
- Ruta de imagen `assets/img/about/about-8.png`.
- SEO/meta/canonical.
- Formulario de contacto.
- `config/contact.php`.
- Imágenes.
- Sitemap y robots.
- Laravel y cualquier estructura futura.

## Validaciones ejecutadas

La etapa define ejecutar:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-public-hero-card-contract.php
& $php sistema/tools/check-public-hero-card-contract.php

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

No se tocó Laravel, no se creó `laravel-app`, no se modificó base de datos, formulario, `config/contact.php`, imágenes, sitemap, robots, páginas internas, Composer ni vendor.
