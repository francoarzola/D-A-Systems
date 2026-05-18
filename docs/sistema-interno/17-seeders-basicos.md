# Seeders básicos

## Objetivo de la etapa

Crear seeders SQL básicos para insertar datos iniciales no sensibles en el sistema interno privado de D&A Systems. Esta etapa prepara valores base para estados de cotización y configuraciones generales sin incluir datos personales, credenciales ni usuarios reales.

## Archivos creados

- `sistema/database/seeders/001_seed_quote_statuses.sql`
- `sistema/database/seeders/002_seed_system_settings.sql`

## Qué datos se insertan

- En `quote_statuses` se insertan los estados base:
  - `borrador`
  - `emitida`
  - `enviada`
  - `aceptada`
  - `rechazada`
  - `anulada`
- En `system_settings` se insertan configuraciones iniciales no sensibles:
  - `company_name = D&A Systems`
  - `default_currency = CLP`
  - `default_tax_rate = 19.00`
  - `default_quote_validity_days = 15`
  - `quote_number_prefix = COT`
  - `quote_number_format = COT-YYYY-0001`

## Por qué no se crean usuarios reales todavía

- La creación de usuarios reales implica credenciales y contraseñas que deben manejarse con cuidado.
- La autenticación real y la tabla `users` se definirán en etapas posteriores cuando la configuración segura de base de datos esté disponible.

## Por qué no se insertan clientes reales

- Los clientes son datos sensibles y personales que no corresponden a una etapa de seeders inicial.
- El enfoque actual es preparar la estructura y los valores comunes sin introducir información real.

## Orden recomendado de ejecución

1. Ejecutar las migraciones.
2. Ejecutar los seeders.

## Cómo ejecutar desde phpMyAdmin

1. Abrir phpMyAdmin desde cPanel.
2. Seleccionar la base de datos del sistema interno.
3. Ejecutar primero las migraciones en `sistema/database/migrations/`.
4. Ejecutar después los seeders en `sistema/database/seeders/`.
5. Verificar que los registros base se insertan sin errores.

## Advertencia de backup previo

- Hacer un respaldo completo de la base de datos antes de ejecutar seeders.
- Exportar la estructura y datos existentes si aplica.
- No ejecutar seeders en un entorno en producción sin respaldo.

## Criterios de seguridad

- No se insertan datos personales.
- No se insertan secretos.
- No se insertan contraseñas.
- No se insertan credenciales.

## Próximas etapas

- **Etapa 6D.4:** configuración segura de base de datos sin credenciales reales.
- **Etapa 6D.5:** conexión PDO.
- **Etapa 6D.6:** creación controlada de usuario admin inicial.
- **Etapa 6D.7:** autenticación real contra la tabla `users`.
