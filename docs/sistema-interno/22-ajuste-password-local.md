# Ajuste de password vacío para ambiente local Laragon

## Objetivo del ajuste

Permitir que la validación de configuración de base de datos acepte una contraseña vacía (`''`) cuando el archivo `sistema/config/database.php` la define explícitamente. Esto es necesario para entornos de desarrollo local como Laragon, donde el usuario `root` puede ejecutar MySQL/MariaDB sin contraseña.

## Por qué Laragon puede usar root sin contraseña

Laragon es un entorno de desarrollo local que, por defecto, instala MySQL/MariaDB con acceso rápido para el usuario `root` sin contraseña. Esto simplifica la configuración local, pero no debe confundirse con una práctica segura para producción.

## Por qué producción / cPanel debe usar contraseña fuerte

En producción y en entornos cPanel, la base de datos está expuesta a riesgos reales de acceso no autorizado. Una contraseña fuerte protege los datos y dificulta ataques de fuerza bruta o acceso indebido. El ajuste solo permite `password` vacío en el código cuando el valor existe en el array de configuración.

## Diferencia entre permitir password vacío técnicamente y recomendarlo en producción

- Permitir password vacío técnicamente significa que el código acepta la clave `password` como parte de la configuración y no rechaza el valor `''`.
- Recomendarlo en producción es incorrecto. En producción siempre se debe usar una contraseña robusta. El ajuste es solo para entornos locales controlados.

## Archivos modificados

- `sistema/app/Infrastructure/Config/DatabaseConfig.php`
- `sistema/app/Infrastructure/Database/Connection.php`

## Pruebas recomendadas

Ejecutar:

```bash
php -l sistema/app/Infrastructure/Config/DatabaseConfig.php
php -l sistema/app/Infrastructure/Database/Connection.php
php sistema/tools/test-db-connection.php
```

### Resultado esperado

- `php -l ...` debe devolver `No syntax errors detected in ...`
- `php sistema/tools/test-db-connection.php` debe mostrar:
  - `OK: conexión a base de datos verificada correctamente.`

## Nota de seguridad

- `sistema/config/database.php` no está versionado y debe permanecer fuera del control de versiones.
- No subir credenciales reales al repositorio.
- No usar contraseña vacía en un hosting o entorno de producción.
