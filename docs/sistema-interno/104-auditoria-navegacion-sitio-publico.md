# 104 - Auditoría de navegación del sitio público

## Hallazgos encontrados

La auditoría 7F.05 revisó las páginas públicas principales antes de iniciar el trabajo en Laravel:

- `index.html`
- `nosotros.html`
- `servicios-ti.html`
- `soluciones-ti.html`
- `politica-privacidad.html`
- `terminos-condiciones.html`
- `sitemap.xml`
- `robots.txt`

El hallazgo principal fue que el menú de `index.html` era más largo que el de las páginas internas y no incluía un enlace visible a `nosotros.html` en la navegación principal. También había diferencias entre las páginas internas: algunas incluían “Cómo trabajamos” y “Preguntas” dentro del menú superior, mientras las legales ya usaban una navegación más simple.

No se detectaron referencias a `D&A Systems SpA`, `dasystemstechnology@gmail.com`, rutas `.webp` antiguas ni mojibake en las páginas auditadas después de las correcciones legales previas.

## Cambios aplicados

Se unificó la navegación principal de las páginas públicas comerciales:

- `index.html`
- `nosotros.html`
- `servicios-ti.html`
- `soluciones-ti.html`

El menú principal quedó alineado con las páginas legales:

```text
Inicio | Servicios | Soluciones | Nosotros | Contacto
```

No se eliminaron secciones del `index.html`. Solo se retiraron del menú superior enlaces secundarios como “¿Te reconoces?”, “Cómo trabajamos” y “Preguntas” para mejorar claridad comercial.

## Decisión de menú

Se eligió una navegación corta y B2B:

- **Inicio**: entrada general al sitio.
- **Servicios**: acceso directo a la página de servicios TI.
- **Soluciones**: acceso directo a soluciones por necesidad de negocio.
- **Nosotros**: refuerza confianza comercial y trazabilidad.
- **Contacto**: llamada a la acción principal.

La decisión evita un menú demasiado largo y mantiene foco en conversión, confianza y exploración clara.

## Justificación de agregar Nosotros

`nosotros.html` ya existía, estaba en sitemap y footer, pero faltaba visibilidad directa en el menú principal del `index.html`. Para un sitio corporativo B2B, la página “Nosotros” ayuda a validar quién presta el servicio, cómo trabaja y por qué confiar antes de contactar.

## Archivos modificados

- `index.html`
- `nosotros.html`
- `servicios-ti.html`
- `soluciones-ti.html`

## Archivos creados

- `sistema/tools/check-public-site-navigation-contract.php`
- `docs/sistema-interno/104-auditoria-navegacion-sitio-publico.md`

## Validaciones ejecutadas

La etapa define ejecutar:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-public-site-navigation-contract.php
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

No se tocó Laravel, no se creó `laravel-app`, no se modificó base de datos, no se tocó el formulario, no se modificó `config/contact.php`, no se tocaron imágenes, CSS ni SCSS.

Tampoco se cambiaron metadatos SEO, canonical, sitemap ni robots salvo la auditoría por contrato.

## Pendientes no bloqueantes

- Evaluar en una etapa posterior si el footer del `index.html` también debe simplificarse para replicar exactamente el menú principal.
- Revisar con analítica real si conviene volver a exponer “Preguntas” en navegación superior. Por ahora queda accesible dentro del sitio, pero no como entrada principal.
- Validar visualmente en móvil que el menú corto reduce fricción y mejora lectura.
