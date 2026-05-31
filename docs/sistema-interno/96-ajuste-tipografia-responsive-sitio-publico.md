# 96 — Ajuste fino de jerarquía tipográfica responsive del sitio público

## 1. Objetivo
Mejorar la legibilidad y equilibrio visual del Hero y títulos principales mediante ajustes CSS/SCSS conservadores y responsive, sin modificar texto, imágenes ni estructura HTML.

## 2. Alcance
- Archivos revisados: `assets/scss/sections/_hero.scss`, `assets/scss/main.scss`, `assets/css/main.css` (si existe).
- Archivos modificados: `assets/scss/sections/_hero.scss` (overrides responsivos para H1).
- No se modificaron HTML, imágenes, JS, formularios, ni recursos externos.

## 3. Problema visual detectado
El `H1` del Hero era demasiado grande en pantallas de notebook/desktop, ocupando varias líneas y dominando la primera pantalla. Se necesita reducir su presencia visual manteniendo peso y jerarquía.

## 4. Archivos revisados
- `assets/scss/sections/_hero.scss`
- `assets/scss/_variables.scss`
- `assets/scss/main.scss`
- `assets/css/main.css` (revisión de existencia)
- `index.html` (verificación de no-modificación)

## 5. Archivos modificados
- `assets/scss/sections/_hero.scss` — se añadieron overrides responsive conservadores para `h1` en puntos de ruptura de desktop, notebook y tablet.

## 6. Selectores ajustados
- `.hero .content h1` — tamaños y `line-height` explícitos por breakpoint:
  - Desktop (>=1200px): ~51px (`3.2rem`)
  - Notebook (992–1199px): ~44px (`2.75rem`)
  - Tablet (768–991px): ~38px (`2.4rem`)
  - Mobile (<576px): mantiene `2rem` (ya definido)
- `.hero .content p` — se mantuvo en `1.125rem`, legible en todos los tamaños.
- `.hero-badge` y `.hero-support-note` — sin cambios funcionales; diseño intacto.

## 7. Criterios responsive aplicados
- H1: desktop 48–52px, notebook 42–46px, tablet 36–40px, mobile 32–36px.
- `line-height` H1: 1.12–1.18 según breakpoint (se aplicó 1.14–1.16 conservador).
- Texto del Hero: 16–18px con `line-height` cómodo (se mantiene `1.7` para el párrafo principal por legibilidad).

## 8. Qué se mantuvo intacto
- HTML de `index.html`.
- Textos y microcopy.
- Imágenes y rutas de assets.
- JavaScript y formularios.
- Colores de marca.

## 9. Pruebas visuales recomendadas
- Revisar en dispositivos/emuladores: 1366×768 (notebook), 1440×900 (desktop), 1024×768 (tablet landscape), 768×1024 (tablet portrait), 375×812 (mobile).
- Verificar que el H1 no ocupe más de 3 líneas en 1366×768.
- Confirmar que la tarjeta flotante `.service-card` no solape contenido crítico en desktop.

## 10. Próxima etapa sugerida
- Afinar espaciado vertical (`padding` del `.content`) según feed de usabilidad.
- Añadir pruebas A/B con variantes tipográficas si se busca mayor legibilidad corporativa.

---
Documento generado como parte de la etapa 7D.03 — ajuste tipográfico responsive.
