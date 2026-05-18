# Configuración segura de base de datos

## Objetivo de la etapa

Establecer la configuración base segura de la base de datos para el sistema interno privado de D&A Systems, sin credenciales reales y sin crear aún la conexión PDO.

## Archivos creados/modificados

- `sistema/config/database.example.php`
- `.gitignore`
- `docs/sistema-interno/18-configuracion-bd-segura.md`

## Diferencia entre `database.example.php` y `database.php`

- `database.example.php` es una plantilla de referencia segura que puede versionarse.
- `database.php` debe contener la configuración real de producción o desarrollo local con credenciales válidas.
- `database.php` no debe versionarse ni compartirse porque contiene secretos de conexión.

## Por qué `database.php` no debe versionarse

- Evita subir credenciales reales a GitHub.
- Reduce el riesgo de exposición accidental en repositorios públicos o privados.
- Permite que cada entorno tenga su propia configuración local segura.

## Cómo crear `database.php` manualmente en cPanel

1. Abrir el Administrador de archivos en cPanel o usar el editor de archivos.
2. Navegar a `sistema/config/`.
3. Crear un nuevo archivo llamado `database.php`.
4. Copiar el contenido de `database.example.php`.
5. Reemplazar los valores de `host`, `port`, `database`, `username` y `password` con los datos reales de cPanel.
6. Guardar el archivo y verificar que no se sube al repositorio.

## Recomendaciones para cPanel

- Crear la base de datos desde MySQL Databases o MySQL Database Wizard.
- Crear un usuario exclusivo para la aplicación.
- Asignar solo los privilegios mínimos necesarios para el sistema.
- Usar una contraseña fuerte y única.
- No usar la misma contraseña del cPanel.
- No subir claves o contraseñas a GitHub.

## Nombres sugeridos

- BD lógica: `dasystems_internal`
- Usuario lógico: `dasystems_app`
- En cPanel pueden quedar con prefijo, por ejemplo `usuariohosting_dasystems_internal`.

## Checklist de seguridad

- [ ] `database.example.php` versionado como plantilla sin credenciales reales.
- [ ] `database.php` excluido de Git.
- [ ] Contraseña fuerte y exclusiva.
- [ ] Usuario de base de datos exclusivo.
- [ ] Privilegios mínimos asignados.
- [ ] No usar contraseña de cPanel.
- [ ] No subir secretos al repositorio.

## Próximos pasos

- Etapa 6D.5: crear `Connection.php` con PDO.
- Etapa 6D.6: script controlado para probar conexión.
- Etapa 6D.7: crear usuario admin inicial de forma segura.
