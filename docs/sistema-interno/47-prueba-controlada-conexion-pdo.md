# Etapa 7A.3 — Prueba controlada de conexión PDO

## Objetivo

Crear una prueba controlada de conexión PDO para validar que `DatabaseConfig` y `Connection` funcionan correctamente con `sistema/config/database.php`, sin ejecutar SQL del módulo Cotizaciones, sin crear tablas, sin CRUD y sin exponer información sensible.

## Revisión previa

El proyecto ya cuenta con infraestructura PDO:

- `sistema/app/Infrastructure/Config/DatabaseConfig.php`
- `sistema/app/Infrastructure/Database/Connection.php`
- `sistema/config/database.example.php`

También existía una herramienta previa:

- `sistema/tools/test-db-connection.php`

Para esta etapa se crea una prueba con el nombre explícito de la etapa:

- `sistema/tools/check-db-connection.php`

La ubicación `sistema/tools` es adecuada porque no pertenece a `sistema/public` y está pensada para herramientas internas ejecutadas por CLI.

## Archivo creado

`sistema/tools/check-db-connection.php`

## Cómo funciona

La herramienta:

1. Verifica que se ejecute por CLI.
2. Carga `DatabaseConfig::fromDefaultPath()->load()`.
3. Crea una conexión con `new Connection($config)`.
4. Obtiene PDO con `pdo()`.
5. Ejecuta una consulta inocua:

```sql
SELECT 1
```

6. Muestra solo un mensaje simple:

- `Conexión OK`
- `Error de conexión controlado`

No muestra host, usuario, password, nombre de base de datos ni stack trace.

## Cómo ejecutar la prueba

Desde la raíz del proyecto:

```bash
php sistema/tools/check-db-connection.php
```

En Windows con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-db-connection.php
```

## Requisitos previos

Antes de ejecutar el script debe existir el archivo real:

`sistema/config/database.php`

Ese archivo debe crearse manualmente a partir de:

`sistema/config/database.example.php`

No debe subirse a GitHub porque contiene credenciales reales.

## Seguridad

- La herramienta no está en `sistema/public`.
- La herramienta rechaza ejecución web con respuesta `403`.
- No expone credenciales.
- No muestra detalles técnicos del error.
- No modifica datos.
- No ejecuta SQL del módulo Cotizaciones.
- Solo ejecuta `SELECT 1`.

## Qué NO se implementó

- No se ejecutó SQL de Cotizaciones.
- No se crearon tablas.
- No se modificó base de datos.
- No se modificó `cotizaciones.php`.
- No se modificó `InternalPage`.
- No se modificaron login, logout, `AuthGuard`, `SessionManager` ni timeout.
- No se creó CRUD.
- No se crearon formularios.
- No se implementó `POST`.
- No se crearon repositories.
- No se crearon services.
- No se crearon controllers.
- No se expusieron credenciales.

## Pruebas realizadas

Se validó la sintaxis PHP del archivo creado:

```bash
php -l sistema/tools/check-db-connection.php
```

No se ejecutó la herramienta porque depende de `sistema/config/database.php` real y del entorno de base de datos donde corresponda probar.

## Próxima etapa recomendada

La siguiente etapa recomendada es definir una estrategia controlada para ejecutar el SQL inicial en un entorno preparado, con respaldo previo y sin conectar todavía el módulo Cotizaciones a CRUD.
