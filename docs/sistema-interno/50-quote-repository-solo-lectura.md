# Etapa 7A.6 - QuoteRepository base de solo lectura

## Objetivo

Crear una primera capa de acceso a datos de solo lectura para el modulo Cotizaciones, usando la infraestructura PDO existente y sin modificar paginas publicas ni datos.

Esta etapa prepara el camino para futuras lecturas controladas desde el sistema interno, pero todavia no implementa CRUD, formularios, `POST`, guardado de borradores ni emision de cotizaciones.

## Repository creado

Archivo:

`sistema/app/Repositories/QuoteRepository.php`

Namespace:

`DAndASystems\Internal\Repositories`

El repositorio recibe una instancia de `PDO` por constructor. No crea conexiones propias, no imprime salida y no conoce rutas publicas. Solo encapsula consultas `SELECT` sobre la tabla `cotizaciones`.

## Metodos disponibles

### countAll(): int

Cuenta el total de registros existentes en `cotizaciones`.

Consulta usada:

```sql
SELECT COUNT(*) FROM cotizaciones
```

### findRecent(int $limit = 10): array

Obtiene cotizaciones recientes ordenadas por `creado_en DESC, id DESC`.

El limite se normaliza entre `1` y `50` para evitar consultas demasiado amplias desde una llamada accidental.

Campos leidos:

- `id`
- `numero_cotizacion`
- `fecha_cotizacion`
- `valido_hasta`
- `nombre_cliente`
- `rut_cliente`
- `estado`
- `subtotal_neto`
- `iva_monto`
- `total`
- `creado_en`
- `actualizado_en`

### findById(int $id): ?array

Busca una cotizacion por `id`.

Reglas:

- Si `$id <= 0`, retorna `null`.
- Si no existe registro, retorna `null`.
- Por ahora trae solo cabecera de cotizacion, sin detalles.

## Herramienta CLI creada

Archivo:

`sistema/tools/check-quote-repository.php`

La herramienta:

- Se ejecuta solo por CLI.
- Carga `DatabaseConfig::fromDefaultPath()->load()`.
- Crea `Connection`.
- Crea `QuoteRepository`.
- Ejecuta `countAll()`.
- Ejecuta `findRecent(5)`.
- Muestra salida simple con `[OK]` o `[ERROR]`.
- No expone host, usuario, password, nombre de base ni stack trace.

## Como ejecutar la prueba

Desde la raiz del proyecto:

```bash
php sistema/tools/check-quote-repository.php
```

Con PHP de Laragon en Windows:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-repository.php
```

La herramienta requiere que exista `sistema/config/database.php` configurado contra una base local o de prueba donde ya se haya ejecutado el SQL inicial.

## Que valida

La prueba valida que:

- La configuracion local de base de datos puede cargarse.
- La conexion PDO funciona.
- `QuoteRepository` puede instanciarse con PDO.
- La tabla `cotizaciones` permite consultas de lectura basicas.
- La lectura reciente puede ejecutarse con limite controlado.

## Seguridad y alcance

El repositorio y la herramienta CLI solo realizan consultas `SELECT`.

No se exponen credenciales ni detalles tecnicos sensibles en caso de error.

## Que NO se implemento

- No se insertan datos.
- No se actualizan datos.
- No se eliminan datos.
- No se modifica estructura de base de datos.
- No se ejecuta SQL DDL.
- No se crean migraciones.
- No se modifican paginas publicas.
- No se modifica `cotizaciones.php`.
- No se modifica `dashboard.php`.
- No se modifica `InternalPage`.
- No se modifican login, logout, AuthGuard, SessionManager ni timeout.
- No se crean formularios.
- No se implementa `POST`.
- No se crean controllers.
- No se crean services.
- No se implementa guardar borrador.
- No se implementa emision.
- No se implementa PDF.
- No se implementa correo.

## Proxima etapa recomendada

La siguiente etapa recomendada es usar esta capa de lectura para preparar una integracion controlada del listado de cotizaciones, manteniendo primero una salida de solo lectura y sin acciones de modificacion.
