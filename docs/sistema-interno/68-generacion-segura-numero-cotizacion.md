# 68. Generacion segura de numero de cotizacion

## Objetivo

Preparar una pieza reutilizable para reservar numeros oficiales de cotizacion de forma segura, antes de implementar la emision real desde navegador.

Formato previsto:

```text
COT-YYYY-0001
```

Ejemplos:

- `COT-2026-0001`
- `COT-2026-0002`

## Archivo creado

Se creo:

```text
sistema/app/Repositories/QuoteNumberRepository.php
```

Namespace:

```php
DAndASystems\Internal\Repositories
```

## Metodos disponibles

```php
public function reserveNextNumber(string $documentType, int $year): string
```

Responsabilidades:

1. Abrir transaccion.
2. Llamar a `reserveNextNumberInCurrentTransaction()`.
3. Confirmar la transaccion.
4. Hacer rollback ante error.
5. Devolver el numero formateado.

Este metodo queda como wrapper standalone para usos aislados.

```php
public function reserveNextNumberInCurrentTransaction(string $documentType, int $year): string
```

Responsabilidades:

1. Normalizar y validar el tipo de documento.
2. Validar el anio del correlativo.
3. Crear de forma segura el contador inicial si no existe.
4. Bloquear la fila de `cotizacion_correlativos` con `FOR UPDATE`.
5. Incrementar `ultimo_numero`.
6. Devolver el numero formateado.

Este metodo asume que el caller ya abrio una transaccion. No abre transaccion, no hace `commit` y no hace `rollback`.

## Tabla utilizada

La reserva usa:

```text
cotizacion_correlativos
```

Campos:

- `tipo_documento`
- `anio`
- `ultimo_numero`

La clave unica `tipo_documento + anio` permite mantener un correlativo independiente por tipo y anio.

## Seguridad del correlativo

La reserva usa `cotizacion_correlativos` y no calcula el siguiente numero con `MAX()+1` desde `cotizaciones`.

Primero crea el contador inicial de forma idempotente:

```sql
INSERT INTO cotizacion_correlativos (
  tipo_documento,
  anio,
  ultimo_numero
) VALUES (
  :tipo_documento,
  :anio,
  0
)
ON DUPLICATE KEY UPDATE ultimo_numero = ultimo_numero
```

Luego bloquea la fila dentro de la transaccion abierta:

```sql
SELECT ultimo_numero
FROM cotizacion_correlativos
WHERE tipo_documento = :tipo_documento
  AND anio = :anio
FOR UPDATE
```

Con la fila bloqueada, incrementa `ultimo_numero` y devuelve el numero formateado. Esto prepara el sistema para evitar duplicados cuando dos usuarios intenten emitir cotizaciones al mismo tiempo.

## Que NO se implemento

- No se implemento emision real.
- No se cambia estado de `borrador` a `emitida`.
- No se asigna `numero_cotizacion` a ninguna cotizacion.
- No se modifica `cotizaciones`.
- No se implementa endpoint publico.
- No se implementa formulario de emision.
- No se implementa PDF.
- No se implementa correo.
- No se implementa AJAX.
- No se implementa API JSON.

## Herramienta CLI

Se creo:

```bash
php sistema/tools/check-quote-number-repository.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-number-repository.php
```

La herramienta valida el contrato del repositorio sin reservar numeros reales, sin ejecutar SQL y sin usar base de datos.

## Uso futuro esperado

En la etapa de emision, el flujo recomendado sera:

1. Validar sesion y CSRF.
2. Cargar la cotizacion.
3. Confirmar que esta en estado `borrador`.
4. Validar datos estrictos de emision.
5. Iniciar transaccion.
6. Reservar numero con `QuoteNumberRepository::reserveNextNumberInCurrentTransaction()`.
7. Asignar `numero_cotizacion`.
8. Cambiar estado a `emitida`.
9. Confirmar transaccion.

## Proxima etapa recomendada

La siguiente etapa recomendada es planificar o implementar el flujo de emision controlada, reutilizando este repositorio para reservar el numero oficial solo en el momento de emitir.
