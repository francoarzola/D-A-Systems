# UI base del sistema interno

## Objetivo de esta etapa

Crear una interfaz visual base estática para el sistema interno privado de D&A Systems, sin implementar autenticación funcional, lógica de negocio ni conexión a base de datos. Esta pantalla sirve como evidencia visual de un dashboard inicial y define el estilo corporativo para los módulos futuros.

## Archivos creados/modificados

- `sistema/public/index.php`
- `sistema/public/assets/css/internal.css`
- `docs/sistema-interno/11-ui-base-sistema-interno.md`

## Decisión de no implementar login aún

Esta etapa es una maquetación estática. No se implementa login real, no se inicia sesión, no se valida usuarios y no se gestiona base de datos. El propósito es definir la experiencia visual inicial sin introducir lógica de seguridad o negocio.

## Módulos representados visualmente

Se incluyeron tarjetas visuales para:
- Cotizaciones
- Clientes
- Atenciones técnicas

También se incluyó un bloque de estado que indica claramente que el módulo es privado y está en preparación.

## Criterios de seguridad respetados

- No se expone ninguna ruta interna ni archivo sensible.
- No se hace uso de recursos compartidos con el sitio público.
- Los estilos se cargan desde `sistema/public/assets/css/internal.css`.
- La pantalla usa lenguaje español y comunica que el acceso es solo para personal autorizado.
- El botón visual es deshabilitado y no ejecuta acción real.

## Próximos pasos

- **Etapa 6C.3: implementación de autenticación base**
  - autenticar usuarios,
  - manejar sesiones seguras,
  - proteger accesos.
- **Etapa 6D: base de datos**
  - definir tablas, migraciones y repositorios.
- **Etapa 6E: clientes**
  - crear CRUD y búsqueda de clientes.
- **Etapa 6F: cotizaciones**
  - habilitar creación, edición y emisión de cotizaciones.
