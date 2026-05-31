# 98 — Normalización SEO técnica del sitio público D&A Systems

## Objetivo
Normalizar el SEO técnico básico de las páginas públicas de D&A Systems para mejorar la coherencia de metadatos, el rastreo y la señalización de marca sin cambiar el diseño visual ni el contenido comercial principal.

## Alcance
- Páginas públicas revisadas:
  - `index.html`
  - `servicios-ti.html`
  - `soluciones-ti.html`
  - `nosotros.html`
  - `terminos-condiciones.html`
  - `politica-privacidad.html`
- Archivos de soporte revisados:
  - `robots.txt`
  - `sitemap.xml`
  - `sistema/tools/check-public-site-seo-contract.php`

## Cambios aplicados por página
- `index.html`
  - Ajuste de `og:description` para que coincida con la meta description.
  - Agregado JSON-LD conservador con `ProfessionalService` y `WebSite`.
  - Confirmado `canonical`, `og:url`, `og:image` y favicon actual.
- `servicios-ti.html`
  - Actualizado `title`, `meta description`, `og:title`, `og:description`.
  - Normalizado `favicon` a `assets/img/uploads/favicon-dasystems.png`.
- `soluciones-ti.html`
  - Actualizado `title`, `meta description`, `og:title`, `og:description`.
  - Normalizado favicon.
- `nosotros.html`
  - Actualizado `title`, `meta description`, `og:title`, `og:description`.
  - Normalizado favicon.
- `terminos-condiciones.html`
  - Actualizado `title`, `meta description`, `og:title`, `og:description`.
  - Normalizado favicon.
- `politica-privacidad.html`
  - Actualizado `title`, `meta description`, `og:title`, `og:description`.
  - Normalizado favicon.

## Titles finales
- `index.html`: D&A Systems | Soporte y gestión TI para empresas en Chile
- `servicios-ti.html`: Servicios TI para empresas en Chile | D&A Systems
- `soluciones-ti.html`: Soluciones TI para PYMES | Continuidad, soporte e infraestructura
- `nosotros.html`: D&A Systems | Partner TI externo para PYMES
- `terminos-condiciones.html`: Términos y condiciones | D&A Systems
- `politica-privacidad.html`: Política de privacidad | D&A Systems

## Descriptions finales
- `index.html`: Soporte y gestión TI para PYMES en Chile. Ordenamos equipos, redes, respaldos, inventario de activos y continuidad operativa con documentación y trazabilidad.
- `servicios-ti.html`: Soporte técnico, redes, respaldos, correos corporativos, licenciamiento, inventario TI e informes técnicos para empresas que necesitan operar con más orden y continuidad.
- `soluciones-ti.html`: Soluciones TI para empresas que necesitan ordenar soporte, proteger información, mejorar conectividad, administrar activos y reducir interrupciones operativas.
- `nosotros.html`: Conoce el enfoque de D&A Systems: soporte TI externo, diagnóstico claro, documentación técnica y acompañamiento para empresas que necesitan ordenar su tecnología.
- `terminos-condiciones.html`: Términos y condiciones de uso del sitio web de D&A Systems y de los canales de contacto asociados a sus servicios TI.
- `politica-privacidad.html`: Información sobre el tratamiento de datos enviados a través del formulario de contacto de D&A Systems para responder solicitudes comerciales o técnicas.

## Canonicals finales
- `index.html`: https://www.dasystems.cl/
- `servicios-ti.html`: https://www.dasystems.cl/servicios-ti.html
- `soluciones-ti.html`: https://www.dasystems.cl/soluciones-ti.html
- `nosotros.html`: https://www.dasystems.cl/nosotros.html
- `terminos-condiciones.html`: https://www.dasystems.cl/terminos-condiciones.html
- `politica-privacidad.html`: https://www.dasystems.cl/politica-privacidad.html

## Cambios en Open Graph
- Se normalizaron `og:title`, `og:description`, `og:url` y `og:image` en todas las páginas públicas.
- `og:image` apunta consistentemente a `https://www.dasystems.cl/assets/img/uploads/Logoweb400.png`.

## Cambios en favicon
- Se normalizó el favicon en todas las páginas públicas a `assets/img/uploads/favicon-dasystems.png`.
- Se eliminó la referencia al favicon antiguo con espacio en el nombre.

## Cambios en sitemap.xml
- Se mantuvieron las 6 URL públicas reales.
- Se actualizó `lastmod` a `2026-05-31`.

## Cambios en robots.txt
- Se confirmó que permite rastreo general:
  - `User-agent: *`
  - `Allow: /`
  - `Sitemap: https://www.dasystems.cl/sitemap.xml`

## JSON-LD agregado
- `index.html` incluye un bloque `ProfessionalService` conservador.
- `index.html` incluye un bloque `WebSite` básico.
- No se agregaron webs sociales ni datos de dirección no confirmados.

## Qué se mantuvo intacto
- Diseño visual, estructura, clases CSS y layout.
- Contenido comercial principal del body y el formulario de contacto.
- Funcionalidad de formularios, rutas de scripts y archivos CSS/JS.
- Activos de imágenes existentes fuera del favicon.
- Base de datos, Composer, vendor y archivos backend.

## Pruebas realizadas
- Validación de metadatos en cada página pública.
- Verificación de `robots.txt` y `sitemap.xml`.
- Ejecución del contrato CLI `sistema/tools/check-public-site-seo-contract.php`.
- Verificación de que no quedan referencias a `D&A Systems SpA` en los metadatos de los archivos públicos.

## Próxima etapa sugerida
- 7D.06 — Auditoría de velocidad móvil y mejora de carga de recursos críticos.
