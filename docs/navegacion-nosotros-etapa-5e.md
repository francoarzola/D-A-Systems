# Etapa 5E: Acceso claro a la página `nosotros.html`

## Objetivo
Añadir accesos claros y discretos a la página `nosotros.html` sin recargar el menú principal del header, priorizando la conversión desde la landing.

## Decisión UX
No se añadió un elemento más en el header para mantener la navegación orientada a la conversión. Se optó por ofrecer rutas naturales desde la sección "Por qué elegirnos" y desde los footers de las páginas para mantener consistencia y descubribilidad.

## Cambios realizados
- `index.html`
  - Se añadió un CTA discreto en la sección `#about`:
    - Texto: "Conocer más sobre D&A Systems"
    - Enlace: `nosotros.html`
  - En el footer (columna de Navegación) se agregó:
    - `Nosotros` → `nosotros.html`

- Páginas con footer actualizadas (se agregó el mismo enlace si faltaba):
  - `servicios-ti.html`
  - `soluciones-ti.html`
  - `nosotros.html`
  - `terminos-condiciones.html`
  - `politica-privacidad.html`

## Recomendación futura
Medir el uso del nuevo enlace durante algunas semanas. Si la navegación a "Nosotros" resulta frecuente, considerar agregarlo en el header o revaluar el orden de elementos (por ejemplo, mantener "Áreas críticas" si aporta conversión).

## Archivos modificados
- index.html
- servicios-ti.html
- soluciones-ti.html
- nosotros.html
- terminos-condiciones.html
- politica-privacidad.html

## Verificación
Ejecutar `git diff --name-only` y comprobar que sólo aparezcan los archivos listados en "Archivos modificados".

