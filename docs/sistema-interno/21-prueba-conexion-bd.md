# Prueba de conexión a la base de datos

## Objetivo de la etapa

Crear un script privado y controlado para probar la conexión a la base de datos del sistema interno, usando `DatabaseConfig` y `Connection`, sin exponer credenciales y sin tocar el sitio público.

## Archivos creados

- `sistema/tools/test-db-connection.php`
- `sistema/tools/.htaccess`
- `docs/sistema-interno/21-prueba-conexion-bd.md`

## Por qué el script no está en `sistema/public`

El script es una herramienta de administración interna. No pertenece al frontend público y debe ejecutarse solo desde CLI para mantenerlo privado.

## Por qué se bloquea el acceso web

Se bloquea el acceso directo al directorio `sistema/tools` para evitar que alguien intente acceder desde un navegador o desde la web.`

## Por qué se ejecuta por CLI

- La prueba de conexión es una operación de administración interna.
- Evita exponer el script en el entorno web.
- Garantiza que la ejecución se realice en un contexto controlado.

## Requisitos previos

- Crear la base de datos real en cPanel.
- Crear un usuario MySQL para la aplicación.
- Asignar permisos mínimos a ese usuario.
- Copiar `sistema/config/database.example.php` como `sistema/config/database.php`.
- Completar las credenciales reales localmente o en hosting.
- No subir `sistema/config/database.php` a GitHub.

## Comando local sugerido

```bash
php sistema/tools/test-db-connection.php
```

## Comando con Laragon en Windows

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" sistema/tools/test-db-connection.php
```

## Resultados esperados

- `OK: conexión a base de datos verificada correctamente.`
- `ERROR: no fue posible verificar la conexión a base de datos.`

## Recomendaciones de seguridad

- No exponer credenciales.
- No versionar `sistema/config/database.php`.
- Usar contraseñas fuertes y exclusivas.
- Mantener el script dentro de `sistema/tools` y bloquearlo desde la web.

## Próximas etapas

- Etapa 6D.8: creación real de BD en cPanel.
- Etapa 6D.9: ejecución controlada de migraciones.
- Etapa 6D.10: creación de usuario admin inicial.
- Etapa 6D.11: login real contra `users`.
