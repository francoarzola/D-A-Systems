# Flujo de atenciones

## Objetivo

Registrar atención técnica, diagnóstico y trabajo realizado para cada cliente, generando un historial útil y estructurado.

## Registro de atención

- El usuario crea un nuevo reporte de atención.
- Campos obligatorios:
  - Cliente
  - Fecha de atención
  - Diagnóstico
  - Trabajo realizado
- Campos opcionales:
  - Recomendaciones
  - Próximos pasos
  - Estado de seguimiento

## Asociar cliente

- Seleccionar cliente activo existente.
- Mostrar información básica del cliente en el formulario.
- Validar que el cliente existe antes de guardar.

## Registrar diagnóstico

- Capturar el problema detectado de forma clara.
- Incluir observaciones técnicas necesarias.
- Mantener el formato legible para futuros seguimientos.

## Registrar trabajo realizado

- Detallar las acciones ejecutadas.
- Incluir tareas específicas, componentes revisados y resultados.
- Registrar horas o duración si se desea un control adicional.

## Recomendaciones

- Añadir recomendaciones de mantenimiento o mejora.
- Indicar riesgo si aplica.
- Incluir pasos sugeridos para el cliente.

## Estado

Estados iniciales:
- `abierto`
- `en_proceso`
- `cerrado`

Flujo:
- Un reporte inicia en `abierto`.
- Se puede actualizar a `en_proceso` durante el trabajo.
- Se cierra cuando la atención ha finalizado.

## Informe PDF futuro

- Diseñar una plantilla de informe PDF para la atención técnica.
- Incluir datos del cliente, diagnóstico, trabajo realizado y recomendaciones.
- Generar PDF solo con usuario autenticado.
- Mantener la entrega de archivos en una ruta privada.
- Esta funcionalidad puede agregarse después del MVP.

## Uso en el sistema

- Las atenciones deben estar vinculadas a un cliente y a un usuario responsable.
- Debería existir un listado con filtros por cliente, estado y fecha.
- Los registros permiten construir un historial técnico que soporte cotizaciones futuras.
