# Etapa 6D.22 — Diseño visual estático del listado y detalle de cotizaciones

## Objetivo de la etapa

Diseñar una vista estática inicial del módulo Cotizaciones para validar visualmente el flujo de usuario antes de implementar base de datos, CRUD, formularios funcionales o persistencia real.

## Qué se diseñó visualmente

- Encabezado informativo del módulo.
- Resumen visual de estados.
- Listado estático de cotizaciones ficticias.
- Bloque de detalle de una cotización de ejemplo.
- Tabla estática de ítems de ejemplo.
- Nota explícita indicando que no hay conexión a base de datos ni acciones reales.

## Componentes de la maqueta

### Resumen de estados

Se agregaron tarjetas para representar una lectura rápida de estados:

- Borrador.
- Enviadas.
- Aceptadas.

Los conteos son de ejemplo y no provienen de datos reales.

### Listado

La tabla de listado muestra columnas sugeridas:

- número
- cliente
- fecha
- validez
- estado
- total
- acción visual no funcional

La columna de acción usa texto estático para representar una futura entrada al detalle, sin enlace ni ejecución real.

### Detalle

El bloque de detalle muestra una cotización ficticia con:

- número
- cliente
- contacto
- estado
- condiciones comerciales
- resumen de subtotal, IVA y total

### Ítems

La tabla de ítems muestra líneas ficticias con:

- descripción
- cantidad
- unidad
- precio unitario
- total línea

## Datos de ejemplo

Todos los datos visibles en `sistema/public/cotizaciones.php` son ficticios y están incluidos solo para validar diseño, lectura y flujo visual. No representan registros reales ni están conectados a una base de datos.

## Qué NO se implementó

- No se creó base de datos.
- No se crearon tablas SQL.
- No se crearon migraciones.
- No se creó CRUD.
- No se crearon formularios funcionales.
- No se crearon botones con acciones reales.
- No se crearon controllers, repositories ni models.
- No se modificó `InternalPage`.
- No se implementó cálculo real de totales.
- No se implementó PDF.
- No se implementó envío por correo.
- No se implementó lógica de negocio.

## Riesgos o decisiones pendientes

- Definir si el listado final tendrá filtros por estado, cliente o fecha.
- Definir si el detalle aparecerá en una página separada o en una vista lateral.
- Definir cómo se mostrarán estados, totales y acciones cuando existan permisos por rol.
- Definir formato final de montos, fechas y numeración.
- Mover estilos inline a CSS dedicado cuando el diseño deje de ser maqueta.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar la estructura visual de la pantalla "Nueva cotización", todavía sin formulario funcional ni persistencia, para validar campos, secciones y flujo de captura antes de crear CRUD.
