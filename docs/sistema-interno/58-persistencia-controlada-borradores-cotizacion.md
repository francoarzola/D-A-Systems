# 58. Persistencia controlada de borradores de cotización

## Objetivo

Agregar persistencia controlada de borradores al repositorio de cotizaciones, sin exponer todavía formularios públicos, POST desde navegador ni acciones funcionales en la interfaz.

La escritura se prueba únicamente mediante una herramienta CLI segura para entorno local o de prueba.

## Archivos creados o modificados

- Modificado: `sistema/app/Repositories/QuoteRepository.php`
- Creado: `sistema/tools/create-draft-quote-check.php`
- Creado: `docs/sistema-interno/58-persistencia-controlada-borradores-cotizacion.md`

## Métodos agregados a QuoteRepository

### createDraft

```php
createDraft(array $header, array $calculatedTotals, ?int $createdBy = null): int
```

Responsabilidades:

- insertar cabecera en `cotizaciones`
- insertar detalles en `cotizacion_detalles`
- usar una transacción
- devolver el ID de la cotización creada
- mantener `numero_cotizacion` en `NULL`
- mantener `estado` en `borrador`
- usar montos calculados desde `QuoteTotalsCalculator`
- no tocar `cotizacion_correlativos`

### draftExistsByClientAndDescription

```php
draftExistsByClientAndDescription(string $clientName, string $description): bool
```

Responsabilidad:

- verificar si ya existe un borrador de prueba con el mismo cliente y descripción
- evitar insertar duplicados desde la herramienta CLI

## Herramienta CLI creada

Archivo:

```bash
sistema/tools/create-draft-quote-check.php
```

La herramienta:

- se ejecuta solo por CLI
- exige `--confirm-local`
- valida que el entorno parezca local o de prueba
- carga `DatabaseConfig`
- carga `Connection`
- usa `QuoteRepository`
- valida datos con `QuoteDraftValidator`
- calcula totales con `QuoteTotalsCalculator`
- guarda el borrador con `QuoteRepository::createDraft()`
- evita duplicados
- usa bloqueo nombrado de MySQL para reducir riesgo de doble ejecución simultánea
- no muestra credenciales ni detalles técnicos sensibles

## Flujo técnico

```text
datos de prueba
  -> QuoteDraftValidator
  -> QuoteTotalsCalculator
  -> QuoteRepository::createDraft()
  -> base de datos
```

## Regla de número de cotización

Los borradores se crean con:

```text
numero_cotizacion = NULL
```

La herramienta no genera números oficiales y no toca `cotizacion_correlativos`.

## Regla de estado

Los registros se crean con:

```text
estado = borrador
```

La herramienta no emite cotizaciones ni cambia estados posteriores.

## Transacción

`QuoteRepository::createDraft()` usa transacción para que cabecera y detalles se creen como una sola operación.

Si falla la cabecera o algún detalle, la operación hace rollback.

## Validación de entorno local o prueba

La herramienta solo permite insertar si:

- el host es `localhost` o `127.0.0.1`
- la base es `dasystems_internal_local` o contiene `_local`, `_test` o `prueba`

Si no cumple, se bloquea sin mostrar datos sensibles de configuración.

## Cómo ejecutar

```bash
php sistema/tools/create-draft-quote-check.php --confirm-local
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/create-draft-quote-check.php --confirm-local
```

Sin `--confirm-local`, la herramienta debe bloquear la ejecución.

## Qué validar después

- Abrir `cotizaciones.php` y revisar que aparezca el borrador.
- Abrir `cotizacion-detalle.php?id={id}` con el ID mostrado por la herramienta.
- Confirmar que el estado sea `borrador`.
- Confirmar que el número de cotización aparezca como sin emitir.
- Confirmar que los totales correspondan a:
  - subtotal neto: `1200000`
  - IVA: `228000`
  - total: `1428000`

## Qué NO se implementó

- No se implementó POST.
- No se implementó formulario público funcional.
- No se implementó guardado desde navegador.
- No se implementó edición.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se crearon controllers.
- No se modificaron páginas públicas.
- No se modificó la estructura de base de datos.
- No se ejecutó SQL DDL.

## Próxima etapa recomendada

La próxima etapa recomendada es crear una capa de servicio de escritura para coordinar validación, cálculo y persistencia antes de exponer un endpoint POST real.
