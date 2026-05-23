# Etapa 6D.19 — Helper de página interna protegida

## Objetivo

Crear una forma simple y reutilizable para renderizar páginas internas protegidas, reduciendo duplicación sin cambiar el comportamiento funcional existente.

## Helper creado

- `sistema/app/Support/InternalPage.php`

El helper `InternalPage::render()` centraliza el flujo común de páginas internas:

- inicia sesión con `SessionManager`
- exige autenticación con `AuthGuard`
- obtiene el nombre del usuario autenticado
- recibe título de página
- recibe heading
- recibe navegación activa
- recibe un callback de contenido
- ejecuta el callback después de autenticar al usuario
- carga el layout interno existente

## Archivos modificados

- `sistema/public/dashboard.php`
- `sistema/public/cotizaciones.php`
- `sistema/public/clientes.php`
- `sistema/public/atenciones.php`
- `sistema/public/configuracion.php`

## Qué se simplificó

- Las páginas internas ya no repiten la carga directa de `SessionManager` y `AuthGuard`.
- Las páginas internas ya no repiten el inicio de sesión ni `requireAuth('login.php')`.
- Las páginas internas ya no repiten la obtención de `$userName`.
- Cada página conserva solo su contenido específico dentro de un callback y llama al helper con sus datos de presentación.
- El contenido específico se genera después de iniciar sesión y exigir autenticación.

## Qué NO se cambió

- No se cambió login.
- No se cambió logout.
- No se cambió el timeout de sesión.
- No se modificó `AuthGuard`.
- No se modificó `SessionManager`.
- No se cambió la base de datos.
- No se agregó CRUD.
- No se crearon formularios.
- No se crearon controllers.
- No se agregaron frameworks ni dependencias.
- No se cambiaron rutas públicas.
- No se rediseñó el panel.
- No se movieron estilos inline a CSS.
- No se alteraron los textos placeholder.

## Pruebas recomendadas

1. Ejecutar `php -l sistema/public/dashboard.php`.
2. Ejecutar `php -l sistema/public/cotizaciones.php`.
3. Ejecutar `php -l sistema/public/clientes.php`.
4. Ejecutar `php -l sistema/public/atenciones.php`.
5. Ejecutar `php -l sistema/public/configuracion.php`.
6. Ejecutar `php -l sistema/app/Views/layouts/internal.php`.
7. Ejecutar `php -l sistema/app/Support/InternalPage.php`.
8. Acceder a cada página con sesión activa y verificar que mantiene el mismo contenido visible.
9. Acceder a cada página sin sesión y confirmar que redirige a `login.php` antes de generar contenido específico.
10. Verificar que la navegación activa sigue funcionando.

## Preparación para próximas etapas

Esta etapa deja un punto único para el renderizado base de páginas internas protegidas. Las próximas páginas o módulos podrán reutilizar el mismo helper sin copiar la lógica de sesión, autenticación y layout.
