# 86 — Header y navegación interna

## 1. Objetivo de la etapa

La etapa 7B.03 agrega una navegación interna sobria y consistente para pantallas autenticadas principales de cotizaciones, sin modificar lógica funcional.

El objetivo es reforzar la sensación de intranet o panel interno y reducir la percepción de pantallas aisladas.

## 2. Pantallas ajustadas

Se agregó navegación contextual en:

- `sistema/public/cotizaciones.php`.
- `sistema/public/cotizacion-detalle.php`.
- `sistema/public/cotizacion-editar.php`.

## 3. Qué se agregó al header/navegación

Se agregó un bloque visual con:

- marca `D&A Systems`;
- subtítulo `Sistema interno`;
- enlace `Cotizaciones`;
- enlace `Crear borrador`;
- enlace `Cerrar sesión`, usando el `logout.php` existente.

Los enlaces usan rutas existentes. No se creó ningún endpoint nuevo.

## 4. Clases CSS creadas o normalizadas

En `internal.css` se agregaron:

- `.internal-page-shell`;
- `.internal-topbar`;
- `.internal-topbar-brand`;
- `.internal-topbar-title`;
- `.internal-topbar-subtitle`;
- `.internal-nav`;
- `.internal-nav-link`;
- `.internal-nav-link-active`;
- `.internal-page-heading`.

También se agregó comportamiento responsive básico para que la navegación pueda envolver en pantallas pequeñas.

## 5. Decisiones visuales

- Header sobrio y operativo.
- Navegación tipo intranet corporativa TI.
- Sin estética gamer.
- Sin estética de landing page.
- Sin rediseño completo de cotizaciones.
- Sin eliminar clases existentes.
- Sin cambiar formularios ni acciones.

## 6. Por qué no se tocó login.php

El login es una pantalla pública previa a la autenticación. La navegación interna solo debe aparecer en pantallas protegidas, por eso `login.php` no se modificó.

## 7. Por qué no se tocó cotizacion-pdf.php

`cotizacion-pdf.php` entrega una descarga binaria PDF autenticada. No debe incluir navegación visual ni HTML de topbar en respuestas exitosas.

## 8. Por qué no se tocó cotizacion-imprimir.php

`cotizacion-imprimir.php` tiene estructura documental propia para impresión. Agregar navegación interna podría contaminar la vista imprimible o afectar impresión.

## 9. Qué se mantuvo intacto

Se mantuvo intacto:

- autenticación;
- sesión;
- CSRF;
- formularios;
- cotizaciones;
- emisión;
- PDF;
- base de datos.

## 10. Riesgos controlados

- No se modificaron servicios ni repositorios.
- No se modificaron endpoints de emisión ni PDF.
- No se crearon rutas nuevas.
- No se agregó JavaScript.
- No se modificó Composer ni `vendor/`.
- Los enlaces usan rutas ya existentes.

## 11. Qué NO se implementó

- No se implementó rediseño completo.
- No se implementó dashboard.
- No se implementó menú dinámico.
- No se crearon nuevos endpoints.
- No se hicieron cambios de lógica.
- No se implementó AJAX ni API JSON.

## 12. Herramienta CLI creada

Se creó `sistema/tools/check-intranet-header-navigation-contract.php`.

La herramienta valida:

- presencia de clases de navegación en `internal.css`;
- presencia de topbar en las tres pantallas internas de cotizaciones;
- ausencia de topbar en `login.php`;
- ausencia de topbar en `cotizacion-pdf.php`;
- ausencia de topbar en `cotizacion-imprimir.php`;
- ausencia de AJAX, API JSON y correo;
- ausencia de CSS o JS nuevos fuera del alcance.

## 13. Cómo ejecutar

```bash
php sistema/tools/check-intranet-header-navigation-contract.php
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-intranet-header-navigation-contract.php
```

## 14. Prueba manual recomendada

1. Iniciar sesión.
2. Abrir `cotizaciones.php`.
3. Abrir `cotizacion-detalle.php?id=3`.
4. Abrir `cotizacion-editar.php?id=1` o un borrador existente.
5. Confirmar navegación interna consistente.
6. Confirmar que PDF, emisión, edición y listado siguen funcionando.

## 15. Próxima etapa recomendada

7B.04 — Orden visual del listado de cotizaciones.
