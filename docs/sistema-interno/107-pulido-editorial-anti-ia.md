# 7F.08 - Pulido editorial anti IA del sitio publico

## Problema detectado

La auditoria editorial detecto que el sitio publico de D&A Systems comunicaba bien su propuesta, pero repetia con demasiada frecuencia ideas como orden, documentacion, trazabilidad, continuidad, proximos pasos claros y acciones claras.

El ajuste busca que las frases de mayor exposicion suenen mas concretas, naturales y cercanas para una empresa B2B de servicios TI en Chile.

## Criterio editorial aplicado

Se aplico una edicion quirurgica sobre textos existentes. No se reescribio el sitio completo, no se agregaron secciones y no se modifico la estructura visual.

El criterio fue mantener tono profesional, espanol chileno neutro, claridad comercial y SEO natural, evitando promesas absolutas o lenguaje excesivamente publicitario.

## Frases reemplazadas

En `index.html` se ajusto el hero principal para indicar que se revisan equipos, redes, respaldos y sistemas criticos, dejando registro de lo encontrado y ayudando a decidir que resolver primero.

En `index.html` se reemplazo la nota bajo CTA por: "Diagnostico inicial sin compromiso — te indicamos que revisar primero y que puede esperar."

En `index.html` se mantuvo el titulo "Soporte TI claro y documentado" y se cambio la frase de apoyo por: "Identificamos riesgos, urgencias y acciones recomendadas."

En `index.html` se mejoro el texto de "Todo documentado" para explicar que la informacion no debe depender de la memoria de alguien.

En `servicios-ti.html` se reescribio la introduccion para indicar que el cliente no necesita llegar con el diagnostico hecho.

En `soluciones-ti.html` se cambio el titulo secundario por "De problemas TI repetidos a un plan de accion concreto" y se ajusto el subtitulo para evitar la frase repetida sobre sobredimensionar costos.

En `nosotros.html` se ajusto la mision para hablar de soporte tecnico responsable, registro de lo realizado y recomendaciones comprensibles.

## Archivos modificados

- `index.html`
- `servicios-ti.html`
- `soluciones-ti.html`
- `nosotros.html`
- `sistema/tools/check-public-hero-card-contract.php`

## Archivos creados

- `sistema/tools/check-public-editorial-polish-contract.php`
- `docs/sistema-interno/107-pulido-editorial-anti-ia.md`

## Que se mantuvo intacto

Se mantuvieron intactos Laravel, formularios, `config/contact.php`, base de datos, imagenes, CSS/SCSS, sitemap, robots, paginas legales, Composer, vendor, navegacion, H1 principales, meta title, meta description y canonical.

## Validaciones ejecutadas

Validaciones definidas para esta etapa:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"

& $php -l sistema/tools/check-public-editorial-polish-contract.php
& $php sistema/tools/check-public-editorial-polish-contract.php

& $php -l sistema/tools/check-public-hero-card-contract.php
& $php sistema/tools/check-public-hero-card-contract.php

& $php sistema/tools/check-public-site-heading-contract.php
& $php sistema/tools/check-public-site-navigation-contract.php
& $php sistema/tools/check-public-legal-pages-contract.php
& $php sistema/tools/check-public-site-seo-contract.php
& $php sistema/tools/check-cpanel-deployment-readiness-contract.php
& $php sistema/tools/check-public-site-image-audit-contract.php
& $php sistema/tools/check-public-site-hero-about-images-contract.php
```

## Confirmacion de alcance

Laravel no fue tocado.

No se modifico SEO tecnico ni estructura visual. Los cambios se limitaron a texto dentro de etiquetas existentes y a los contratos/documentacion de la etapa.
