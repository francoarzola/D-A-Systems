# Flujo de cotizaciones

## Visión general

El flujo de cotizaciones debe permitir crear propuestas comerciales rápidas para clientes, gestionar ítems, calcular totales y emitir un documento PDF descargable dentro de un área autenticada.

## Paso 1: Seleccionar o crear cliente

1. El usuario accede a la sección de Cotizaciones.
2. Puede buscar un cliente existente o crear uno nuevo.
3. Si se crea un cliente nuevo, el sistema guarda los datos y vuelve al formulario de cotización.
4. Si se selecciona un cliente existente, el formulario carga datos básicos del cliente.

## Paso 2: Crear cotización

Campos principales:
- Cliente
- Fecha de emisión
- Fecha de vencimiento
- Moneda
- Observaciones
- Estado inicial `Borrador`

Reglas:
- El campo cliente es obligatorio.
- Una cotización puede guardarse como borrador sin emitirla.

## Paso 3: Agregar ítems

Cada ítem debe contener:
- Descripción del servicio o producto
- Cantidad
- Precio unitario
- Descuento opcional
- Total calculado por fila

Reglas de cálculo:
- `subtotal = SUM(total_price de cada ítem)`
- `tax = subtotal * tasa_iva`
- `total = subtotal + tax`
- El cálculo se realiza en backend antes de guardar.
- `discount` se aplica por ítem y no debe ser negativo.

## Paso 4: Guardar borrador

- El usuario puede guardar la cotización en estado `Borrador`.
- Un borrador puede modificarse después.
- Se registra el autor y la fecha de creación.

## Paso 5: Emitir cotización

- Emitir cambia `is_draft = 0` y `status_id` a `Emitida`.
- Se genera un `quote_number` único con formato definido (por ejemplo `DAS-2026-0001`).
- Se actualiza `issue_date` y `expiry_date` si el usuario lo define.
- El sistema registra el evento en `audit_logs`.

## Paso 6: Generar PDF

- El PDF se genera con Dompdf o mPDF.
- La plantilla debe incluir:
  - Datos del cliente
  - Detalle de ítems
  - Subtotal, IVA y total
  - Número de cotización
  - Fecha de emisión
  - Validez
  - Observaciones
  - Firma y datos de D&A Systems

- El PDF se crea en ubicación privada y se entrega solo mediante controlador autenticado.

## Paso 7: Descargar PDF autenticado

- El usuario puede descargar la cotización emitida desde la aplicación.
- El controlador valida:
  - Usuario autenticado
  - Permiso de acceso al cliente/cotización
  - Que la cotización existe y no está eliminada

## Paso 8: Cambiar estado

Estados principales:
- `Borrador`
- `Emitida`
- `Aceptada`
- `Rechazada`
- `Vencida`

Flujo de estado:
- Un borrador puede convertirse en emitida.
- Una cotización emitida puede marcarse como aceptada o rechazada.
- Las cotizaciones vencidas pueden determinarse según `expiry_date`.

Reglas de negocio:
- Solo las cotizaciones emitidas pueden descargarse como PDF.
- Solo `admin` puede cambiar estados sensibles si se requiere.

## Mejora futura: duplicar cotización

- Permitir clonar una cotización existente para acelerar la creación de propuestas similares.
- Al duplicar, copiar cliente, ítems y observaciones, pero crear una nueva `quote_number` y mantener `is_draft = 1`.
- Esta función queda para versiones posteriores al MVP.
