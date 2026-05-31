# 100 — Paquete de Producción para cPanel

**Etapa:** 7E.02 — Crear paquete limpio de producción para subir a cPanel

**Fecha:** 2026-05-31

**Estado:** En construcción

---

## 1. Objetivo

Crear un paquete limpio de producción que contenga solo los archivos necesarios para el sitio público D&A Systems en cPanel. El paquete debe excluir documentación interna, herramientas de auditoría, archivos de Git, configuraciones locales y elementos de desarrollo.

---

## 2. Carpeta de build

El paquete se generó en:

```
build/cpanel-production/
```

Esta carpeta contiene únicamente los archivos y carpetas necesarios para despliegue.

---

## 3. Qué se incluyó en el paquete

### Archivos públicos
- index.html
- servicios-ti.html
- soluciones-ti.html
- nosotros.html
- terminos-condiciones.html
- politica-privacidad.html
- robots.txt
- sitemap.xml

### Carpetas y configuraciones
- assets/
- forms/
- config/
- sistema/app/
- sistema/config/
- sistema/public/
- sistema/database/ (si está presente)
- sistema/storage/ (estructura vacía mínima)
- composer.json
- composer.lock
- vendor/ (dependencias necesarias)

### Contenido verificado
- `assets/img/uploads/favicon-dasystems.png`
- `forms/contact.php`
- `config/contact.php`
- `composer.json`
- `sitemap.xml`
- `robots.txt`

---

## 4. Qué se excluyó del paquete

- .git/
- .github/
- .gitignore
- .vscode/
- docs/
- sistema/tools/
- README.md
- archivos .md internos
- package.json
- package-lock.json
- node_modules/
- .env
- .env.local
- .env.example
- storage/logs/
- storage/rate-limit/
- archivos temporales
- backups
- archivos de pruebas
- screenshots

---

## 5. Contrato CLI de validación

Se creó el contrato:

```
sistema/tools/check-cpanel-production-package-contract.php
```

Este script valida que `build/cpanel-production/` esté correctamente armado y limpio.

---

## 6. Validaciones realizadas

- Verificación de existencia de las 6 páginas públicas.
- Inclusión de `assets/`, `forms/`, `config/`, `sistema/public/`.
- Inclusión de `robots.txt` y `sitemap.xml`.
- Inclusión de favicon correcto.
- Exclusión de `docs/`, `sistema/tools/`, `.git`, `.github`, `.vscode`, `.env`, `.env.local`, y `README.md`.
- Verificación de ausencia de mojibake y `SpA` en HTML.
- Validación de `forms/contact.php`, `config/contact.php` y `composer.json`.
- Advertencia si falta `vendor/autoload.php`.
- Advertencia si `sistema/config/company.php` contiene "Pendiente".

---

## 7. Resultados esperados

Al ejecutar el contrato CLI, el paquete debe terminar con:

```
[OK] Paquete de producción cPanel validado correctamente.
```

---

## 8. Siguiente paso recomendado

**7E.03 — Subida controlada a cPanel y pruebas en producción**

- Subir `build/cpanel-production/` a cPanel mediante SFTP o Git
- Configurar variables de entorno SMTP
- Probar formulario de contacto
- Probar módulo de cotizaciones
- Verificar que el sitio cargue con HTTPS
- Confirmar SEO, robots.txt y sitemap

---

## 9. Notas

Este paquete se creó sin comprimir. No se generó ZIP en esta etapa, porque primero se valida la carpeta.
