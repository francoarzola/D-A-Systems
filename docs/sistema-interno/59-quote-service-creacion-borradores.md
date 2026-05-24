# 59. QuoteService para creación de borradores

## Objetivo

Agregar a `QuoteService` una operación de escritura controlada para crear borradores de cotización, coordinando validación, cálculo y persistencia sin exponer todavía formularios públicos ni endpoints POST.

## Archivos creados o modificados

- Modificado: `sistema/app/Services/QuoteService.php`
- Modificado: `sistema/tools/create-draft-quote-check.php`
- Creado: `sistema/tools/check-quote-service-create-draft.php`
- Creado: `docs/sistema-interno/59-quote-service-creacion-borradores.md`

## Método agregado

```php
createDraft(array $draftData, ?int $createdBy = null): array
```

El método retorna una estructura controlada:

- `success`
- `quote_id`
- `errors`
- `warnings`
- `totals`

## Dependencias de QuoteService

`QuoteService` mantiene compatibilidad con el uso actual de solo lectura.

Ahora puede recibir opcionalmente:

- `QuoteDraftValidator`
- `QuoteTotalsCalculator`

Si no se entregan, el servicio los crea internamente cuando se llama a `createDraft()`.

## Flujo técnico

```text
QuoteService::createDraft()
  -> QuoteDraftValidator::validateDraft()
  -> QuoteTotalsCalculator::calculate()
  -> QuoteRepository::createDraft()
```

Si la validación falla, el servicio no calcula ni persiste.

Si la persistencia falla por error técnico, la excepción sube hacia la capa CLI o endpoint futuro. Esa capa debe mostrar un mensaje genérico y, más adelante, registrar el detalle técnico en logging.

## Herramienta CLI actualizada

`sistema/tools/create-draft-quote-check.php` ahora usa `QuoteService::createDraft()` para guardar el borrador.

La herramienta conserva:

- ejecución solo por CLI
- requisito de `--confirm-local`
- validación de entorno local o prueba
- bloqueo nombrado de MySQL
- idempotencia por cliente y descripción
- mensajes simples con `[OK]` y `[ERROR]`

## Herramienta CLI nueva

Se creó:

```bash
php sistema/tools/check-quote-service-create-draft.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-service-create-draft.php
```

Esta herramienta usa SQLite en memoria para verificar el flujo del servicio sin tocar la base real del proyecto.

Valida:

- creación de un borrador válido
- estado `borrador`
- `numero_cotizacion = NULL`
- creación de detalles
- totales esperados
- rechazo de un borrador inválido
- propagación de una excepción técnica de persistencia simulada en SQLite en memoria

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
- No se ejecutó SQL DDL sobre la base real.

## Próxima etapa recomendada

La próxima etapa recomendada es preparar un endpoint POST protegido para guardar borradores desde un formulario real, reutilizando `QuoteService::createDraft()` y agregando protección CSRF.
