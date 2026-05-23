# Etapa 7A.2 — Conexión PDO segura para el sistema interno

## Objetivo

Preparar la infraestructura mínima y segura de conexión a base de datos usando PDO para el sistema interno, sin implementar consultas de cotizaciones, CRUD, formularios, `POST` ni ejecución automática de SQL.

## Revisión previa

Antes de crear archivos nuevos se revisó el proyecto y ya existe infraestructura de conexión PDO:

- `sistema/app/Infrastructure/Database/Connection.php`
- `sistema/app/Infrastructure/Config/DatabaseConfig.php`
- `sistema/config/database.example.php`
- `.gitignore`

Por esta razón no se crea una segunda clase en `sistema/app/Core/Database.php`. Duplicar la conexión agregaría dos caminos para abrir PDO y haría más difícil mantener seguridad, validación y configuración.

La integración mínima recomendada es reutilizar las clases existentes:

- `DatabaseConfig` carga `sistema/config/database.php`.
- `Connection` recibe el arreglo de configuración y entrega una instancia `PDO`.

## Archivos existentes reutilizados

### sistema/app/Infrastructure/Database/Connection.php

Responsabilidades:

- Recibir configuración por constructor.
- Validar datos mínimos.
- Construir DSN MySQL.
- Usar `charset=utf8mb4`.
- Activar excepciones de PDO.
- Usar `PDO::FETCH_ASSOC` por defecto.
- Desactivar emulación de prepares con `PDO::ATTR_EMULATE_PREPARES => false`.
- Reutilizar la misma instancia `PDO` dentro del objeto.

### sistema/app/Infrastructure/Config/DatabaseConfig.php

Responsabilidades:

- Cargar el archivo real no versionado `sistema/config/database.php`.
- Verificar que exista y sea legible.
- Verificar que retorne un arreglo.
- Validar claves mínimas de configuración.

### sistema/config/database.example.php

Archivo versionado de ejemplo, sin secretos reales.

Define la estructura esperada:

- `host`
- `port`
- `database`
- `username`
- `password`
- `charset`
- `collation`
- `options`

### .gitignore

Ya excluye:

- `sistema/config/database.php`
- `sistema/config/database.local.php`
- `sistema/config/*.local.php`

Por lo tanto no fue necesario modificar `.gitignore` en esta etapa.

## Cómo crear database.php real en hosting

En el hosting o ambiente local:

1. Copiar `sistema/config/database.example.php`.
2. Crear `sistema/config/database.php`.
3. Reemplazar los valores de ejemplo por credenciales reales del hosting.
4. Mantener `charset` como `utf8mb4`.
5. Mantener opciones PDO seguras.
6. No subir `database.php` a GitHub.

Ejemplo conceptual:

```php
<?php

declare(strict_types=1);

return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'NOMBRE_REAL_BASE_DATOS',
    'username' => 'USUARIO_REAL',
    'password' => 'PASSWORD_REAL',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];
```

El archivo real debe crearse manualmente en el entorno donde se ejecutará el sistema.

## Uso futuro esperado

Cuando una etapa posterior necesite consultar base de datos:

```php
$config = DatabaseConfig::fromDefaultPath()->load();
$pdo = (new Connection($config))->pdo();
```

Ese uso debe quedar dentro de scripts controlados, páginas específicas o repositorios futuros. Esta etapa no conecta automáticamente páginas existentes.

## Seguridad

- No versionar credenciales reales.
- No mostrar detalles internos de conexión al usuario.
- Usar excepciones PDO para manejar fallos controladamente.
- Usar prepared statements en consultas futuras.
- No usar usuario `root` en producción.
- Limitar permisos del usuario MySQL al mínimo necesario.
- Mantener `utf8mb4` para evitar problemas de codificación.

## Qué NO se implementó

- No se creó una nueva clase duplicada de conexión.
- No se creó base de datos.
- No se ejecutó SQL.
- No se modificaron tablas.
- No se modificó PHP funcional.
- No se modificó CSS.
- No se conectó `cotizaciones.php`.
- No se modificó `InternalPage`.
- No se modificó login, logout, `AuthGuard`, `SessionManager` ni timeout.
- No se crearon repositories.
- No se crearon services.
- No se crearon controllers.
- No se creó CRUD.
- No se crearon formularios funcionales.
- No se implementó `POST`.
- No se agregaron frameworks ni dependencias externas.

## Pruebas recomendadas

Como no se modificaron archivos PHP en esta etapa, no se requiere validar sintaxis nueva.

Para verificar la infraestructura existente:

```bash
php -l sistema/app/Infrastructure/Database/Connection.php
php -l sistema/app/Infrastructure/Config/DatabaseConfig.php
php -l sistema/config/database.example.php
```

## Próxima etapa recomendada

La siguiente etapa recomendada es preparar una prueba controlada y no pública de conexión, usando `DatabaseConfig` y `Connection`, sin ejecutar SQL de cotizaciones ni implementar CRUD.
