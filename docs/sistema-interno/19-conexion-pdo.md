# Conexión PDO segura

## Objetivo de la etapa

Definir una clase de conexión PDO segura y reutilizable para el sistema interno privado, sin conectar aún el login ni ejecutar consultas reales.

## Archivo creado

- `sistema/app/Infrastructure/Database/Connection.php`

## Qué hace `Connection.php`

- Recibe configuración de base de datos a través del constructor.
- Valida los campos mínimos necesarios (`host`, `port`, `database`, `username`, `password`).
- Normaliza valores seguros por defecto como `charset` y `options`.
- Construye un DSN MySQL seguro.
- Devuelve una instancia `PDO` mediante el método `pdo()`.
- Reutiliza la misma instancia PDO internamente.

## Qué NO hace todavía

- No carga `database.php` ni otros archivos de configuración.
- No establece conexión de login.
- No ejecuta consultas.
- No crea tablas ni base de datos.
- No crea usuarios reales.

## Por qué no se conecta aún al login

La autenticación y el login se implementarán en etapas posteriores. Primero se debe asegurar la capa de conexión y la carga segura de configuración sin mezclar responsabilidades.

## Cómo se espera cargar configuración en etapa posterior

En una etapa posterior, la aplicación cargará `sistema/config/database.php` o `database.local.php` en un bootstrap seguro y pasará el arreglo de configuración a `Connection`.

Ejemplo conceptual de uso:

```php
$config = [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'usuariohosting_dasystems_internal',
    'username' => 'usuariohosting_dasystems_app',
    'password' => 'CAMBIAR_EN_CPANEL',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

$connection = new DAndASystems\Internal\Infrastructure\Database\Connection($config);
$pdo = $connection->pdo();
```

## Consideraciones de seguridad

- No exponer `password` ni otros datos sensibles en mensajes de error.
- No mostrar errores internos al usuario.
- Usar `prepared statements` en repositorios futuros.
- No usar el usuario `root` para la aplicación.
- No versionar `sistema/config/database.php`.

## Validación recomendada

Ejecutar:

```bash
php -l sistema/app/Infrastructure/Database/Connection.php
```

## Próximas etapas

- Etapa 6D.6: carga segura de configuración y prueba controlada de conexión.
- Etapa 6D.7: creación controlada de usuario admin inicial.
- Etapa 6D.8: login real contra `users`.
