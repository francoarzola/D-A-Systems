# Auditoría Integral Final — Etapa 5F
## D&A Systems

---

## Resumen Ejecutivo

El sitio corporativo de D&A Systems ha implementado mejoras significativas en seguridad, privacidad, navegación y publicación. Se han aplicado protecciones contra CSRF, rate limiting, logging seguro, externalización de configuración sensible, y mejora de la navegación. El sitio se encuentra en un estado funcional y listo para publicación en hosting cPanel **con observaciones menores**.

---

## Estado General del Sitio

**Categoría:** Sitio corporativo B2B (landing + servicios internos)  
**Archivos principales:** index.html, servicios-ti.html, soluciones-ti.html, nosotros.html, términos-condiciones.html, política-privacidad.html  
**Lenguaje:** HTML5, CSS3, JavaScript (vanilla), PHP 7.3+  
**Formularios:** 2 formularios activos (index.html, servicios-ti.html)  
**Configuración:** Externalizada a `config/contact.php`

---

## Tabla de Hallazgos por Prioridad

### Crítico
Ninguno identificado.

### Alto
| Hallazgo | Ubicación | Descripción | Impacto | Recomendación |
|----------|-----------|-------------|--------|--------------|
| HSTS no activado | `.htaccess` | HSTS está comentado | Exposición a downgrade en HTTPS | Activar solo después de validar HTTPS estable en cPanel |

### Medio
| Hallazgo | Ubicación | Descripción | Impacto | Recomendación |
|----------|-----------|-------------|--------|--------------|
| Dependencias vendorizadas | `assets/vendor/php-email-form/` | PHPMailer 3.11 embebida sin gestor de paquetes | Falta de actualizaciones automáticas | Monitorizarlas regularmente; considerar Composer en etapas posteriores |
| Validación de formulario solo client-side en algunas secciones | `validate.js` | validate.js maneja errores sin sobreescribir backend | Seguridad adecuada pero UI depende de JS | Funcional; mantener como está |

### Bajo
| Hallazgo | Ubicación | Descripción | Impacto | Recomendación |
|----------|-----------|-------------|--------|--------------|
| Analytics o tracking ausente | N/A | No se detectan píxeles de GA, FB, etc. | No hay medición de conversión | Agregar Google Analytics o similar si es necesario para negocio |
| Robots.txt simple | `robots.txt` | Solo `Allow: /` y sitemap | Funcional pero sin restricciones adicionales | Suficiente para MVP |
| Sitemap sin prioridades | `sitemap.xml` | Contiene todas las páginas pero sin `priority` | SEO neutro | Considerar agregar prioridades en etapas posteriores |

---

## Checklist de Navegación

- [x] Header en index.html con enlaces a: #hero, #areas-criticas, #servicios-ti, #about, #soluciones-ti, #faq, #contact
- [x] Footer en index.html con enlaces a: index.html (internos), nosotros.html, terminos-condiciones.html, politica-privacidad.html
- [x] Footer en servicios-ti.html con mismos enlaces normalizados
- [x] Footer en soluciones-ti.html con mismos enlaces normalizados
- [x] Footer en nosotros.html con enlaces consistentes
- [x] Footer en terminos-condiciones.html con enlaces consistentes
- [x] Footer en politica-privacidad.html con enlaces consistentes
- [x] CTA "Conocer más sobre D&A Systems" en sección #about apunta a nosotros.html
- [x] Enlaces interno a nosotros.html accesibles desde todos los footers
- [x] No se detectan enlaces rotos (rutas relativas correctas)

---

## Checklist de Formularios

### Formulario en index.html
- [x] `action="forms/contact.php"`
- [x] `method="post"`
- [x] Campo `csrf_token` presente con clase `csrf-token-field`
- [x] Campo `privacy_consent` presente (requerido)
- [x] Honeypot `website` presente (aria-hidden)
- [x] Campos obligatorios: name, email, subject, message
- [x] Campos opcionales: phone, company
- [x] Labels para todos los inputs (id + label)
- [x] Estados: loading, error-message, sent-message presentes
- [x] Script de inyección de CSRF token al final del body
- [x] Compatible con validate.js

### Formulario en servicios-ti.html
- [x] `action="forms/contact.php"`
- [x] `method="post"`
- [x] Campo `csrf_token` presente con clase `csrf-token-field`
- [x] Campo `privacy_consent` presente (requerido)
- [x] Honeypot `website` presente (aria-hidden)
- [x] Subject pre-lleno con valor descriptivo
- [x] Campos obligatorios: name, email, message
- [x] Estados: loading, error-message, sent-message presentes
- [x] Script de inyección de CSRF token presente en page
- [x] Compatible con validate.js

---

## Checklist de Seguridad en forms/contact.php

- [x] Configuración desde `config/contact.php` con `getenv('DA_SYSTEMS_RECEIVING_EMAIL')`
- [x] Validación de destinatario con `filter_var(..., FILTER_VALIDATE_EMAIL)`
- [x] Validación del método POST (rechaza GET, otros con 405)
- [x] Rate limiting: 5 intentos / 15 minutos por IP (SHA-256)
- [x] Logging JSON Lines en `storage/logs/contact.log`
- [x] CSRF validation: `hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])`
- [x] Sanitización: `strip_tags()` + `trim()`
- [x] Header injection mitigation: reemplazo de `\r`, `\n`, `%0a`, `%0d`
- [x] Límites de longitud: name (100), email (150), phone (30), company (150), subject (150), message (3000)
- [x] Validación de email del remitente: `filter_var(..., FILTER_VALIDATE_EMAIL)`
- [x] No registro de PII en logs (solo timestamp, event, ip_hash, method, status, reason, user_agent_hash)
- [x] No registro de csrf_token en logs
- [x] Mensajes de error controlados (sin stack trace)
- [x] Códigos HTTP apropiados (400, 405, 429, 500)

---

## Checklist de Seguridad en forms/csrf-token.php

- [x] Solo acepta método GET (rechaza POST, etc. con 405)
- [x] Responde JSON: `{ "csrf_token": "<token>" }`
- [x] Headers de cache-control: `no-store, no-cache, must-revalidate, max-age=0`
- [x] Session name: `DA_SYSTEMS_SESSION` (único)
- [x] Cookie httponly: `true`
- [x] Cookie SameSite: `Lax`
- [x] Cookie secure: `true` solo si HTTPS detectado
- [x] Cookie path: `/`
- [x] Token generado con `random_bytes(32)` → hex
- [x] No se exponen rutas internas
- [x] Funciona con PHP 7.3+ (fallback para versiones antiguas)

---

## Checklist de Privacidad y Cumplimiento

- [x] Política de privacidad enlazada desde formularios (index.html, servicios-ti.html)
- [x] Consentimiento explícito requerido (`privacy_consent` checkbox)
- [x] Texto de consentimiento claro: "Acepto que D&A Systems trate mis datos..."
- [x] Enlace a política desde formularios (funciona)
- [x] No se guardan datos personales en logs operacionales
- [x] Tokens CSRF no se registran
- [x] Consistencia entre formularios y política

---

## Checklist de SEO Técnico

- [x] Title distintivo en cada página
- [x] Meta description en cada página
- [x] Canonical en cada página apuntando a `https://www.dasystems.cl/...`
- [x] Open Graph tags: og:title, og:description, og:type, og:url, og:image
- [x] robots.txt presente y referencia sitemap.xml
- [x] sitemap.xml presente con todas las páginas principales
- [x] URLs consistentes con `dasystems.cl`
- [x] Favicon presente (referencias en head)
- [x] lang="es" en html
- [x] Charset UTF-8 en meta

**Observación:** Considerar agregar `priority` en sitemap.xml en etapas posteriores.

---

## Checklist de .htaccess y Hardening

- [x] Header `X-Content-Type-Options: nosniff`
- [x] Header `Referrer-Policy: strict-origin-when-cross-origin`
- [x] Header `X-Frame-Options: SAMEORIGIN` (protege contra clickjacking)
- [x] Header `Permissions-Policy` con restricciones de API
- [x] HSTS comentado (correcto, pendiente activación en producción)

**Riesgos:**
- HSTS desactivado: no obliga HTTPS pero es seguro mientras se valida.
- No se detecta protección contra ListDir aunque generalmente cPanel lo maneja.

---

## Checklist de Accesibilidad Básica

- [x] Labels vinculados a inputs (id + label for)
- [x] Alt text en logo y imágenes principales
- [x] Aria-label en CTA principal
- [x] Aria-label en WhatsApp
- [x] Aria-hidden en honeypot
- [x] Navegación con estructura clara
- [x] Formularios con instrucciones visibles
- [x] Contraste visual aparentemente adecuado (Bootstrap estándar)
- [x] Soporte para navegación móvil (Bootstrap responsive)

**Recomendación:** Realizar test de accesibilidad con herramientas automatizadas (Lighthouse, Axe) antes de producción.

---

## Checklist de cPanel / Publicación

- [x] Permisos necesarios: `storage/logs` y `storage/rate-limit` requieren permisos de escritura para usuario PHP (755 en directorios, 644 en archivos)
- [x] PHP sessions: compatible con PHP 7.3+ (fallback en constructor)
- [x] Envío de correo: usa PHPMailer, requiere configuración SMTP o sendmail en cPanel
- [x] Escritura de logs: creadirectories automáticamente con `mkdir()`
- [x] No listado de directorios: asumido por configuración cPanel estándar
- [x] HTTPS: requerido para `secure=true` en cookies (debe estar activo)
- [x] Rutas relativas: todas correctas (assets/, forms/, etc.)
- [x] config/contact.php: debe existir en producción con `returning_email_address`

---

## Checklist de Consistencia Corporativa

- [x] Lenguaje profesional y claro
- [x] Evitar textos genéricos de plantilla (customizado a D&A Systems)
- [x] Coherencia de CTA: "Solicitar diagnóstico", "Enviar solicitud", etc.
- [x] Coherencia de entidad: "D&A Systems" (mayúsculas correctas)
- [x] Correo consistente: `contacto@dasystems.cl`
- [x] Teléfono consistente: `+56 9 7300 0457`
- [x] WhatsApp activo y con URL compatible

---

## Riesgos Pendientes

1. **CSRF token básico:** Depende de sesiones PHP. No reemplaza CAPTCHA en caso de spam volumétrico.
2. **Rate limit local:** No distribuido. Si hay múltiples instancias, cada una tendrá su propio contador.
3. **PHPMailer embebido:** Versión 3.11 sin gestor de paquetes. Requiere monitoreo manual.
4. **Logging local:** Archivo único `contact.log` crece indefinidamente. Considerar rotación.
5. **Dependencias vendorizadas:** Bootstrap, AOS, Swiper, etc. sin gestor de paquetes.
6. **Sin CAPTCHA:** Vulnerable a bots sofisticados si spam aumenta.
7. **Sin WAF:** Confía en validaciones client + server.

---

## Recomendaciones Antes de Publicación en cPanel

1. **Validar HTTPS:** Asegurar que el certificado SSL esté activo en cPanel antes de ir a producción.
2. **Crear config/contact.php:** Puede contener variable de entorno `DA_SYSTEMS_RECEIVING_EMAIL` o valor hardcodeado.
3. **Permisos de directorios:** Ejecutar en cPanel:
   ```bash
   chmod 755 storage/logs storage/rate-limit
   chmod 644 storage/logs/.gitkeep storage/rate-limit/.gitkeep
   ```
4. **Configurar SMTP o sendmail:** En cPanel, asegurar que PHP puede enviar correos (mail.php o SMTP configurado).
5. **Probar formularios localmente:** Enviar pruebas antes de publicar.
6. **Revisar logs:** Acceder a `storage/logs/contact.log` y verificar estructura JSON Lines.
7. **Activar robots.txt y sitemap.xml:** Indexar en Google Search Console después del deploy.

---

## Recomendaciones Post-Publicación

1. **Monitoreo de logs:** Revisar `storage/logs/contact.log` regularmente para patrones anómales.
2. **Rate limit monitoring:** Si se detectan múltiples bloqueos, considerar aumentar `$max_attempts` o ventana.
3. **Rotación de logs:** Implementar cron job para archivar/purgar logs antiguos.
4. **Análisis de conversión:** Agregar Google Analytics para medir clics en "Nosotros" vs "Solicitar diagnóstico".
5. **Feedback de formularios:** Monitorear mensajes de error para identificar problemas UX.
6. **CAPTCHA si spam:** Si aparece spam real, integrar reCAPTCHA v3 o Turnstile.
7. **HSTS en producción:** Después de validar HTTPS estable por 30+ días, descomentar HSTS en .htaccess.

---

## Veredicto

**Estado: LISTO PARA PUBLICACIÓN CON OBSERVACIONES**

### Criterios cumplidos:
✅ Formularios seguros (CSRF, rate limit, sanitización)  
✅ Logging seguro (no PII, JSON Lines)  
✅ Navegación clara y consistente  
✅ SEO técnico básico correcto  
✅ Accesibilidad fundamentales cubiertas  
✅ Privacidad y cumplimiento adecuados  
✅ Código funcional y sin errores visibles  

### Observaciones:
⚠️ HSTS comentado (pendiente activación en producción)  
⚠️ Dependencias vendorizadas requieren monitoreo  
⚠️ Logging local sin rotación automática  
⚠️ Sin CAPTCHA (aceptable para MVP, agregar si spam aumenta)  

### Paso siguiente:
1. Desplegar a cPanel
2. Validar funcionalidad de formularios
3. Revisar logs post-publicación
4. Implementar monitoreo de conversión
