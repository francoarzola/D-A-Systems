# 97 — Reemplazo controlado de imágenes Hero y About del sitio público

## 1. Objetivo
Asegurar que las imágenes principales del Hero y de la sección About/"Cómo trabajamos" sean coherentes con una empresa B2B de servicios TI, manteniendo una estética corporativa, tecnológica, sobria y profesional.

## 2. Alcance
- Revisar `index.html` para identificar las imágenes del Hero y About.
- Confirmar rutas actuales.
- Verificar si ya existen imágenes nuevas en `assets/img/about/` que puedan reemplazar las actuales.
- No modificar layout ni textos.
- No descargar ni agregar nuevas imágenes desde red.

## 3. Imágenes revisadas
- `assets/img/about/about-8.webp` (Hero)
- `assets/img/about/about-square-8.webp` (About)

## 4. Rutas anteriores
- `assets/img/about/about-8.webp`
- `assets/img/about/about-square-8.webp`

## 5. Rutas nuevas
- No se realizó ningún cambio de ruta.
- No se encontraron nuevas imágenes disponibles en `assets/img/about/` al momento de esta etapa.

## 6. Criterio visual aplicado
- Priorizar imágenes de soporte TI corporativo y administración tecnológica.
- Evitar estilos gamer, hackers con capucha, código verde, oficinas irreales o temas ajenos a TI.
- Mantener compatibilidad con la paleta azul/azul marino/celeste/gris/blanco del sitio.
- Conservar formato WebP y atributos `loading`, `fetchpriority` y `decoding` existentes.

## 7. Alt text usado
- Hero: "Especialista TI administrando infraestructura tecnológica empresarial"
- About: "Gestión y soporte TI para empresas"

## 8. Qué se mantuvo intacto
- `index.html` en estructura y textos.
- `assets/css/main.css` y `assets/scss/`.
- JavaScript y formularios.
- Imágenes existentes en otros directorios.
- Base de datos, Composer, vendor.

## 9. Pruebas realizadas
- Verificación de existencia de `assets/img/about/about-8.webp` y `assets/img/about/about-square-8.webp`.
- Validación de que las rutas actuales no contienen espacios ni caracteres problemáticos.
- Confirmación de la presencia de los campos del formulario de contacto en `index.html`.
- Creación de un contrato CLI para ejecutar estas comprobaciones de forma reproducible.

## 10. Próxima etapa sugerida
- 7D.04B — Reemplazo de imágenes de Soluciones.
