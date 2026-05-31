# Diagnóstico e inventario de imágenes del sitio público

## Objetivo
Documentar el inventario actual de imágenes visibles en `index.html`, mapear su uso por sección y proponer criterios de coherencia visual sin modificar ningún activo en esta etapa.

## Alcance
- Solo se analizan las imágenes referenciadas directamente desde `index.html`.
- No se cambian archivos, rutas ni contenidos.
- La auditoría se enfoca en:
  - presencia de imágenes clave
  - asociación con secciones de valor
  - recomendaciones de estilo y nomenclatura futura
  - estado de optimización WebP / íconos de marca.

## Inventario de imágenes detectadas

### Logotipo e identidad de marca
- `assets/img/uploads/Logoweb400.png`
  - Uso: logotipo principal en el header y como `og:image` para redes.
  - Observación: es la imagen de marca principal del sitio.
  - Recomendación: mantener y verificar que la versión final sea limpia, legible y visible sobre fondos claros.

### Iconos de plataforma y dispositivo
- `assets/img/uploads/favicon D&A Systems.png`
  - Uso: favicon del sitio.
  - Recomendación: conservar el favicon actual y, si se reevalúa, generar versiones de 32x32 y 16x16 con nombre más simple para control de caché.
- `assets/img/apple-touch-icon.png`
  - Uso: icono Apple Touch.
  - Recomendación: mantener la coherencia con la identidad del favicon.

### Sección Hero / Servicios TI
- `assets/img/about/about-8.webp`
  - Uso: imagen hero de la primera sección de la página.
  - Rol: transmite especialización en infraestructura y soporte TI.
  - Recomendación: si se reemplaza en el futuro, elegir una imagen corporativa con tecnología, equipo de trabajo o sala de servidores limpia y sobria.

- `assets/img/about/about-square-8.webp`
  - Uso: imagen secundaria en la sección de “Gestión TI orientada a continuidad”.
  - Rol: refuerza el mensaje preventivo y de continuidad TI.
  - Recomendación: conservar si sigue alineada; de lo contrario, optar por un recurso visual que enfatice orden y profesionalismo.

### Sección de soluciones / portafolio
- `assets/img/portfolio/portfolio-3.webp`
  - Uso: solución de continuidad / respaldo.
  - Rol: acompaña el mensaje de protección de información crítica.

- `assets/img/portfolio/portfolio-7.webp`
  - Uso: solución de infraestructura / redes.
  - Rol: refuerza diagnóstico de conectividad y redes.

- `assets/img/portfolio/portfolio-portrait-5.webp`
  - Uso: solución de soporte / mantenimiento.
  - Rol: representa atención a equipos y usuarios.

- `assets/img/portfolio/portfolio-8.webp`
  - Uso: solución de seguridad.
  - Rol: acompaña revisión de accesos y configuraciones.

- `assets/img/portfolio/portfolio-9.webp`
  - Uso: solución de inventario TI.
  - Rol: comunica levantamiento de activos y trazabilidad.

- `assets/img/portfolio/portfolio-11.webp`
  - Uso: solución de administración de servidores.
  - Rol: sugiere recursos críticos y continuidad operativa.

## Mapa de sección y coherencia visual

- Las imágenes actuales se agrupan en tres grandes ejes:
  1. Marca / identidad (`Logoweb400.png`, favicon, Apple Touch)
  2. Mensaje principal TI / continuidad (`about-8.webp`, `about-square-8.webp`)
  3. Casos de uso y soluciones específicas (`portfolio-*.webp`)

- El sitio público busca transmitir:
  - orden, prevención y continuidad tecnológica
  - soporte corporativo práctico y cercano
  - accesos seguros y revisión de infraestructura.

- Coherencia visual recomendada:
  - predominar imágenes limpias, con tonos azules/grises y entornos empresariales.
  - evitar fotografías excesivamente genéricas que parezcan stock sin vinculación al mundo TI.
  - mantener estilo uniforme entre hero y cartera de soluciones.

## Observaciones clave

- `index.html` ya usa WebP en las piezas de contenido principales, lo cual es positivo para rendimiento.
- El logo se encuentra tanto en el header como en metadatos de redes sociales; conviene asegurar consistencia de marca en ambos casos.
- No se detectan imágenes rotas en el inventario presente en `index.html`.

## Recomendaciones de seguimiento

- Revisar los activos `portfolio-*.webp` para asegurar que todas las etiquetas `alt` sean descriptivas y consistentes con el mensaje de cada solución.
- Al reemplazar imágenes en el futuro, usar nombres de archivo más descriptivos y estructurados, por ejemplo:
  - `hero-ti-gestion-01.webp`
  - `servicios-prevencion-01.webp`
  - `portfolio-continuidad-01.webp`
  - `portfolio-infraestructura-01.webp`
  - `portfolio-soporte-01.webp`
  - `portfolio-seguridad-01.webp`
  - `portfolio-inventario-01.webp`

- Mantener la práctica de cargar imágenes clave en formato WebP y confirmar la existencia de versiones de respaldo para navegadores no compatibles si se decide migrar más adelante.

## Criterios de auditoría

- Esta auditoría es diagnóstica: no modifica activos.
- Se valida que las rutas referenciadas en `index.html` existan en el repositorio.
- Se propone coherencia estilística para futuras etapas de reemplazo o mejora visual.
