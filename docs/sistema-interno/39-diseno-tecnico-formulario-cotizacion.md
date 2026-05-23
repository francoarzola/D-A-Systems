# Etapa 6D.25 — Diseño técnico de formulario de cotización

## Objetivo del formulario

Definir la estructura técnica futura del formulario de cotización, alineada con la maqueta actual de `sistema/public/cotizaciones.php` y con el modelo conceptual documentado en la etapa 6D.21.

Esta etapa no convierte la maqueta en un formulario funcional. Solo deja definidos nombres de campos, estructura esperada del `POST`, validaciones futuras, acciones previstas y reglas de seguridad para una implementación posterior.

## Acciones futuras del formulario

El formulario real deberá soportar tres acciones principales:

- `guardar_borrador`: guardar una cotización editable con estado `borrador`.
- `emitir_cotizacion`: validar la cotización completa, asignar número correlativo y pasarla a estado `emitida`.
- `cancelar`: volver al listado o abandonar la captura sin guardar cambios nuevos.

La acción deberá enviarse como un campo explícito, por ejemplo `form_action`, para evitar depender del texto visible de botones.

## Estructura futura del formulario

La pantalla "Nueva cotización" debería convertirse más adelante en un formulario protegido por sesión, con secciones equivalentes a la maqueta:

1. Datos generales de la cotización.
2. Datos del cliente.
3. Datos de contacto.
4. Ítems o servicios cotizados.
5. Condiciones comerciales y observaciones.
6. Resumen de totales calculado en backend.
7. Acciones del formulario.

El formulario real deberá usar `method="post"` y un destino interno definido en la etapa de implementación. Ese `action` no se define en esta etapa para evitar crear flujo funcional antes de tiempo.

## Campos propuestos para cabecera

| Campo | Uso futuro | Observaciones |
| --- | --- | --- |
| `quote_date` | Fecha de la cotización. | Obligatoria. Debe recibirse como fecha válida. |
| `valid_until` | Fecha de validez comercial. | No debe ser anterior a `quote_date`. |
| `client_name` | Nombre o razón social del cliente. | Obligatorio para emitir. Puede ser regla mínima también para borrador. |
| `client_rut` | RUT del cliente. | Opcional hasta definir integración con Clientes. |
| `contact_name` | Nombre de contacto. | Opcional en borrador; recomendable al emitir. |
| `contact_email` | Correo de contacto. | Validar formato si se informa. |
| `contact_phone` | Teléfono de contacto. | Opcional. |
| `description` | Descripción general de la cotización. | Opcional, útil para contexto comercial. |
| `commercial_terms` | Condiciones comerciales. | Opcional, puede incluir validez, garantías o plazos. |
| `notes` | Observaciones. | Pendiente definir si serán internas, visibles al cliente o ambas. |

## Campos propuestos para ítems

Los ítems deberán enviarse como una lista indexada:

- `items[index][description]`
- `items[index][quantity]`
- `items[index][unit]`
- `items[index][unit_price_net]`
- `items[index][discount_amount]`

Ejemplo de índices esperados:

- `items[0][description]`
- `items[0][quantity]`
- `items[0][unit]`
- `items[0][unit_price_net]`
- `items[0][discount_amount]`

El backend deberá normalizar el orden y asignar `line_number` según la posición válida de cada ítem.

## Campos calculados no confiables desde frontend

Los siguientes campos no deberían aceptarse como fuente de verdad desde el navegador:

- `line_subtotal_net`
- `line_total_net`
- `subtotal_net`
- `tax_amount`
- `total_amount`

La interfaz podrá mostrarlos como vista previa, pero el backend deberá recalcularlos siempre antes de guardar o emitir.

## Estructura esperada de POST

Ejemplo conceptual:

```php
[
    'form_action' => 'guardar_borrador',
    'quote_date' => '2026-05-23',
    'valid_until' => '2026-06-22',
    'client_name' => 'Cliente de ejemplo SpA',
    'client_rut' => '76.000.000-0',
    'contact_name' => 'Andrea Pérez',
    'contact_email' => 'andrea@example.test',
    'contact_phone' => '+56 9 0000 0000',
    'description' => 'Servicios TI para operación interna',
    'commercial_terms' => 'Validez 30 días.',
    'notes' => 'Observación interna de ejemplo.',
    'items' => [
        [
            'description' => 'Mesa de ayuda mensual',
            'quantity' => '1',
            'unit' => 'mes',
            'unit_price_net' => '480000',
            'discount_amount' => '0',
        ],
        [
            'description' => 'Configuración inicial',
            'quantity' => '1',
            'unit' => 'servicio',
            'unit_price_net' => '320000',
            'discount_amount' => '0',
        ],
    ],
]
```

Este ejemplo es solo documental. No existe todavía recepción real de `POST`.

## Validaciones futuras

Validaciones mínimas sugeridas:

- `client_name` obligatorio.
- `quote_date` obligatorio.
- `valid_until` no puede ser anterior a `quote_date`.
- `contact_email` debe tener formato válido si se informa.
- Debe existir al menos un ítem válido.
- `items[index][description]` obligatorio para ítems que se guardan.
- `items[index][quantity]` debe ser mayor que cero.
- `items[index][unit_price_net]` debe ser mayor o igual a cero.
- `items[index][discount_amount]` debe ser mayor o igual a cero.
- El total de línea calculado no debe ser negativo.
- Los totales generales calculados no deben ser negativos.

Las reglas para borrador pueden ser más flexibles, pero el sistema debe evitar guardar estructuras imposibles de recuperar o recalcular.

## Regla para quote_number

`quote_number` no debe enviarse desde el formulario.

Regla definida:

- No se asigna al guardar borrador.
- Puede permanecer `NULL` mientras la cotización está en estado `borrador`.
- Se asigna automáticamente al emitir.
- Desde estado `emitida` debe ser obligatorio y único.
- Formato sugerido: `COT-YYYY-0001`.

El texto visible actual "Se asignará al emitir" en la maqueta es coherente con esta regla.

## Diferencia entre guardar borrador y emitir

### Guardar borrador

La acción `guardar_borrador` debería permitir registrar una cotización incompleta, siempre que cumpla reglas mínimas para no romper el flujo posterior.

Reglas sugeridas para borrador:

- Mantener estado `borrador`.
- No asignar `quote_number`.
- Permitir campos comerciales pendientes.
- Permitir completar contacto más adelante.
- Recalcular totales si existen ítems válidos.

### Emitir cotización

La acción `emitir_cotizacion` debe ser más estricta porque formaliza la cotización.

Reglas sugeridas para emisión:

- Exigir cliente.
- Exigir fecha de cotización.
- Exigir fecha de validez válida.
- Exigir al menos un ítem completo.
- Recalcular totales en backend.
- Asignar `quote_number` único.
- Cambiar estado a `emitida`.
- Bloquear o limitar edición posterior según reglas de trazabilidad futuras.

## Reglas de seguridad futuras

- Validar todo en backend aunque exista validación visual en frontend.
- No confiar en inputs deshabilitados, ocultos o manipulables desde navegador.
- Mantener protección de sesión con `InternalPage` o el mecanismo vigente al implementar.
- Usar token CSRF cuando el formulario sea real.
- No aceptar totales enviados desde frontend como fuente de verdad.
- Normalizar números antes de calcular, evitando depender de separadores de miles visibles.
- Escapar todo dato mostrado nuevamente en HTML.
- Registrar usuario creador cuando exista persistencia real.

## Decisiones pendientes

- Definir si los ítems se podrán agregar y eliminar dinámicamente en frontend.
- Confirmar si el IVA será fijo, configurable por cotización o configurable globalmente.
- Definir si existirán ítems exentos de IVA.
- Definir permisos por rol para crear, editar, emitir y anular.
- Definir trazabilidad de emisión y cambios posteriores.
- Definir si `notes` será interno, visible al cliente o se dividirá en dos campos.
- Definir endpoint o página que procesará el `POST` cuando exista implementación real.
- Definir comportamiento de `cancelar`: volver al listado, limpiar maqueta o pedir confirmación si hay cambios.

## Qué NO se implementó

- No se creó formulario funcional.
- No se agregó `method`.
- No se agregó `action`.
- No se creó recepción real de `POST`.
- No se guardaron datos.
- No se creó base de datos.
- No se creó SQL.
- No se crearon migraciones.
- No se creó CRUD.
- No se crearon controllers.
- No se crearon repositories.
- No se crearon models.
- No se modificó `InternalPage`.
- No se modificaron login, logout, timeout, `AuthGuard` ni `SessionManager`.
- No se implementaron cálculos reales.
- No se implementó PDF.
- No se implementó envío por correo.
- No se agregaron frameworks ni dependencias.

## Próxima etapa recomendada

La siguiente etapa recomendada es definir el flujo técnico de almacenamiento de borradores, todavía como diseño previo, incluyendo dónde se procesará el `POST`, qué capa validará datos y cómo se preparará la futura persistencia sin romper el sistema interno actual.
