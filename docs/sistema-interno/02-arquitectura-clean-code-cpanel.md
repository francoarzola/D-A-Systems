# Arquitectura Clean Code para cPanel

## Principios de diseño

- Clean Architecture ligera
- Separación clara de responsabilidades
- Código mantenible y testable
- Compatibilidad con cPanel y PHP 8.3
- Uso de Composer para dependencias
- Bootstrap 5 para las vistas
- PDF con Dompdf o mPDF
- Sin Node.js, sin Laravel

## Estructura propuesta de carpetas

```
/docs/sistema-interno/   # documentación técnica
/sistema/                # raíz del sistema interno en el hosting
  app/
    Domain/
      Entities/
      ValueObjects/
      Interfaces/
    Application/
      UseCases/
      DTOs/
      Services/
    Infrastructure/
      Persistence/
      Repositories/
      Mail/
      Pdf/
      Security/
      Logger/
    Http/
      Controllers/
      Middleware/
      Requests/
      Responses/
    Views/
      layouts/
      quotes/
      clients/
      reports/
      auth/
    Config/
      database.php
      app.php
      auth.php
    Storage/
      logs/
      cache/
      pdfs/
    public/
      index.php
      assets/
        css/
        js/
        images/
  vendor/
  composer.json
```

> Nota: el sistema interno debe instalarse en un subdirectorio aislado `/sistema/` o en un dominio interno distinto, sin tocar el sitio público actual.

## Separación entre sitio público y sistema interno

- El sitio público permanece en la raíz principal del hosting.
- El sistema interno se aloja en `/sistema/` o en un subdominio privado.
- No compartir archivos de plantilla, configuración ni dependencias con el sitio público.
- El acceso al `/sistema/` debe requerir autenticación y no debe estar indexado por motores de búsqueda.

## Capas de Clean Architecture ligera

### Domain

Contiene entidades del negocio, reglas de dominio puras y contratos.
No debe depender de frameworks ni de infraestructura.

### Application

Orquesta casos de uso: gestionar cotizaciones, clientes y atenciones.
Define DTOs y servicios de aplicación.

### Infrastructure

Implementa repositorios, persistencia MySQL, envío de correo, generación de PDF y acceso a recursos externos.
Esta capa depende de la capa Application y Domain.

### Http/Controllers

Recibe peticiones, valida datos, invoca casos de uso y retorna vistas o JSON.
Permite desacoplar la lógica de negocio de la lógica HTTP.

### Views

Plantillas HTML con Bootstrap 5.
Usar un motor simple de plantillas basado en includes PHP o un micro-template.

### Config

Archivos PHP de configuración que retornan arrays.
Separar credenciales de base de datos, mail y rutas.

### Storage

Directorios de logs, archivos temporales y PDF generados.
Debe usarse con permisos controlados.

## Adaptación a cPanel

- Estructura de carpetas diseñada para trabajo en cPanel.
- Archivos públicos en `/sistema/public/`.
- Rutas amigables con rewrite a través de `.htaccess` en `/sistema/public/.htaccess`.
- Uso de PDO con MySQL/MariaDB, compatible con cPanel.
- Composer en raíz `/sistema/` para autoload y dependencias.
- Evitar binarios o servicios externos no disponibles en cPanel.
- Manejo de logs y PDF en carpetas no expuestas públicamente.

## Decisión técnica

- PHP 8.3: mayor compatibilidad con sintaxis moderna y seguridad.
- MySQL/MariaDB: motor estándar en cPanel.
- PDO: conexión segura y preparada para SQL.
- Bootstrap 5: UI responsiva sin necesidad de Node.js.
- Composer: autoload y librerías como Dompdf o mPDF.
- Dompdf/mPDF: generación de PDF en servidor.

## Recomendaciones de implementación

- Autoload PSR-4 con Composer.
- Separar configuraciones sensibles fuera del repositorio.
- Implementar rutas simples tipo front controller.
- Usar nomenclatura clara y archivos pequeños.
- Diseñar con pruebas unitarias parciales desde el inicio.
- Tratar la aplicación como un sistema administrativo autónomo.
