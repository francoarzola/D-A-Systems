# Flujo de clientes

## Objetivo

Permitir al equipo interno de D&A Systems administrar la base de clientes con relación a cotizaciones y atenciones técnicas.

## Alta de cliente

- El usuario ingresa datos obligatorios: nombre, email y teléfono.
- Opcional: razón social, RUT, dirección, ciudad, región y persona de contacto.
- El sistema valida email y teléfono.
- Se guarda el cliente con `is_active = 1`.
- Se registra el creador y la fecha.

## Edición de cliente

- El usuario puede actualizar datos del cliente.
- Solo campos editables: nombre, contacto, dirección, notas.
- El historial de cambios se registra en `audit_logs`.
- No se elimina información crítica sin control.

## Búsqueda

- Buscar por nombre, razón social, RUT, email o teléfono.
- Listar resultados con paginación ligera.
- Mostrar estado activo/inactivo.
- Ofrecer filtros básicos por ciudad, región o estado.

## Desactivación

- No borrar clientes físicamente.
- Marcar `is_active = 0` para desactivar.
- Una cliente desactivado no aparece en la selección de cotizaciones.
- Seguir conservando su historial de cotizaciones y atenciones.
- El sistema debe permitir reactivar clientes si es necesario.

## Relación con cotizaciones

- Cada cotización está asociada a un cliente.
- Al crear cotizaciones, se selecciona un cliente activo.
- El detalle del cliente se muestra en la vista de cotización.
- Si el cliente se desactiva, la cotización histórica permanece disponible para consulta.

## Relación con atenciones

- Cada atención técnica se asocia a un cliente.
- El reporte de atención incluye datos básicos del cliente.
- Las atenciones reflejan el historial de servicio por cliente.
- Permitir filtrar atenciones por cliente en el módulo de informes.

## Consideraciones operativas

- Mantener datos de cliente lo más actualizados posible.
- Evitar duplicidad de clientes con validación de email y RUT.
- Registrar cambios importantes en `audit_logs`.
- Usar etiquetas o notas internas para información relevante de servicio.
