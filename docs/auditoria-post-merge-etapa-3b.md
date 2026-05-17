# Auditoría post-merge Etapa 3B — D&A Systems

## Contexto
- Rama de auditoría: `etapa-3b-auditoria-post-merge`.
- Alcance revisado:
  - `index.html`
  - `servicios-ti.html`
  - `soluciones-ti.html`
  - `nosotros.html`
  - `terminos-condiciones.html`
  - `politica-privacidad.html`

## Resultado general
La auditoría detectó **errores reales de placeholders visibles en datos de contacto** (correo/WhatsApp/teléfono), los cuales fueron corregidos.
No se detectaron restos de texto de plantilla ni `href="#"` al cierre de la revisión.

## Revisión por archivo
### 1) `index.html`
- OK: IDs esperados, CTA principal correcto, clases críticas conservadas, sin `href="#"`.
- Problemas: `contacto@tudominio.cl`, `+56 9 XXXX XXXX`, `wa.me/569XXXXXXXX`.
- Corrección: normalizado a `contacto@dasystems.cl`, `+56 9 7300 0457`, `https://wa.me/56973000457`.

### 2) `servicios-ti.html`
- OK: navegación interna a `index.html#...`, clase `service-details`, sin textos de plantilla.
- Problemas: placeholders de correo/teléfono/WhatsApp.
- Corrección: normalización de contacto.

### 3) `soluciones-ti.html`
- OK: navegación interna a `index.html#...`, clase `portfolio-details`, sin textos de plantilla.
- Problemas: placeholders de correo/teléfono/WhatsApp.
- Corrección: normalización de contacto.

### 4) `nosotros.html`
- OK: navegación interna a `index.html#...`, clase `starter-section`, sin textos de plantilla.
- Problemas: placeholders de correo/teléfono/WhatsApp.
- Corrección: normalización de contacto.

### 5) `terminos-condiciones.html`
- OK: título con `D&amp;A`, clase `terms-of-service`, sin `[Ingresar ...]`.
- Problemas: placeholders de contacto en footer.
- Corrección: normalización de contacto.

### 6) `politica-privacidad.html`
- OK: título con `D&amp;A`, clase `privacy-2`, sin `[Ingresar ...]`.
- Problemas: placeholders de contacto en footer.
- Corrección: normalización de contacto.

## Recomendaciones
1. Validación automática en CI para detectar placeholders.
2. Centralizar datos de contacto para evitar inconsistencias.
3. Checklist de publicación con búsqueda de `XXXX`, `tudominio`, `[Ingresar ...]`.
4. Auditoría semántica periódica de copy legal/comercial.
