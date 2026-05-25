# 68. Generación segura de número de cotización

## Objetivo

Preparar una pieza reutilizable para reservar números oficiales de cotización de forma segura, antes de implementar la emisión real desde navegador.

Formato previsto:

```text
COT-YYYY-0001
```

Ejemplos:

- `COT-2026-0001`
- `COT-2026-0002`

## Archivo creado

Se creó:

```text
sistema/app/Repositories/QuoteNumberRepository.php
```

Namespace:

```php
DAndASystems\Internal\Repositories
```

## Método disponible

```php
public function reserveNextNumber(string $documentType, int $year): string
```

Responsabilidades:

1. Normalizar y validar el tipo de documento.
2. Validar el año del correlativo.
3. Abrir transacción.
4. Bloquear la fila de `cotizacion_correlativos` con `FOR UPDATE`.
5. Crear el contador inicial si no existe.
6. Incrementar `ultimo_numero`.
7. Confirmar la transacción.
8. Devolver el número formateado.

## Tabla utilizada

La reserva usa:

```text
cotizacion_correlativos
```

Campos:

- `tipo_documento`
- `anio`
- `ultimo_numero`

La clave única `tipo_documento + anio` permite mantener un correlativo independiente por tipo y año.

## Seguridad del correlativo

La reserva se hace dentro de una transacción y usa bloqueo de fila:

```sql
SELECT ultimo_numero
FROM cotizacion_correlativos
WHERE tipo_documento = :tipo_documento
  AND anio = :anio
FOR UPDATE
```

Esto prepara el sistema para evitar duplicados cuando dos usuarios intenten emitir cotizaciones al mismo tiempo.

## Qué NO se implementó

- No se implementó emisión real.
- No se cambia estado de `borrador` a `emitida`.
- No se asigna `numero_cotizacion` a ninguna cotización.
- No se modifica `cotizaciones`.
- No se implementa endpoint público.
- No se implementa formulario de emisión.
- No se implementa PDF.
- No se implementa correo.
- No se implementa AJAX.
- No se implementa API JSON.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-quote-number-repository.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-number-repository.php
```

La herramienta valida el contrato del repositorio sin reservar números reales, sin ejecutar SQL y sin usar base de datos.

## Uso futuro esperado

En la etapa de emisión, el flujo recomendado será:

1. Validar sesión y CSRF.
2. Cargar la cotización.
3. Confirmar que está en estado `borrador`.
4. Validar datos estrictos de emisión.
5. Iniciar transacción.
6. Reservar número con `QuoteNumberRepository`.
7. Asignar `numero_cotizacion`.
8. Cambiar estado a `emitida`.
9. Confirmar transacción.

## Próxima etapa recomendada

La siguiente etapa recomendada es diseñar o implementar el flujo de emisión controlada, reutilizando este repositorio para reservar el número oficial solo en el momento de emitir.
