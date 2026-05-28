# 85 — Normalización visual base de la intranet interna

## 1. Objetivo de la etapa

La etapa 7B.02 aplica una normalización visual base de bajo riesgo para que la intranet interna se vea más consistente, sobria y corporativa.

El trabajo parte por estilos generales y por el login, sin cambiar lógica de negocio ni flujos funcionales.

## 2. Alcance visual aplicado

El alcance se limitó a:

- estilos base en `internal.css`;
- botón funcional del login;
- reducción de estilos inline simples en `login.php`;
- contrato CLI de verificación;
- documentación de la etapa.

No se rediseñaron pantallas de cotizaciones ni se modificaron flujos funcionales.

## 3. Archivos modificados

- `sistema/public/assets/css/internal.css`.
- `sistema/public/login.php`.

## 4. Cambios realizados en internal.css

Se agregaron estilos para:

- `.button-primary`, como botón funcional primario de la intranet;
- estados `hover` y `focus-visible` del botón primario;
- `.auth-card`, para separar el formulario de login sin estilo inline;
- `.auth-field-spaced`, para espaciado vertical del campo de contraseña.

Se mantuvieron las clases existentes, incluyendo `button-disabled`, para no romper usos previos o referencias visuales.

## 5. Cambios realizados en login.php

El formulario mantiene:

- `method="post"`;
- `action="login.php"`;
- `name="email"`;
- `name="password"`;
- `csrf_token`;
- botón `type="submit"`;
- lógica PHP de autenticación intacta.

Cambios visuales:

- el formulario usa `class="card auth-card"` en lugar de estilo inline;
- la etiqueta de contraseña usa `class="auth-field-spaced"`;
- el contenedor del botón ya no usa estilo inline;
- el botón funcional cambió de `button-disabled` a `button-primary`.

## 6. Por qué se corrigió button-disabled

`button-disabled` comunica visualmente una acción deshabilitada. En el login, el botón `Acceder` es funcional y envía el formulario. Usar una clase llamada `button-disabled` podía confundir tanto a usuarios como a futuras mantenciones.

La nueva clase `button-primary` expresa que se trata de la acción principal de la pantalla.

## 7. Qué se mantuvo intacto

Se mantuvo intacto:

- autenticación;
- sesión;
- CSRF;
- rutas;
- nombres de inputs;
- método y acción del formulario;
- cotizaciones;
- PDF;
- emisión.

## 8. Criterios visuales usados

- Estilo sobrio.
- Apariencia de intranet corporativa TI.
- Buen contraste.
- Botones claros.
- Tarjetas limpias.
- Formularios legibles.
- Diseño liviano y operativo.
- Sin estética gamer.
- Sin estética de landing page.
- Sin sobrediseñar.

## 9. Riesgos controlados

- No se cambió lógica PHP.
- No se tocaron validaciones del login.
- No se modificó base de datos.
- No se modificaron pantallas de cotizaciones.
- No se modificó PDF ni emisión.
- No se agregaron dependencias ni JavaScript.

## 10. Qué NO se implementó

- No se implementó rediseño completo.
- No se implementaron cambios de lógica.
- No se modificó base de datos.
- No se modificó cotizaciones.
- No se modificó PDF.
- No se modificó emisión.
- No se implementó AJAX ni API JSON.

## 11. Herramienta CLI creada

Se creó `sistema/tools/check-intranet-base-visual-contract.php`.

La herramienta verifica:

- que el login conserva formulario POST, CSRF, email, password y submit;
- que el botón submit no usa `button-disabled`;
- que `internal.css` contiene estilos base esperados;
- que no se introdujo AJAX, API JSON ni correo;
- que no se crearon CSS o JS nuevos fuera del alcance.

## 12. Cómo ejecutar

```bash
php sistema/tools/check-intranet-base-visual-contract.php
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-intranet-base-visual-contract.php
```

## 13. Prueba manual recomendada

1. Abrir `login.php`.
2. Confirmar que el botón se ve funcional y no deshabilitado.
3. Iniciar sesión.
4. Confirmar que redirige correctamente.
5. Abrir `cotizaciones.php` y confirmar que no se rompió visualmente.

## 14. Próxima etapa recomendada

7B.03 — Header y navegación interna.
