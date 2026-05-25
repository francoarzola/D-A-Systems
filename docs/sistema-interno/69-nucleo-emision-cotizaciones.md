# 69. Nucleo de emision de cotizaciones

## Objetivo

Implementar el nucleo backend para emitir un borrador como cotizacion oficial, sin exponer todavia un boton ni un endpoint desde navegador.

La etapa permite preparar la regla critica de emision antes de crear interfaz publica: una cotizacion en estado `borrador` puede convertirse en `emitida` solo si cumple las validaciones internas y obtiene un numero oficial seguro.

## Por que primero sin endpoint

La emision cambia estado, asigna `numero_cotizacion` y consume un correlativo oficial. Por eso primero se implementa como nucleo backend transaccional, aislado de formularios, botones, AJAX o API JSON.

Este orden permite validar el contrato y la concurrencia sin abrir una accion de navegador que pueda usarse antes de terminar los controles de UI, permisos y CSRF.

## Metodo agregado en QuoteRepository

```php
public function issueDraft(int $quoteId, string $documentType = 'COT'): ?array
```

Responsabilidades:

1. Rechazar IDs invalidos.
2. Abrir una transaccion.
3. Bloquear la cotizacion con `SELECT ... FOR UPDATE`.
4. Confirmar que existe.
5. Confirmar que esta en estado `borrador`.
6. Confirmar que `numero_cotizacion` es `NULL` o esta vacio.
7. Validar datos minimos para emitir.
8. Reservar numero oficial.
9. Actualizar `cotizaciones`.
10. Confirmar la transaccion.
11. Hacer rollback si ocurre un error tecnico.

Retorna `null` si la cotizacion no puede emitirse por regla de negocio.

## Metodo agregado en QuoteService

```php
public function issueDraft(int $quoteId): array
```

Retorno exitoso:

```php
[
    'success' => true,
    'quote_id' => $quoteId,
    'numero_cotizacion' => 'COT-2026-0001',
    'estado' => 'emitida',
    'errors' => [],
]
```

Retorno cuando no se puede emitir:

```php
[
    'success' => false,
    'quote_id' => $quoteId,
    'numero_cotizacion' => null,
    'estado' => null,
    'errors' => ['Solo se pueden emitir cotizaciones en estado borrador.'],
]
```

`QuoteService` no captura excepciones tecnicas. Si el repositorio falla por base de datos u otro error tecnico, la excepcion se propaga.

## Flujo transaccional

El repositorio ejecuta:

1. `beginTransaction()`.
2. `SELECT ... FROM cotizaciones ... FOR UPDATE`.
3. Validaciones de estado, numero y datos minimos.
4. Reserva de numero oficial dentro de la misma transaccion.
5. `UPDATE cotizaciones`.
6. `commit()`.

Si ocurre un `Throwable`, el repositorio hace `rollBack()` y vuelve a lanzar la excepcion.

## Uso de QuoteNumberRepository

La emision usa el mismo `PDO` transaccional para instanciar:

```php
$numbers = new QuoteNumberRepository($this->pdo);
```

Luego llama:

```php
$numbers->reserveNextNumberInCurrentTransaction($documentType, $year);
```

Ese metodo no abre ni cierra transacciones. La reserva queda dentro de la transaccion de emision y mantiene el formato:

```text
COT-YYYY-0001
```

## Validaciones antes de emitir

Antes de asignar numero oficial se valida:

- La cotizacion existe.
- `estado` es `borrador`.
- `numero_cotizacion` es `NULL` o esta vacio.
- `nombre_cliente` no esta vacio.
- `fecha_cotizacion` existe.
- Hay al menos un detalle en `cotizacion_detalles`.

El anio del correlativo se calcula con `fecha_cotizacion` cuando tiene formato valido `Y-m-d`. Si no se puede interpretar, se usa el anio actual.

## Que cambia en cotizaciones

La etapa solo modifica la fila emitida:

```sql
UPDATE cotizaciones
SET numero_cotizacion = :numero_cotizacion,
    estado = 'emitida',
    actualizado_en = CURRENT_TIMESTAMP
WHERE id = :id
  AND estado = 'borrador'
  AND (numero_cotizacion IS NULL OR numero_cotizacion = '')
```

No modifica detalles ni estructura de base de datos.

## Que NO se implemento

- No se creo endpoint publico.
- No se creo `sistema/public/cotizacion-emitir.php`.
- No se modifico `cotizacion-editar.php`.
- No se modifico `cotizaciones.php`.
- No se modifico `cotizacion-detalle.php`.
- No se creo boton de emision.
- No se implemento PDF.
- No se implemento correo.
- No se creo AJAX.
- No se creo API JSON.
- No se implemento anulacion.
- No se implemento historial ni auditoria.
- No se ejecuta SQL DDL.

## Herramienta CLI

Se creo:

```bash
php sistema/tools/check-quote-issue-core-contract.php
```

La herramienta valida el contrato leyendo archivos fuente. No usa base de datos, no modifica archivos y no emite una cotizacion real.

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-issue-core-contract.php
```

## Proxima etapa recomendada

La siguiente etapa recomendada es crear el endpoint controlado de emision con validacion de sesion, permisos y CSRF, reutilizando `QuoteService::issueDraft()` como unica entrada backend para emitir.
