# 99 — Preparación Final para Despliegue en cPanel

**Etapa:** 7E.01 — Preparación final del sitio público D&A Systems para despliegue en hosting cPanel

**Fecha:** 2026-05-31

**Estado:** ✅ LISTO PARA DESPLIEGUE

---

## 1. Resumen de Estado General

El sitio público D&A Systems está completamente auditorizado y listo para despliegue en hosting cPanel. Se han validado:

- ✅ Páginas HTML públicas (6 archivos)
- ✅ Configuración de SEO técnico
- ✅ Sitemap.xml y robots.txt
- ✅ Formulario de contacto con protecciones
- ✅ Módulo de cotizaciones (funcional)
- ✅ Codificación UTF-8 sin BOM
- ✅ Ausencia de "SpA" en todo contenido público
- ✅ Favicon correcto
- ✅ Configuración SMTP por variables de entorno
- ✅ No hay datos sensibles hardcodeados

---

## 2. Páginas Públicas Incluidas

El sitio público consta de **6 páginas HTML estáticas** que se deben desplegar:

| Página | Ruta | Estado |
|--------|------|--------|
| Inicio | index.html | ✅ UTF-8, metadata OK, favicon OK |
| Servicios TI | servicios-ti.html | ✅ UTF-8, metadata OK, favicon OK |
| Soluciones TI | soluciones-ti.html | ✅ UTF-8, metadata OK, favicon OK |
| Nosotros | nosotros.html | ✅ UTF-8, metadata OK, favicon OK |
| Términos y Condiciones | terminos-condiciones.html | ✅ UTF-8, metadata OK, favicon OK |
| Política de Privacidad | politica-privacidad.html | ✅ UTF-8, metadata OK, favicon OK |

**Todas las páginas incluyen:**
- `<meta charset="utf-8">` (sin BOM)
- `<meta name="description">` (155-160 caracteres)
- `<link rel="canonical">` (URL canónica correcta)
- `<meta property="og:...">` (Open Graph tags completos)
- `<link href="assets/img/uploads/favicon-dasystems.png" rel="icon">`
- Formulario de contacto con:
  - CSRF token (`forms/csrf-token.php`)
  - Campo honeypot (`website`)
  - Rate limiting
  - Privacy consent checkbox

---

## 3. Archivos/Carpetas a Subir a cPanel

### Carpetas principales (SUBIR):
```
/
├── index.html
├── servicios-ti.html
├── soluciones-ti.html
├── nosotros.html
├── terminos-condiciones.html
├── politica-privacidad.html
├── robots.txt
├── sitemap.xml
├── composer.json
├── composer.lock
├── .htaccess (si existe, para rewrite rules)
├── assets/
│   ├── css/
│   ├── js/
│   ├── img/
│   │   ├── about/
│   │   ├── bg/
│   │   ├── portfolio/
│   │   ├── services/
│   │   └── uploads/
│   │       ├── favicon-dasystems.png (CRÍTICO)
│   │       ├── Logoweb400.png
│   │       └── (todas las imágenes necesarias)
│   └── vendor/ (Bootstrap, AOS, GLightBox, Swiper, etc.)
├── forms/
│   ├── contact.php
│   └── csrf-token.php
├── config/
│   └── contact.php
├── sistema/
│   ├── config/
│   │   ├── company.php
│   │   └── database.php (si es necesario en producción)
│   ├── public/
│   │   ├── login.php
│   │   ├── cotizaciones.php
│   │   ├── cotizacion-detalle.php
│   │   ├── cotizacion-editar.php
│   │   ├── cotizacion-actualizar.php
│   │   ├── cotizacion-emitir.php
│   │   ├── cotizacion-imprimir.php
│   │   ├── cotizacion-pdf.php
│   │   └── cotizacion-guardar.php
│   ├── app/
│   │   ├── Services/
│   │   ├── Support/
│   │   ├── Traits/
│   │   └── Controllers/ (si existe)
│   ├── database/ (migraciones y seeders)
│   └── storage/ (permisos writable)
└── vendor/autoload.php (CRÍTICO: debe existir)
```

### Carpetas/Archivos que NO se deben subir (EXCLUIR):

```
.git/                              (control de versiones)
.gitignore
.github/                           (workflows, issues, etc.)
.vscode/                           (configuración local de editor)
docs/                              (documentación técnica interna)
docs/sistema-interno/              (auditorías internas, no pública)
.env.local                         (credenciales locales)
.env.example                       (plantilla, no necesaria)
*.md                               (archivos de documentación)
README.md                          (no es público)
package.json / package-lock.json   (Node, si existe)
node_modules/                      (dependencias Node)
.DS_Store                          (archivos del sistema)
Thumbs.db                          (archivos del sistema)
sistema/tools/check-*.php          (contratos internos de QA)
storage/logs/                      (logs de desarrollo local)
storage/rate-limit/                (caché local)
tests/                             (tests unitarios)
laragon/                           (entorno local)
```

---

## 4. Variables de Entorno Necesarias en cPanel

Para que el formulario de contacto funcione en producción, configura estas variables de entorno en cPanel (a través de `.env` o UI):

```bash
# Correo de recepción de contactos
DA_SYSTEMS_RECEIVING_EMAIL=contacto@dasystems.cl

# Configuración SMTP (ejemplo: Gmail, Mailgun, SendGrid, etc.)
DA_SYSTEMS_SMTP_HOST=smtp.gmail.com
DA_SYSTEMS_SMTP_PORT=587
DA_SYSTEMS_SMTP_ENCRYPTION=tls
DA_SYSTEMS_SMTP_USERNAME=tu-email@gmail.com
DA_SYSTEMS_SMTP_PASSWORD=tu-app-password  # NO hardcodear en repo
DA_SYSTEMS_SMTP_MAILER=smtp
```

**⚠️ IMPORTANTE:** Los valores de SMTP nunca deben estar en el repositorio. Deben configurarse en cPanel después del despliegue.

---

## 5. Permisos y Carpetas Writable en cPanel

Configura estos permisos **después de desplegar** en cPanel:

```bash
chmod 755 /                        # Raíz pública
chmod 755 /assets/                 # Estáticos públicos
chmod 755 /sistema/                # Sistema (si es público)
chmod 755 /sistema/storage/        # Para logs y cache
chmod 755 /storage/                # Si existe, para logs
chmod 755 /vendor/                 # Dependencias
chmod 644 *.html                   # Archivos HTML
chmod 644 robots.txt
chmod 644 sitemap.xml
chmod 755 forms/                   # Si la carpeta existe y necesita ejecución
chmod 755 config/                  # Si la carpeta existe
```

---

## 6. Checklist de Formulario de Contacto en Producción

Después de desplegar a cPanel, prueba el formulario:

- [ ] Navega a `https://www.dasystems.cl/#contact` (desde cualquier página)
- [ ] Completa el formulario con:
  - Email válido
  - Nombre
  - Asunto
  - Mensaje
- [ ] Deja vacío el campo "website" (honeypot)
- [ ] Marca el checkbox de consentimiento de privacidad
- [ ] Haz clic en "Enviar"
- [ ] Confirma que:
  - [ ] El formulario se envía sin errores
  - [ ] El correo llega a `DA_SYSTEMS_RECEIVING_EMAIL`
  - [ ] El correo contiene datos completos del mensaje
  - [ ] No hay errores 500, 403, etc.
- [ ] Intenta enviar spam rápidamente (para probar rate limit):
  - [ ] Debe rechazarse después de ~5 intentos en 1 minuto
  - [ ] Mensaje amigable: "Por favor espera antes de enviar otro mensaje"

---

## 7. Checklist de Módulo de Cotizaciones en Producción

Si el hosting permite acceso interno a `/sistema/public/`:

- [ ] Accede a `https://www.dasystems.cl/sistema/public/login.php`
- [ ] Inicia sesión con credenciales configuradas localmente
- [ ] Verifica:
  - [ ] Listado de cotizaciones carga correctamente
  - [ ] Botón "Crear borrador" funciona
  - [ ] Puedes editar un borrador
  - [ ] La vista de detalle muestra PDF, imprimir y editar
  - [ ] Datos comerciales (empresa) se ven correctamente
  - [ ] No hay errores de base de datos
  
**Nota:** El acceso a `/sistema/public/` puede estar restringido a IP interna o requiere configuración especial en cPanel.

---

## 8. Checklist de Seguridad Básica Post-Despliegue

- [ ] HTTPS habilitado en cPanel (Let's Encrypt gratuito)
- [ ] Redirección HTTP → HTTPS configurada en `.htaccess`
- [ ] Headers de seguridad en `.htaccess`:
  ```apache
  <IfModule mod_headers.c>
    Header set X-Content-Type-Options nosniff
    Header set X-Frame-Options SAMEORIGIN
    Header set X-XSS-Protection "1; mode=block"
  </IfModule>
  ```
- [ ] Archivos sensibles no accesibles:
  - [ ] `config/` no es navegable
  - [ ] `docs/` no es navegable
  - [ ] `.env` no es accesible (si existe)
- [ ] Base de datos protegida con firewall de cPanel
- [ ] Logs de PHP configurados para no exponerse públicamente
- [ ] Backups automáticos habilitados en cPanel
- [ ] SFTP/SSH habilitado para actualizaciones futuras

---

## 9. Checklist de SEO Post-Despliegue

- [ ] Envía `sitemap.xml` a Google Search Console:
  - [ ] Accede a: https://search.google.com/search-console
  - [ ] Agrega propiedad: `https://www.dasystems.cl/`
  - [ ] Navega a Sitemaps
  - [ ] URL: `https://www.dasystems.cl/sitemap.xml`
- [ ] Envía `robots.txt` a Google Search Console
- [ ] Espera 48-72 horas para que Google indexe páginas
- [ ] Verifica índice en Search Console:
  - [ ] Todas las 6 páginas deben estar indexadas
  - [ ] Sin errores críticos de indexación
- [ ] Verifica en Google que el sitio aparezca:
  - [ ] `site:dasystems.cl` devuelve 6+ resultados
  - [ ] Snippets muestran descriptions correctas

---

## 10. Datos Pendientes a Completar

El archivo `sistema/config/company.php` aún tiene valores **Pendiente** que deben completarse antes de usar la facturación/cotizaciones:

```php
'tax_id' => 'Pendiente',              // RUT o NIF de la empresa
'address' => 'Pendiente',             // Dirección física
'phone' => 'Pendiente',               // Teléfono de contacto
'website' => 'Pendiente',             // URL del sitio (debe ser https://www.dasystems.cl)
'default_payment_terms' => 'Pendiente', // Términos de pago (ej: Neto 30 días)
```

**Acción:** Completar estos valores antes de emitir cotizaciones oficiales.

---

## 11. Riesgos y Limitaciones Conocidas

### ⚠️ Imágenes Pendientes de Reemplazo
Las imágenes del sitio son placeholders BootstrapMade. En una etapa posterior (7E.02 - Etapa de branding), se reemplazarán con imágenes profesionales de D&A Systems.

**Impacto:** Bajo. El sitio es funcional y cumple objetivos de comunicación. La identidad visual completa vendrá después.

### ⚠️ Módulo de Cotizaciones Requiere Acceso Protegido
Actualmente, `/sistema/public/` es accesible públicamente si está en el mismo hosting. Se recomienda:

**Opción 1:** Proteger con `.htaccess` + Basic Auth
```apache
<DirectoryMatch "^/sistema">
    AuthType Basic
    AuthName "Area Restringida"
    AuthUserFile /home/user/.htpasswd
    Require valid-user
</DirectoryMatch>
```

**Opción 2:** Mover a subdominio privado o hosting separado

**Opción 3:** Aceptar que esté disponible pero documentado (actual)

### ⚠️ Base de Datos
No se modifica en esta etapa. Asegúrate de que exista y tenga permisos correctos en cPanel.

### ⚠️ Vendor/Composer
Si `vendor/` no se sube al hosting, **debes** ejecutar `composer install` en producción o usar `--no-dev` para minimizar dependencias.

---

## 12. Próxima Etapa Recomendada

**7E.02 — Despliegue Controlado en cPanel**

- [ ] Crear cuenta de hosting en cPanel
- [ ] Configurar dominio `dasystems.cl`
- [ ] Subir archivos via SFTP/Git
- [ ] Configurar variables de entorno
- [ ] Probar formulario y cotizaciones
- [ ] Validar SEO en Search Console
- [ ] Hacer hard launch

---

## 13. Validaciones Completadas

Ejecuta este comando para validar la preparación:

```bash
php sistema/tools/check-cpanel-deployment-readiness-contract.php
```

**Resultado esperado:**
```
[OK] Sitio público listo para preparación de despliegue en cPanel.
```

---

## 14. Archivos Críticos a Verificar Antes de Subir

| Archivo | Propósito | Obligatorio |
|---------|-----------|-------------|
| `index.html` | Página de inicio | ✅ SÍ |
| `servicios-ti.html` | Página de servicios | ✅ SÍ |
| `soluciones-ti.html` | Página de soluciones | ✅ SÍ |
| `nosotros.html` | Página about | ✅ SÍ |
| `terminos-condiciones.html` | Legales | ✅ SÍ |
| `politica-privacidad.html` | Privacidad | ✅ SÍ |
| `robots.txt` | SEO | ✅ SÍ |
| `sitemap.xml` | SEO | ✅ SÍ |
| `assets/img/uploads/favicon-dasystems.png` | Favicon | ✅ SÍ |
| `forms/contact.php` | Formulario | ✅ SÍ |
| `config/contact.php` | Config formulario | ✅ SÍ |
| `composer.json` | Dependencias | ✅ SÍ |
| `vendor/autoload.php` | Autoloader | ✅ SÍ |
| `sistema/config/company.php` | Datos comerciales | ✅ SÍ |
| `.htaccess` | Rewrite rules (si existe) | ⚠️ REVISAR |

---

## 15. Contacto y Soporte

Para preguntas sobre este despliegue:

- **Documentación:** Revisar `docs/sistema-interno/`
- **Contratos de validación:** `sistema/tools/check-*.php`
- **Errores:** Consultar logs de cPanel o ejecutar contratos de validación

---

**Documento generado:** 2026-05-31
**Responsable:** Auditor Técnico Senior
**Estado:** ✅ APROBADO PARA DESPLIEGUE
