# Etapa 6D.23 — Diseño visual estático de Nueva cotización

## Objetivo

Diseñar una maqueta estática de la pantalla "Nueva cotización" para validar visualmente campos, secciones y flujo de captura antes de implementar formularios funcionales, base de datos, CRUD o persistencia real.

## Qué se diseñó visualmente

- Sección "Nueva cotización" dentro de `sistema/public/cotizaciones.php`.
- Bloques visuales para datos generales, cliente y contacto.
- Tabla estática de ítems por cotizar.
- Resumen visual de totales.
- Bloque de condiciones comerciales.
- Botones visuales no funcionales:
  - Guardar borrador
  - Emitir cotización
  - Cancelar
- Nota explícita de que los campos y botones son solo maqueta.

## Componentes de la maqueta

### Datos generales

Incluye número, fecha y validez. El número se muestra como "Se asignará al emitir", alineado con la decisión técnica de que `quote_number` puede ser `NULL` mientras la cotización está en borrador.

### Datos del cliente

Incluye razón social, RUT y descripción general, usando datos ficticios.

### Datos de contacto

Incluye nombre, correo y teléfono de ejemplo.

### Ítems

Muestra una tabla estática con líneas de ejemplo para revisar columnas, cantidades, unidades, precios y total línea.

### Resumen de totales

Muestra subtotal, descuento, IVA y total como valores ficticios. No existe cálculo real.

### Botones visuales

Los botones son elementos visuales sin acción. No son formularios, no tienen `method`, no tienen `action` y no ejecutan lógica.

## Qué NO se implementó

- No se creó formulario funcional.
- No se usó `method` ni `action`.
- No se guardan datos.
- No se creó base de datos.
- No se creó SQL.
- No se crearon migraciones.
- No se creó CRUD.
- No se crearon controllers, repositories ni models.
- No se implementaron cálculos reales.
- No se implementó PDF.
- No se implementó envío por correo.
- No se modificó `InternalPage`.
- No se modificaron login, logout, timeout, `AuthGuard` ni `SessionManager`.
- No se agregó lógica de negocio.

## Riesgos o decisiones pendientes

- Definir el orden final de secciones para crear una cotización real.
- Definir qué campos serán obligatorios en la primera versión funcional.
- Definir cómo se agregarán y eliminarán ítems cuando exista formulario real.
- Definir si los botones serán acciones separadas o parte de un mismo flujo.
- Mover estilos inline a CSS dedicado cuando el diseño deje de ser maqueta.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar la estructura técnica de los formularios de cotización, todavía sin persistencia real, para preparar validaciones, nombres de campos y flujo de envío antes de implementar CRUD.
