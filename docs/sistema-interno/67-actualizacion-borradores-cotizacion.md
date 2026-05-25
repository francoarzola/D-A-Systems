# 67. Actualización de borradores de cotización

## Objetivo

Permitir la actualización real de cotizaciones existentes solo cuando están en estado `borrador`, reutilizando validación backend, cálculo centralizado de totales, CSRF, mensajes flash y POST/Redirect/GET.

Esta etapa no implementa emisión ni cambios de estado.

## Endpoint creado

Se creó:

```text
sistema/public/cotizacion-actualizar.php
```

El endpoint:

- acepta solo `POST`
- exige autenticación con `AuthGuard`
- valida CSRF con clave `quote_draft_edit`
- lee `cotizacion_id` desde `POST`
- valida que el ID sea entero positivo
- construye datos permitidos desde el formulario
- llama a `QuoteService::updateDraft()`
- redirige con HTTP 303
- muestra mensajes mediante `FlashMessage`

## Formulario de edición

`sistema/public/cotizacion-editar.php` volvió a tener un formulario real:

- `method="post"`
- `action="cotizacion-actualizar.php"`
- token CSRF `quote_draft_edit`
- hidden `cotizacion_id`
- hidden `form_action=guardar_borrador`
- botón real `Guardar cambios`

La página sigue cargando datos reales solo para cotizaciones en estado `borrador`.

## Método agregado en QuoteService

Se agregó:

```php
QuoteService::updateDraft(int $quoteId, array $draftData): array
```

Responsabilidades:

1. Validar ID positivo.
2. Validar datos con `QuoteDraftValidator`.
3. Calcular totales con `QuoteTotalsCalculator`.
4. Solicitar persistencia a `QuoteRepository::updateDraft()`.
5. Devolver resultado controlado para errores de validación o estado no editable.

Los errores técnicos de persistencia siguen subiendo hacia la capa endpoint, que muestra un mensaje genérico.

## Método agregado en QuoteRepository

Se agregó:

```php
QuoteRepository::updateDraft(int $quoteId, array $header, array $calculatedTotals): bool
```

El repositorio:

- usa transacción
- bloquea la cotización con `FOR UPDATE`
- permite actualizar solo si `estado = 'borrador'`
- no modifica `numero_cotizacion`
- no modifica `estado`
- actualiza cabecera y montos calculados
- elimina detalles actuales del borrador
- inserta los detalles recalculados
- hace rollback si ocurre un error

## Datos permitidos desde navegador

El endpoint acepta:

- `cotizacion_id`
- `form_action`
- `nombre_cliente`
- `rut_cliente`
- `nombre_contacto`
- `correo_contacto`
- `telefono_contacto`
- `descripcion`
- `fecha_cotizacion`
- `valido_hasta`
- `condiciones_comerciales`
- `observaciones`
- `detalles[0][descripcion]`
- `detalles[0][cantidad]`
- `detalles[0][unidad]`
- `detalles[0][precio_unitario_neto]`
- `detalles[0][descuento_monto]`

## Datos no aceptados desde navegador

No se aceptan ni se confía en:

- `numero_cotizacion`
- `estado`
- `subtotal_neto`
- `iva_porcentaje`
- `iva_monto`
- `total`
- campos de correlativos

Los totales se recalculan siempre en backend.

## Manejo de errores

- Método distinto de `POST`: mensaje genérico y redirección al listado.
- ID inválido: mensaje genérico y redirección al listado.
- CSRF inválido: mensaje genérico y redirección a edición.
- Validación fallida: conserva temporalmente datos y errores, y vuelve a edición.
- Estado no editable: mensaje controlado y vuelve a edición.
- Error técnico: mensaje genérico y vuelve a edición.

## Qué NO se implementó

- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se implementó AJAX.
- No se implementó API JSON.
- No se agregó cálculo en frontend.
- No se agregaron múltiples líneas dinámicas.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-update-draft-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-update-draft-contract.php
```

La herramienta verifica el contrato del endpoint, formulario, servicio y repositorio sin ejecutar POST real ni usar base de datos.

## Prueba manual recomendada

1. Iniciar sesión.
2. Crear o ubicar una cotización en estado `borrador`.
3. Abrir `cotizacion-editar.php?id={id}`.
4. Modificar cliente, fechas o primer detalle.
5. Guardar cambios.
6. Confirmar redirección al detalle.
7. Confirmar que los datos actualizados se muestran.
8. Intentar editar una cotización no borrador y confirmar que se bloquea.

## Próxima etapa recomendada

La próxima etapa recomendada es robustecer la edición con más de una línea de detalle o preparar la emisión formal de borradores, manteniendo siempre validación backend y control de estado.
