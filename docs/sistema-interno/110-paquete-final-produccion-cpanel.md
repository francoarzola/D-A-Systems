# 7F.11 - Paquete final de produccion para cPanel

## Fecha de generacion

Generado el 2026-06-02.

## Estado de validaciones

El paquete `build/cpanel-production` fue regenerado desde `main` con working tree limpio antes de iniciar la tarea.

La validacion principal esperada es:

```powershell
$php = "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe"
& $php sistema/tools/check-cpanel-production-package-contract.php
```

## Que contiene el paquete

El paquete incluye los archivos necesarios para despliegue en cPanel:

- paginas HTML publicas
- `sitemap.xml`
- `robots.txt`
- `composer.json`
- `composer.lock`
- `vendor/`
- `assets/`
- `forms/`
- `config/`
- `sistema/config/`
- `sistema/public/`

Tambien incluye las imagenes WebP optimizadas:

- `assets/img/about/about-8.webp`
- `assets/img/about/about-square-8.webp`
- `assets/img/portfolio/portfolio-3.webp`
- `assets/img/portfolio/portfolio-7.webp`
- `assets/img/portfolio/portfolio-8.webp`
- `assets/img/portfolio/portfolio-9.webp`
- `assets/img/portfolio/portfolio-11.webp`
- `assets/img/portfolio/portfolio-portrait-5.webp`

## Que se excluye

El paquete no incluye:

- `.git/`
- `.github/`
- `docs/`
- `sistema/tools/`
- `node_modules/`
- `build/` interno
- archivos `.env`
- archivos `.log`
- backups
- temporales
- `assets/scss/`

## Advertencias pendientes

El contrato de preparacion para cPanel puede advertir que `sistema/config/company.php` contiene valores `Pendiente`. Esa advertencia no bloquea la generacion del paquete, pero debe resolverse antes de usar datos comerciales definitivos.

## Confirmaciones

`build/` no se versiona y queda registrado en `.gitignore`.

Laravel no fue tocado. No se modifico base de datos, formulario PHP, `config/contact.php`, contenido editorial, imagenes fuente, CSS/SCSS/JS, sitemap, robots ni paginas HTML publicas.
