# Usuario administrador inicial

## Objetivo de la etapa

Crear un script privado y controlado para generar el primer usuario administrador del sistema interno en la tabla `users`, usando la conexión PDO existente y sin guardar contraseñas planas en Git.

## Archivo creado

- `sistema/tools/create-admin-user.php`

## Por qué el script está en `sistema/tools` y no en `sistema/public`

El script es de uso privado y debe ejecutarse solo desde línea de comandos. No pertenece a la capa pública del sitio ni a un endpoint web, por lo que se coloca fuera de `sistema/public`.

## Por qué se ejecuta por CLI

Ejecutar el script por CLI evita la exposición en un navegador y reduce la superficie de ataque. Además permite gestionar credenciales de forma privada y controlada.

## Ejecución local con Laragon

```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" sistema/tools/create-admin-user.php "Administrador" "correo@dominio.cl"
```

El script solicitará la contraseña de forma interactiva para que no quede registrada en el historial de la terminal.

## Requisitos previos

- `sistema/config/database.php` creado localmente y no versionado.
- Migraciones ejecutadas para tener la tabla `users` disponible.
- Conexión a la base de datos probada correctamente.

## Reglas de contraseña

- Mínimo 12 caracteres.
- Al menos una letra minúscula.
- Al menos una letra mayúscula.
- Al menos un número.
- Al menos un símbolo.

## Advertencias de seguridad

- No subir contraseñas ni hashes a GitHub.
- No hardcodear contraseñas ni datos sensibles en el repositorio.
- El script no imprime la contraseña recibida ni el hash generado.

## Cómo verificar en HeidiSQL

1. Abrir la base de datos local.
2. Navegar a la tabla `users`.
3. Buscar el registro con el email usado.
4. Verificar que el usuario existe con `role = 'admin'` y `active = 1`.
5. No hay contraseña plana visible en la tabla, solo `password_hash`.

## Próximas etapas

- 6D.10 conectar login real contra `users`.
- 6D.11 registrar `login_attempts`.
- 6D.12 proteger `dashboard` con `AuthGuard`.
