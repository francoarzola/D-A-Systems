# Etapa 6D.16 — Layout interno reutilizable

## Objetivo

Separar la estructura HTML repetible del dashboard en un layout interno común, sin cambiar la lógica funcional existente del sistema interno.

## Archivos modificados o creados

- `sistema/public/dashboard.php`
- `sistema/app/Views/layouts/internal.php`
- `docs/sistema-interno/30-layout-interno-reutilizable.md`

## Qué se refactorizó

- El `dashboard.php` mantiene la lógica de sesión, autenticación y obtención del usuario autenticado.
- El HTML repetible se movió al layout `internal.php`:
  - `<!DOCTYPE html>`
  - `<html lang="es">`
  - `<head>`
  - carga de `assets/css/internal.css`
  - estructura general del `<body>`
  - header interno básico
  - contenedor principal
  - enlace de cierre de sesión
  - footer interno básico
- El contenido principal del dashboard quedó en `dashboard.php` y se entrega al layout mediante la variable `$content`.
- El layout recibe variables simples:
  - `$pageTitle`
  - `$pageHeading`
  - `$userName`
  - `$content`

## Qué NO se cambió

- No se cambió la lógica de login.
- No se cambió la lógica de logout.
- No se cambió el timeout de sesión por inactividad.
- No se cambiaron validaciones de seguridad.
- No se cambiaron rutas públicas existentes.
- No se agregó navegación lateral ni menú complejo.
- No se agregaron nuevas funcionalidades.
- No se rediseñó visualmente el dashboard.
- No se agregaron dependencias nuevas ni frameworks.

## Flujo resultante

1. `dashboard.php` carga `SessionManager` y `AuthGuard`.
2. `dashboard.php` inicia sesión y exige autenticación con `requireAuth('login.php')`.
3. `AuthGuard` mantiene la protección de acceso y el timeout por inactividad.
4. `dashboard.php` define los datos de presentación de la página.
5. `dashboard.php` captura su contenido principal en `$content`.
6. `dashboard.php` incluye `sistema/app/Views/layouts/internal.php`.
7. El layout imprime la estructura HTML común y el contenido principal recibido.

## Preparación para 6D.17

Esta etapa deja un punto central para incorporar la navegación interna base en la siguiente fase. En 6D.17 se podrá agregar navegación al layout una sola vez, sin repetir menús en cada página privada.

## Pruebas recomendadas

1. Ejecutar `php -l sistema/public/dashboard.php`.
2. Ejecutar `php -l sistema/app/Views/layouts/internal.php`.
3. Ingresar al dashboard con sesión activa y verificar que se ve igual que antes.
4. Verificar que el nombre del usuario autenticado sigue apareciendo.
5. Verificar que el botón `Cerrar sesión` sigue apuntando a `logout.php`.
6. Acceder al dashboard sin sesión y confirmar que sigue redirigiendo a `login.php`.
