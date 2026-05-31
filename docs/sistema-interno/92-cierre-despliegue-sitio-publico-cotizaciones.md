# 7B.09 — Cierre técnico y preparación de despliegue del sitio público + módulo de cotizaciones

## Objetivo

Cerrar técnicamente la versión actual del sitio público y el módulo PHP de cotizaciones, documentar el estado de readiness y dejar claro qué se entrega hoy y qué queda fuera de alcance para el futuro portal privado Laravel/React.

## Nueva decisión arquitectónica

Este repositorio queda como:

- Sitio público de D&A Systems basado en BootstrapMade / HTML / SCSS / JS.
- Módulo PHP operativo de cotizaciones dentro de `sistema/`.
- Portal privado futuro separado que se desarrollará fuera de este repositorio con Laravel + React + Inertia + MySQL.

## Estado final del módulo de cotizaciones

El módulo actual entrega las funcionalidades básicas de cotizaciones:

- Login y sesión segura.
- Creación de borradores.
- Edición de borradores.
- Emisión de cotizaciones.
- Asignación de número oficial.
- Detalle de cotización.
- Vista imprimible.
- Descarga de PDF con Dompdf.
- Flujo validado de acciones operativas.

## Qué queda fuera del sistema actual

No se construirá ni elegirá seguir en este repositorio:

- Clientes.
- Inventario TI.
- RRHH.
- Tickets.
- Dashboard avanzado.
- Multiempresa.
- SaaS.
- API JSON.
- React.
- Laravel.

## Checklist previo a producción

Antes de subir a producción, confirmar:

1. `sistema/config/company.php` con datos reales de empresa.
2. Conexión MySQL válida en `sistema/config/database.php`.
3. Usuario administrador disponible.
4. Permisos de carpetas: `storage/`, `sistema/storage/`, `sistema/public/` según cPanel recomendados.
5. Composer/vendor instalados o subidos como artefacto.
6. Dompdf instalado y funcionando.
7. PDF descargables generados correctamente.
8. Sesión/cookies funcionando con `session.cookie_secure` e `HTTPS`.
9. Sitio servido por HTTPS.
10. Backups programados de código y base de datos.
11. No exponer archivos sensibles ni configuraciones públicas.

## Estructura sugerida para despliegue en cPanel

- Dominio principal apunta al sitio público HTML/SCSS/JS.
- El módulo PHP de cotizaciones se ubica en un subdirectorio protegido como `sistema/`.
- `sistema/public/` debe ser la raíz de aplicación PHP si se habilita mediante subdominio o ruta.
- `sistema/config/` y `sistema/app/` no deben estar accesibles directamente desde la web.
- `vendor/` debe estar presente junto a `composer.json` y `composer.lock`.
- Archivos que sí deben subirse:
  - `sistema/` completo.
  - `composer.json` y `composer.lock`.
  - `vendor/` o `composer install` en producción.
  - `sistema/config/company.php` con datos reales.
  - `sistema/config/database.php` con credenciales correctas.
- Archivos que no deberían exponerse públicamente:
  - `sistema/config/*.php` directamente desde web.
  - `sistema/app/*` directamente desde web.
  - `vendor/` si no se controla correctamente el acceso.
  - `docs/` si contiene información sensible.

## Consideraciones de seguridad

- No exponer configuraciones ni datos comerciales en archivos públicos.
- No dejar `storage/` ni `sistema/storage/` sin permisos adecuados.
- Validar HTTPS antes de producción.
- Asegurar que el login y las sesiones se ejecutan sobre HTTPS.
- Respaldar la base de datos antes de cada despliegue.
- Proteger herramientas CLI y no ejecutar archivos de mantenimiento desde la web.

## Comandos de validación local

```bash
php -l sistema/tools/check-public-site-quotes-final-readiness-contract.php
php sistema/tools/check-public-site-quotes-final-readiness-contract.php
```

Comando Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-public-site-quotes-final-readiness-contract.php
```

## Prueba manual final

1. Iniciar sesión.
2. Crear un borrador.
3. Editar un borrador.
4. Emitir cotización.
5. Ver detalle de cotización.
6. Abrir vista imprimible.
7. Descargar PDF.
8. Cerrar sesión.

## Riesgos pendientes antes de producción

- `sistema/config/company.php` aún contiene valores `Pendiente`.
- Debe existir `vendor/autoload.php` o ejecutarse `composer install` en el servidor.
- Validar conexión MySQL y permisos de carpetas.
- Confirmar HTTPS y backups.

## Próximo proyecto recomendado

Portal privado D&A Systems con Laravel + React + Inertia + MySQL + ModernAdmin.
