# Checklist técnico pre-publicación (cPanel) — Etapa 5

Fecha base: 2026-05-17

## 1) Publicación de archivos
- [ ] Subir rama aprobada a `public_html/` o subcarpeta final del dominio.
- [ ] Confirmar estructura esperada:
  - [ ] `index.html`
  - [ ] `servicios-ti.html`
  - [ ] `soluciones-ti.html`
  - [ ] `nosotros.html`
  - [ ] `terminos-condiciones.html`
  - [ ] `politica-privacidad.html`
  - [ ] `forms/contact.php`
  - [ ] `.htaccess`
  - [ ] `robots.txt`
  - [ ] `sitemap.xml`

## 2) Permisos y propietario
- [ ] Archivos en `644`.
- [ ] Directorios en `755`.
- [ ] `forms/` y `assets/` legibles por Apache/PHP-FPM.

## 3) Validación de carga de páginas
- [ ] Home (`/`) responde HTTP 200.
- [ ] `servicios-ti.html` responde HTTP 200.
- [ ] `soluciones-ti.html` responde HTTP 200.
- [ ] `nosotros.html` responde HTTP 200.
- [ ] `terminos-condiciones.html` responde HTTP 200.
- [ ] `politica-privacidad.html` responde HTTP 200.

## 4) HTTPS y redirecciones
- [ ] Certificado TLS activo y vigente en dominio principal.
- [ ] Redirección HTTP -> HTTPS activa.
- [ ] Canonical coincide con versión HTTPS final.
- [ ] HSTS habilitado solo tras 48-72h de estabilidad HTTPS.

## 5) Formulario (flujo funcional)
- [ ] Envío válido desde `index.html` muestra confirmación de éxito.
- [ ] Envío válido desde `servicios-ti.html` muestra confirmación de éxito.
- [ ] Envío sin consentimiento devuelve error controlado.
- [ ] Honeypot (`website`) lleno devuelve respuesta anti-bot esperada.
- [ ] Método GET al endpoint devuelve `405 Método no permitido`.

## 6) Correo saliente y entregabilidad
- [ ] Llega correo a destinatario configurado.
- [ ] Prueba de bandeja principal y spam.
- [ ] SPF válido para el dominio remitente.
- [ ] DKIM válido para el dominio remitente.
- [ ] DMARC publicado (al menos `p=none` inicial).

## 7) Headers de seguridad
- [ ] `X-Content-Type-Options: nosniff` presente.
- [ ] `Referrer-Policy: strict-origin-when-cross-origin` presente.
- [ ] `X-Frame-Options: SAMEORIGIN` presente.
- [ ] `Permissions-Policy` presente.

## 8) SEO técnico final
- [ ] `robots.txt` accesible en `/robots.txt`.
- [ ] `sitemap.xml` accesible en `/sitemap.xml`.
- [ ] Sitemap enviado en Google Search Console.
- [ ] Inspección de URL en GSC para Home y `servicios-ti.html`.

## 9) Monitoreo mínimo
- [ ] Revisar logs de error de Apache/PHP 24h post-deploy.
- [ ] Revisar tasa de errores de formulario y rebotes de correo.
- [ ] Definir canal de alerta operativo (correo interno o chat).

## 10) Criterio de salida
- [ ] 0 errores críticos abiertos.
- [ ] Formularios operativos en ambos puntos de entrada.
- [ ] SEO técnico básico validado y rastreable.
