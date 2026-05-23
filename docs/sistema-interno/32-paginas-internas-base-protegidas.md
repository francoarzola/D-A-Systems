# Etapa 6D.18 — Páginas internas base protegidas

## Objetivo

Crear páginas internas base protegidas para módulos futuros, sin desarrollar funcionalidades CRUD ni lógica de negocio.

## Archivos creados o modificados

- `sistema/public/cotizaciones.php`
- `sistema/public/clientes.php`
- `sistema/public/atenciones.php`
- `sistema/public/configuracion.php`
- `sistema/app/Views/layouts/internal.php`
- `docs/sistema-interno/32-paginas-internas-base-protegidas.md`

## Qué se agregó

- Páginas base protegidas para los futuros módulos internos:
  - Cotizaciones
  - Clientes
  - Atenciones
  - Configuración
- Cada página:
  - carga `SessionManager`
  - carga `AuthGuard`
  - inicia sesión
  - exige autenticación con `requireAuth('login.php')`
  - obtiene el usuario autenticado
  - define `$pageTitle`, `$pageHeading`, `$userName` y `$activeNav`
  - captura contenido con `ob_start()`
  - reutiliza `sistema/app/Views/layouts/internal.php`
- La navegación del layout ahora enlaza a las páginas base reales:
  - `cotizaciones.php`
  - `clientes.php`
  - `atenciones.php`
  - `configuracion.php`

## Qué NO se cambió

- No se creó CRUD.
- No se crearon formularios.
- No se crearon tablas de base de datos.
- No se consultó la base de datos.
- No se modificó login.
- No se modificó logout.
- No se modificó el timeout de sesión.
- No se modificó `AuthGuard`.
- No se modificó `SessionManager`.
- No se agregaron roles ni permisos.
- No se agregaron frameworks ni dependencias.
- No se movieron estilos inline a CSS.
- No se creó lógica de negocio ni controllers.

## Flujo resultante

1. El usuario ingresa a una página interna.
2. La página inicia sesión con `SessionManager`.
3. La página exige autenticación con `AuthGuard`.
4. Si no hay sesión válida, se redirige a `login.php`.
5. Si la sesión está activa, se renderiza el contenido placeholder dentro del layout interno.
6. El layout marca la sección activa mediante `$activeNav`.

## Pruebas recomendadas

1. Ejecutar `php -l sistema/public/dashboard.php`.
2. Ejecutar `php -l sistema/public/cotizaciones.php`.
3. Ejecutar `php -l sistema/public/clientes.php`.
4. Ejecutar `php -l sistema/public/atenciones.php`.
5. Ejecutar `php -l sistema/public/configuracion.php`.
6. Ejecutar `php -l sistema/app/Views/layouts/internal.php`.
7. Acceder a cada página con sesión activa y verificar que usa el layout común.
8. Acceder a cada página sin sesión y confirmar que redirige a `login.php`.
9. Verificar que `Cerrar sesión` sigue apuntando a `logout.php`.

## Preparación para la siguiente etapa

Esta etapa deja rutas internas reales y protegidas para que las próximas fases puedan implementar módulos específicos sin volver a resolver estructura, layout ni protección base.
