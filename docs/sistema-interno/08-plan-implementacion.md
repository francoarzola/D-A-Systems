# Plan de implementación

## Etapas del proyecto

### Etapa 6B: Estructura base del sistema interno

- Crear carpeta `/sistema/` separada del sitio público.
- Definir estructura de carpetas `app/`, `public/`, `Config/`, `Storage/`, `vendor/`.
- Configurar Composer y autoload PSR-4.
- Crear front controller en `public/index.php`.
- Instalar Bootstrap 5 en `public/assets/`.
- Crear base de layout y vistas iniciales.
- Configurar `.htaccess` básico en `public/`.

### Etapa 6C: Autenticación

- Crear tabla `users` y migración básica.
- Implementar login/logout con sesiones seguras.
- Implementar middleware de autenticación.
- Agregar control de roles `admin` y `usuario`.
- Implementar rate limit de login y bloqueo de IP/usuario.
- Crear vista de login y panel de bienvenida.

### Etapa 6D: Base de datos y migraciones

- Crear tablas iniciales: `users`, `clients`, `quote_statuses`, `quotes`, `quote_items`, `service_reports`, `audit_logs`, `login_attempts`.
- Implementar clases de repositorio e interfaces.
- Crear configuración de conexión PDO para MySQL/MariaDB.
- Cargar datos iniciales de `quote_statuses`.
- Verificar integridad referencial y claves foráneas.

### Etapa 6E: Clientes

- Implementar CRUD de clientes.
- Crear vistas de listado, creación, edición y detalle.
- Implementar búsqueda y filtros básicos.
- Validar backend de datos de cliente.
- Registrar auditoría en creación/edición/desactivación.
- Controlar `is_active` y desactivación segura.

### Etapa 6F: Cotizaciones

- Implementar flujo de cotización.
- Crear formulario para seleccionar cliente y definir datos generales.
- Implementar gestión de ítems de cotización.
- Calcular subtotal, IVA y total en backend.
- Guardar cotización como borrador y emitir.
- Implementar gestión de estados y listado de cotizaciones.
- Registrar tasas de auditoría.

### Etapa 6G: Generación de PDF

- Integrar Dompdf o mPDF con Composer.
- Crear plantilla de cotización en HTML/CSS.
- Generar PDF sólo para cotizaciones emitidas.
- Servir descarga mediante controlador autenticado.
- Probar generación y descarga segura.

### Etapa 6H: Atenciones técnicas

- Implementar módulo de atenciones.
- Crear formulario de registro con diagnóstico, trabajo realizado y recomendaciones.
- Asociar cada atención a un cliente y usuario.
- Crear listado con filtros por cliente, estado y fecha.
- Registrar auditoría en creación y actualización.
- Preparar la base para un informe PDF futuro.

### Etapa 6I: Pruebas y despliegue en cPanel

- Probar flujos críticos: login, clientes, cotizaciones, PDF, atenciones.
- Validar login, sesiones, CSRF y validación backend.
- Verificar permisos de carpetas `Storage/` y `vendor/`.
- Configurar dominio o subdirectorio en cPanel.
- Subir código y dependencias con Composer.
- Probar conexión MySQL/MariaDB en cPanel.
- Validar que no se modifica el sitio público.
- Documentar pasos para despliegue y prueba.

## Prioridades

1. Autenticación segura.
2. Base de datos y estructura de clases.
3. Módulo de cotizaciones.
4. Generación de PDF.
5. Gestión de clientes.
6. Registro de atenciones.
7. Despliegue y pruebas en cPanel.

## Notas de implementación

- Mantener cada etapa pequeña y verificable.
- Preferir un MVP funcional antes que una implementación completa de todas las mejoras.
- Documentar decisiones técnicas en cada entrega.
- Evitar sobrecargar el MVP con funcionalidades no críticas.
