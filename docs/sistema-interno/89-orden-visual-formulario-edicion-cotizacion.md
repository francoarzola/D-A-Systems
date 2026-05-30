# Etapa 7B.06 — Orden visual del formulario de edición de cotización

## Objetivo

Mejorar la jerarquía visual de `cotizacion-editar.php` para facilitar la edición de borradores, agrupando campos por intención y dejando las acciones visibles.

## Problema detectado

El formulario de edición era funcional pero carecía de una agrupación visual clara y las acciones importantes no estaban siempre visibles.

## Qué se ordenó en `cotizacion-editar.php`

- Añadido encabezado operativo: `Editar cotización` con texto contextual.
- Resumen superior con: estado, cliente, fecha, validez y total.
- Acciones principales (`Guardar cambios`, `Ver detalle`, `Volver al listado`) visibles al inicio del formulario.
- Formulario organizado en secciones: `Datos generales`, `Cliente`, `Contacto`, `Detalle de línea`, `Observaciones`.
- Se mantuvieron todos los campos, nombres de inputs e hiddens.

## Jerarquía visual resultante

1. Topbar interna (existente)
2. Encabezado operativo
3. Resumen superior
4. Acciones principales (Guardar, Ver detalle, Volver)
5. Mensajes de error/flash
6. Formulario agrupado por secciones
7. Botón de guardar al final también

## Qué se mantuvo funcionalmente intacto

- Formulario POST y `action` original (`cotizacion-actualizar.php`).
- CSRF token y campos ocultos (`cotizacion_id`, `form_action`).
- Valores repoblados y manejo de errores.
- Validaciones existentes y recalculo en servidor.
- No se alteraron servicios/repositorios ni lógica de negocio.

## Cambios realizados en `internal.css`

- Añadidas clases para soportar la jerarquía del formulario de edición: `quote-edit-heading`, `quote-edit-intro`, `quote-edit-summary`, `quote-edit-summary-item`, `quote-edit-actions`, `quote-edit-form`.

## Decisiones visuales

- Mantener estilo sobrio y coherente con el resto del módulo.
- Mostrar acciones al inicio para reducir scroll y facilitar flujo de edición.

## Riesgos controlados

- No se cambió la lógica de guardado ni los endpoints; sólo presentación.
- No se añadieron scripts ni dependencias.

## Herramienta CLI de verificación

- `sistema/tools/check-quote-edit-visual-order-contract.php` valida la presencia de elementos clave y asegura que no se introdujeron llamadas/procedimientos no permitidos.

### Cómo ejecutar

```
php sistema/tools/check-quote-edit-visual-order-contract.php
```

(Ver instrucción de Laragon en la tarea original.)

## Pruebas recomendadas (manuales)

1. Iniciar sesión.
2. Abrir `cotizacion-editar.php?id=<borrador>`.
3. Confirmar encabezado operativo y resumen superior.
4. Modificar un campo y guardar; verificar que los cambios se guardan y aparecen en el detalle.
5. Confirmar que no es posible editar una cotización emitida (si regla existente).
6. Ejecutar sintaxis PHP:

```
php -l sistema/public/cotizacion-editar.php
php -l sistema/tools/check-quote-edit-visual-order-contract.php
```

## Próxima etapa recomendada

- 7B.07 — Consolidación visual final del módulo de cotizaciones.
