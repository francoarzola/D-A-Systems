# 7B.07 — Consolidación visual final del módulo de cotizaciones

## Objetivo

Unificar y consolidar visualmente las pantallas del módulo de cotizaciones (`cotizaciones.php`, `cotizacion-detalle.php`, `cotizacion-editar.php`) para que compartan patrones coherentes de encabezados, resúmenes, acciones, tarjetas, tablas y formularios sin alterar la lógica funcional.

## Pantallas revisadas

- `cotizaciones.php` (Listado)
- `cotizacion-detalle.php` (Detalle)
- `cotizacion-editar.php` (Edición de borrador)

## Estado visual final del módulo

Las tres pantallas comparten:
- `internal-topbar` con marca y navegación interna.
- Encabezados operativos consistentes (page heading / quotes-page-heading).
- Resumen superior en forma de tarjetas (`quote-detail-summary`, `quote-edit-summary`, `quotes-summary-grid`).
- Barra de acciones (`quote-detail-actions`, `quote-edit-actions`, `quote-actions`) con orden y estilos homogéneos.
- Tarjetas (`card`) para bloques secundarios y `quote-nested-card` para sub-secciones.
- Tablas legibles (`quote-table`, `quote-table-list`, `quote-table-compact`).
- Formularios agrupados con `grid` y campos con `quote-label` / `quote-input`.

## Patrones visuales consolidados

- Navegación interna: `internal-topbar`, `internal-nav`, `internal-nav-link`, `internal-nav-link-active`.
- Encabezados operativos: `quotes-page-heading`, `quote-detail-heading`, `quote-edit-heading`.
- Resumen superior: `quote-detail-summary`, `quote-edit-summary`, `quotes-summary-grid`, `quote-*-summary-item`.
- Acciones principales: `quote-detail-actions`, `quote-edit-actions`, `quote-actions`, `quote-action-*`.
- Tarjetas: `card`, `quote-nested-card`, `summary-card` (alias addicional para compatibilidad).
- Formularios: `form-grid`, `quote-input`, `quote-field`, `form-error-summary`.
- Mensajes flash y estado: `flash-message-*`, `status-panel`.
- Botones: `button-primary`, `quote-action-primary`, `quote-action-strong`, `quote-action-muted`.

## Cambios realizados en `internal.css`

- Añadidas alias y pequeñas reglas de normalización para `summary-card`, `summary-item`, `action-bar`, `form-grid`, `table-section` y clases de botón (`btn-primary`, `btn-muted`).
- Ajustes de espaciado para `card h2` y consistencia de `quote-*` helpers.
- No se eliminaron ni renombraron clases existentes; cambios son aditivos y conservadores.

## Qué se revisó en cada pantalla

- `cotizaciones.php`: revisado encabezado, resumen, listado y sección de creación de borrador (`id="crear-borrador"`, `Crear borrador`, `method="post"`, `csrf` present).
- `cotizacion-detalle.php`: revisado encabezado operativo, resumen superior, acciones (Editar, Emitir, Vista imprimible, Descargar PDF), enlaces a `cotizacion-pdf.php?id=`, `cotizacion-imprimir.php?id=`, y preservación de `csrf` y estados (`borrador`, `emitida`).
- `cotizacion-editar.php`: revisado encabezado operativo, resumen superior, acciones principales al inicio del formulario, preservación de `method="post"`, `csrf`, `type="submit"`, `cotizacion-actualizar.php` y enlaces de navegación.

## Decisiones visuales tomadas

- Priorizar la consistencia y la mínima superficie de cambio.
- Añadir alias CSS en vez de reemplazar nombres existentes para reducir riesgo.
- Mantener los colores y escalas tipográficas del sistema interno existente.

## Qué se mantuvo funcionalmente intacto

- Listado real y paginación leída desde base de datos.
- Creación de borrador (`cotizaciones-guardar.php`).
- Edición de borrador (`cotizacion-actualizar.php`).
- Emisión (`cotizacion-emitir.php`).
- Vista imprimible y descarga de PDF (`cotizacion-imprimir.php`, `cotizacion-pdf.php`).
- CSRF y nombres de campos.
- Rutas, servicios y repositorios.
- Base de datos y números oficiales de cotización.

## Riesgos controlados

- No se cambiaron endpoints ni la lógica de negocio.
- Cambios CSS son aditivos; en caso de conflicto previo, las clases originales permanecen.

## Qué NO se implementó

- No se rediseñó completamente la interfaz.
- No se añadieron scripts ni endpoints.
- No se cambiaron servicios, repositorios ni base de datos.
- No se implementó AJAX ni API JSON.

## Herramienta CLI creada

- `sistema/tools/check-quotes-visual-consolidation-contract.php` — valida presencia de patrones, enlaces, tokens CSRF y ausencia de llamadas/funciones prohibidas.

### Cómo ejecutar

```
php sistema/tools/check-quotes-visual-consolidation-contract.php
```

Comando Laragon:

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quotes-visual-consolidation-contract.php
```

## Prueba manual recomendada

1. Iniciar sesión.
2. Abrir `cotizaciones.php`.
3. Abrir `cotizacion-detalle.php?id=<id>` (una cotización emitida y una en borrador).
4. Abrir `cotizacion-editar.php?id=<borrador>`.
5. Confirmar que encabezados, resumenes y barras de acción son consistentes entre pantallas.
6. Confirmar que crear/editar/emitir/descarga PDF/vista imprimible funcionan.

## Próxima etapa recomendada

- 7B.08 — Revisión de textos, acentos y microcopy del módulo de cotizaciones.
- O alternativamente 8A.01 — Inicio del módulo de clientes.

***

## Comprobaciones sugeridas tras los cambios

```
php -l sistema/public/cotizaciones.php
php -l sistema/public/cotizacion-detalle.php
php -l sistema/public/cotizacion-editar.php
php -l sistema/tools/check-quotes-visual-consolidation-contract.php
php sistema/tools/check-quotes-visual-consolidation-contract.php
```

Y los comandos git habituales:

```
git status
git diff --check
```
