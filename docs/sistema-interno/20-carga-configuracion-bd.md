# Carga segura de configuración de base de datos

## Objetivo de la etapa

Crear una clase responsable de cargar de forma segura la configuración real de base de datos desde un archivo no versionado, sin abrir conexión PDO todavía.

## Archivo creado

- `sistema/app/Infrastructure/Config/DatabaseConfig.php`

## Qué hace `DatabaseConfig`

- Recibe la ruta de un archivo de configuración en el constructor.
- Valida que el archivo exista y sea accesible.
- Incluye el archivo y valida que retorne un arreglo.
- Verifica que los campos mínimos estén presentes:
  - `host`
  - `port`
  - `database`
  - `username`
  - `password`
  - `charset`
- Permite `options` como arreglo opcional.
- Retorna la configuración validada.

## Qué NO hace todavía

- No abre conexión PDO.
- No ejecuta consultas.
- No crea tablas ni bases de datos.
- No carga credenciales reales desde el repositorio.
- No conecta el login.

## Por qué `database.php` no se versiona

- Contiene credenciales verdaderas de conexión.
- No debe subir secretos a GitHub.
- Permite que cada entorno mantenga su propia configuración local.

## Cómo crear `database.php` manualmente

1. Copiar `sistema/config/database.example.php` en `sistema/config/database.php`.
2. Reemplazar los valores de `host`, `port`, `database`, `username` y `password` por los valores reales de cPanel.
3. Mantener `charset` como `utf8mb4`.
4. No añadir el archivo al control de versiones.

## Qué datos debe completar el usuario en cPanel

- Nombre de la base de datos.
- Nombre de usuario de la base de datos.
- Contraseña fuerte y exclusiva para la aplicación.
- Host y puerto entregados por cPanel.

## Advertencia

- No subir credenciales a GitHub.
- No usar la misma contraseña del cPanel.
- No compartir `sistema/config/database.php` con otras personas.

## Validación recomendada

Ejecutar:

```bash
php -l sistema/app/Infrastructure/Config/DatabaseConfig.php
```

## Próxima etapa

- Etapa 6D.7: script privado de prueba de conexión.
- Etapa 6D.8: creación BD real en cPanel.
- Etapa 6D.9: usuario admin inicial.
- Etapa 6D.10: login real.
