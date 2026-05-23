# Etapa 6D.20 — Diseño funcional inicial del módulo Cotizaciones

## Objetivo del módulo

El módulo Cotizaciones permitirá crear, organizar y dar seguimiento a propuestas comerciales para clientes de D&A Systems. Su primera versión debe enfocarse en registrar información clara, calcular totales y mantener un estado funcional de cada cotización.

## Alcance inicial

- Definir la estructura funcional de una cotización.
- Identificar estados, campos y vistas necesarias.
- Preparar la página interna `cotizaciones.php` con una vista informativa del módulo.
- Mantener el módulo sin lógica real hasta que se implemente la etapa técnica correspondiente.

## Qué queda fuera por ahora

- CRUD de cotizaciones.
- Formularios funcionales.
- Tablas SQL, migraciones o cambios de base de datos.
- Cálculo real de totales.
- Generación de PDF.
- Envío por correo.
- Integración real con clientes.
- Permisos por rol.
- Trazabilidad o auditoría de cambios.

## Actores o usuarios del módulo

- Administrador interno: podrá gestionar cotizaciones y revisar su estado.
- Usuario comercial o administrativo: podrá crear y actualizar cotizaciones cuando existan permisos definidos.
- Cliente: no accede al sistema interno en esta etapa, pero será el destinatario de la cotización.

## Flujo funcional propuesto

1. Crear una cotización.
2. Agregar datos del cliente.
3. Agregar datos de contacto.
4. Agregar ítems o servicios cotizados.
5. Calcular subtotal, descuentos, IVA si corresponde y total.
6. Guardar la cotización como borrador.
7. Emitir o marcar como enviada cuando esté lista.
8. Registrar aceptación, rechazo o anulación según corresponda.
9. Eventualmente generar PDF para entrega formal.

## Estados sugeridos

- `borrador`: cotización en preparación.
- `emitida`: cotización terminada internamente.
- `enviada`: cotización enviada al cliente.
- `aceptada`: cotización aprobada por el cliente.
- `rechazada`: cotización no aceptada por el cliente.
- `anulada`: cotización invalidada por corrección, duplicidad u otro motivo.

## Campos sugeridos para una cotización

- número de cotización
- fecha
- fecha de validez
- cliente
- contacto
- correo
- teléfono
- descripción general
- ítems
- subtotal
- descuentos
- IVA si corresponde
- total
- estado
- observaciones
- condiciones comerciales

## Campos sugeridos para ítems

- descripción
- cantidad
- unidad
- precio unitario
- descuento
- total línea

## Vistas futuras

- Listado de cotizaciones.
- Nueva cotización.
- Detalle de cotización.
- Edición de borrador.
- Vista previa PDF.

## Riesgos o decisiones pendientes

- Numeración correlativa: definir si será global, anual o por prefijo.
- Uso de IVA: confirmar cuándo aplica y cómo se mostrará.
- Integración con clientes: definir si se selecciona desde un módulo de clientes o se ingresan datos manuales.
- Formato PDF: definir plantilla, logo, condiciones y datos legales.
- Permisos por rol: definir quién puede crear, emitir, anular o ver cotizaciones.
- Trazabilidad de cambios: definir si se registrarán cambios de estado y modificaciones relevantes.

## Actualización de la página interna

La página `sistema/public/cotizaciones.php` mantiene `InternalPage::render()` y la navegación activa `cotizaciones`. Solo se reemplazó el placeholder básico por contenido informativo estático del diseño funcional.

## Qué NO se implementó

- No se creó base de datos.
- No se crearon tablas SQL.
- No se crearon migraciones.
- No se creó CRUD.
- No se crearon formularios funcionales.
- No se crearon botones de acción real.
- No se implementó generación de PDF.
- No se implementó envío por correo.
- No se creó lógica de negocio.

## Próxima etapa recomendada

La siguiente etapa recomendada es definir el modelo de datos mínimo para cotizaciones e ítems, todavía como diseño técnico antes de crear tablas o formularios reales.
