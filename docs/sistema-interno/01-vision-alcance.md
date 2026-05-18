# Visión y Alcance

## Objetivo del sistema interno

Diseñar un sistema interno privado para D&A Systems que permita gestionar clientes, generar cotizaciones profesionales y registrar atenciones técnicas de manera segura y eficiente. El sistema debe ser una herramienta administrativa para el equipo interno, separada del sitio público, con capacidad de crecimiento y mejora continua.

## Módulos iniciales

1. Gestión de clientes
2. Cotizaciones
3. Atenciones técnicas / informes de atención
4. Autenticación y control de acceso
5. Administración de usuarios y roles

## Alcance MVP

Incluye:
- CRUD básico de clientes
- CRUD de cotizaciones con estado, cálculo de impuestos, borrador y emisión
- Generación de PDF para cotizaciones
- Registro de atenciones técnicas con diagnóstico, trabajo realizado y recomendaciones
- Autenticación segura de usuarios con roles mínimos
- Separación del sistema interno respecto al sitio público
- Documentación técnica y estructura de carpetas compatible con cPanel

No incluye en esta primera versión:
- Integración con pasarelas de pago
- Portal de clientes externo
- Automatización de ventas o pipeline comercial completo
- Integración con ERP externo
- Multi-tenant o separación por empresa independiente
- Gestión avanzada de stock / inventario

## Exclusiones claras

- No modificar el sitio público existente.
- No usar Node.js ni Laravel.
- No habilitar administración pública del contenido del sitio web.
- No crear módulos financieros complejos en el MVP.
- No incluir servicio de correo transaccional masivo en fase inicial.

## Criterios de éxito

- Módulo de cotizaciones funcional y usable por al menos 2 usuarios.
- Almacenamiento seguro de clientes y cotizaciones en MySQL/MariaDB.
- Emisión de cotizaciones en PDF descargables dentro del área autenticada.
- Auditoría mínima de operaciones sensibles (login, cotizaciones y atenciones).
- Estructura compatible con cPanel y sin dependencia de Node.js.
- Documentación técnica lista para implementación posterior.

## Riesgos identificados

- Configuración de cPanel limita el uso de algunas funciones CLI.
- Falta de experiencia previa con PHP 8.3 y Composer en el hosting actual.
- Seguridad en el manejo de archivos PDF y directorios protegidos.
- Alto acoplamiento si no se diseña una arquitectura limpia desde el inicio.
- Escalabilidad limitada si se construye con lógica demasiado monolítica.

## Priorización

Primer foco: módulo de Cotizaciones.
Segundo foco: Gestión de Clientes.
Tercer foco: Registro de Atenciones Técnicas.

La primera entrega debe ser una versión operativa reducida, clara y fácil de mantener.