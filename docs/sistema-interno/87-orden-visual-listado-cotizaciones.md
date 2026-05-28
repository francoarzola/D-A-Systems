# 87 - Orden visual del listado de cotizaciones

## 1. Objetivo de la etapa

La etapa 7B.04 ordena visualmente `cotizaciones.php` para que funcione como un panel operativo de cotizaciones. El cambio prioriza el listado real, separa la creacion de borradores y reduce la sensacion de maqueta sin cambiar logica de negocio.

## 2. Problema detectado en 7B.01

El diagnostico visual detecto que `cotizaciones.php` mezclaba formulario real, listado real y secciones estaticas de referencia. Aunque el flujo funcionaba, la jerarquia visual hacia dificil distinguir que era operativo y que era demostrativo.

## 3. Que se ordeno en cotizaciones.php

Se mantuvo la navegacion superior interna creada en 7B.03. Debajo se agrego un encabezado operativo con titulo, descripcion breve y acceso visual a crear borrador.

El listado real de cotizaciones se ubico antes del formulario. La creacion de borrador quedo como bloque secundario separado con `id="crear-borrador"`.

Las secciones estaticas de referencia fueron retiradas porque ya no aportaban al flujo real y reforzaban una percepcion de prototipo.

## 4. Jerarquia visual resultante

1. Topbar interna.
2. Encabezado operativo de cotizaciones.
3. Mensajes flash o errores generales.
4. Tarjetas resumen.
5. Listado real de cotizaciones.
6. Formulario real para crear borrador.

## 5. Funcionalidad mantenida intacta

- Formulario de creacion de borrador.
- Metodo `post`, `action`, CSRF y nombres de campos.
- Repoblado temporal del formulario.
- Errores por campo.
- Listado real desde base de datos.
- Acciones por fila: ver detalle y editar borrador cuando corresponde.
- Mensajes flash.
- Escape de salida con `ViewFormatter::e()`.

## 6. Cambios en internal.css

Se agregaron clases de orden visual:

- `quotes-page-heading`
- `quotes-eyebrow`
- `quotes-summary-grid`
- `quotes-list-section`
- `quote-create-section`
- `quotes-section-header`

Estas clases ordenan el encabezado, destacan el listado y separan el bloque de creacion sin redisenar todo el modulo.

## 7. Decisiones visuales

El listado queda como contenido principal porque es la operacion mas recurrente. El formulario sigue disponible en la misma pantalla, pero con menor prioridad visual. La estetica se mantiene sobria, clara y propia de una intranet corporativa TI.

## 8. Riesgos controlados

No se tocaron servicios, repositorios, SQL, autenticacion, sesion ni CSRF. El formulario conserva su estructura funcional y el listado conserva sus datos y acciones. No se agrego JavaScript.

## 9. Que NO se implemento

- No rediseño completo.
- No dashboard nuevo.
- No cambios de logica.
- No cambios en base de datos.
- No cambios en servicios o repositorios.
- No cambios en PDF.
- No cambios en emision.
- No AJAX ni API JSON.

## 10. Herramienta CLI creada

Se creo:

```bash
php sistema/tools/check-quotes-list-visual-order-contract.php
```

La herramienta valida que el listado real, formulario, CSRF, acciones y clases visuales sigan presentes, y que no se hayan introducido llamadas AJAX, API JSON, correo ni escrituras de archivos.

## 11. Comando Laragon

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quotes-list-visual-order-contract.php
```

## 12. Prueba manual recomendada

1. Iniciar sesion.
2. Abrir `cotizaciones.php`.
3. Verificar que el listado real aparece claro y antes del formulario.
4. Verificar que Crear borrador esta separado.
5. Crear un borrador de prueba si corresponde.
6. Abrir detalle desde una fila.
7. Editar un borrador desde una fila.
8. Confirmar que la navegacion superior sigue funcionando.

## 13. Proxima etapa recomendada

7B.05 - Orden visual del detalle de cotizacion.
