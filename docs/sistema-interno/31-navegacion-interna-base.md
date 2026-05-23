# Etapa 6D.17 — Navegación interna base

## Objetivo

Agregar una navegación interna simple y reutilizable dentro del layout común del sistema interno, sin desarrollar todavía los módulos completos.

## Archivos modificados o creados

- `sistema/app/Views/layouts/internal.php`
- `docs/sistema-interno/31-navegacion-interna-base.md`

## Qué se agregó

- Una navegación interna base dentro de `internal.php`.
- Un enlace funcional a `dashboard.php`.
- Marcadores visuales para futuras secciones:
  - Cotizaciones
  - Clientes
  - Atenciones
  - Configuración
- Un enlace de cierre de sesión dentro de la navegación, apuntando a `logout.php`.
- Soporte para `$activeNav`, con valor predeterminado `dashboard`, para marcar la sección activa en futuras páginas internas.

## Qué queda pendiente

- Crear las páginas reales de Cotizaciones, Clientes, Atenciones y Configuración.
- Definir navegación activa por página cuando existan nuevos módulos.
- Mover estilos inline de navegación a CSS dedicado si la navegación crece.
- Agregar permisos por rol si el sistema lo requiere en etapas posteriores.

## Qué NO se cambió

- No se cambió la lógica de login.
- No se cambió la lógica de logout.
- No se cambió el timeout de sesión por inactividad.
- No se cambió la autenticación.
- No se cambió la base de datos.
- No se crearon módulos completos ni CRUD.
- No se agregaron frameworks ni dependencias.
- No se rediseñó completamente el panel.

## Flujo resultante

1. Cada página interna sigue preparando su sesión, autenticación y contenido.
2. La página incluye el layout `internal.php`.
3. El layout imprime el header, la navegación interna, el contenido principal y el footer.
4. El enlace `Dashboard` apunta a `dashboard.php`.
5. Los módulos futuros aparecen como marcadores deshabilitados hasta que existan sus páginas reales.
6. El cierre de sesión sigue apuntando a `logout.php`.

## Pruebas recomendadas

1. Ejecutar `php -l sistema/app/Views/layouts/internal.php`.
2. Ejecutar `php -l sistema/public/dashboard.php`.
3. Abrir el dashboard con sesión activa y verificar que la navegación aparece.
4. Confirmar que `Dashboard` se muestra como sección activa.
5. Confirmar que `Cerrar sesión` apunta a `logout.php`.
6. Confirmar que los módulos futuros no intentan abrir páginas inexistentes.
