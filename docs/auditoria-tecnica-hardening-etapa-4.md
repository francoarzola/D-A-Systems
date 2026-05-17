# Auditoría técnica hardening — Etapa 4 (D&A Systems)

## Alcance
Revisión estática (sin modificar código) de:
- `index.html`
- `servicios-ti.html`
- `soluciones-ti.html`
- `nosotros.html`
- `terminos-condiciones.html`
- `politica-privacidad.html`
- `forms/contact.php`
- `.htaccess` (si existe)
- `assets/css/main.css`
- `assets/js/main.js`

## Resumen ejecutivo
El sitio está funcional y bien encaminado en estructura comercial, pero **aún no está listo para publicación robusta** por hallazgos en seguridad básica, backend de formulario y SEO técnico.

### Hallazgos de mayor impacto
1. **Crítico:** `forms/contact.php` mantiene destinatario placeholder `contact@example.com` y configuración plantilla no endurecida.
2. **Alto:** persisten placeholders de contacto en varias secciones visibles (`mailto:contacto@tudominio.cl`, `wa.me/569XXXXXXXX`, `+56 9 XXXX XXXX`).
3. **Alto:** no existe `.htaccess` con headers base de seguridad ni controles de exposición básicos.
4. **Alto:** metadatos SEO esenciales vacíos (`meta description`) y ausencia de canonical/OG.
5. **Medio:** formulario carece de controles anti-spam/anti-abuso (honeypot/rate limit/challenge) y no incorpora consentimiento explícito de tratamiento de datos.

## Priorización
- **Crítico:** correo receptor del formulario en placeholder.
- **Alto:** placeholders de contacto visibles/activos; ausencia de hardening web base; `meta description` vacía.
- **Medio:** anti-abuso de formulario, canonical/OG, ausencia de `robots.txt` y `sitemap.xml`, consentimiento explícito de privacidad.
- **Bajo:** `meta keywords` vacío y ajustes finos de rendimiento/fuentes.

## Recomendaciones concretas

### Antes de publicar
1. Corregir datos de contacto en `href` y texto visible.
2. Configurar `forms/contact.php` con destinatario real + validaciones backend estrictas.
3. Agregar `.htaccess` con headers de seguridad base (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`/`frame-ancestors`, `Permissions-Policy`; `HSTS` solo con HTTPS correcto).
4. Completar `meta description` por página.

### Después de publicar
1. Agregar canonical y Open Graph.
2. Crear `robots.txt` y `sitemap.xml`.
3. Implementar anti-spam más robusto y monitoreo de logs de formulario.
4. Revisar carga condicional de vendors JS/CSS.

## Evidencia de revisión
- `forms/contact.php`: destinatario placeholder `contact@example.com`, dependencia librería plantilla.
- `index.html` y páginas internas: placeholders de contacto aún presentes en algunos `mailto`/WhatsApp/teléfono.
- `.htaccess`: no existe.
- SEO: `meta description` vacía y ausencia de canonical/OG en páginas auditadas.
