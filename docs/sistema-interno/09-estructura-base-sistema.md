# Estructura base del sistema interno

## Objetivo de esta etapa

Crear la estructura inicial del sistema interno privado de D&A Systems sin implementar todavía funcionalidad de negocio. Esta etapa busca separar claramente el sistema administrativo del sitio público, construir una base segura y dejar listos los espacios de trabajo para autenticación, base de datos, clientes, cotizaciones y atenciones.

## Estructura creada

Se creó la siguiente estructura en el directorio `/sistema/`:

- `/sistema/.htaccess`
- `/sistema/public/`
  - `index.php`
  - `.htaccess`
- `/sistema/app/`
  - `.htaccess`
  - `Core/`
  - `Domain/`
  - `Application/`
  - `Infrastructure/`
  - `Http/`
  - `Views/`
- `/sistema/config/`
  - `.htaccess`
- `/sistema/storage/`
  - `.htaccess`
  - `logs/`
  - `quotes/`
  - `reports/`
- `/sistema/database/`
  - `.htaccess`
  - `migrations/`
  - `seeders/`

Se agregaron archivos `.gitkeep` en carpetas vacías necesarias para versionar la estructura base.

## Separación entre sitio público y sistema interno

- El sitio público existente no se modifica ni se mezcla con el sistema interno.
- El sistema interno queda aislado en `/sistema/` para mantener un perímetro administrativo independiente.
- Solo el contenido de `/sistema/public/` está pensado para ser expuesto vía web, mientras que `app/`, `config/`, `storage/` y `database/` quedan protegidos.

## Por qué app/config/storage/database no deben ser públicos

- `app/`: contiene lógica de aplicación, controladores, entidades y clases. El acceso directo podría filtrar código y rutas internas.
- `config/`: guarda parámetros de configuración sensibles (bases de datos, credenciales, tokens). No debe exponerse.
- `storage/`: aloja archivos generados, logs y datos temporales. Debe estar protegido para evitar fuga de información interna.
- `database/`: contiene migraciones y seeding. No debe ser accesible directamente por la web.

## Consideraciones para cPanel

- La estructura está diseñada para que en cPanel la carpeta `/sistema/public/` pueda actuar como webroot del sistema interno.
- Los archivos `.htaccess` protegen carpetas no públicas y evitan acceso directo desde el navegador.
- Se evita el uso de Node.js y se mantiene compatibilidad con PHP 8.3 y Composer.
- El código inicial de `public/index.php` es un placeholder seguro y no realiza conexiones a bases de datos ni inicia login real.

## Próximos pasos

- **Etapa 6C: Autenticación segura**
  - Implementar login/logout, sesiones seguras y control de roles.
- **Etapa 6D: Base de datos**
  - Definir tablas, migraciones, conexión PDO y repositorios.
- **Etapa 6E: Clientes**
  - Crear CRUD de clientes, búsqueda y relación con cotizaciones.
- **Etapa 6F: Cotizaciones**
  - Desarrollar creación de cotizaciones, gestión de ítems, cálculo de totales y emisión de PDF.
