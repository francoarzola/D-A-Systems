# 73 - Configuración centralizada de datos comerciales para cotizaciones

## Objetivo

Centralizar los datos comerciales de D&A Systems que se muestran en cotizaciones, especialmente en la vista imprimible HTML, para que futuras etapas de PDF o correo usen una única fuente de información.

## Archivo `company.php`

Se creó `sistema/config/company.php` como configuración simple que retorna un array PHP con los datos comerciales base:

- nombre comercial
- razón social
- RUT
- giro
- dirección
- ciudad
- país
- correo
- teléfono
- sitio web
- condiciones de pago por defecto
- nota de pie
- nota de validez comercial

Los datos tributarios o de contacto que aún no están confirmados se dejan como `Pendiente`. No se inventan RUT, dirección ni teléfono reales.

## Clase `CompanyProfile`

Se creó `sistema/app/Support/CompanyProfile.php` para cargar y normalizar la configuración comercial.

La clase entrega estos métodos:

- `all()`
- `commercialName()`
- `legalName()`
- `taxId()`
- `businessActivity()`
- `address()`
- `city()`
- `country()`
- `email()`
- `phone()`
- `website()`
- `defaultPaymentTerms()`
- `defaultFooterNote()`
- `quoteValidityNote()`

Si un valor falta, viene vacío o no es texto simple, se normaliza como `Pendiente`.

## Uso en vista imprimible

`sistema/public/cotizacion-imprimir.php` ahora usa `CompanyProfile` para mostrar:

- nombre comercial en el encabezado
- razón social
- RUT
- giro
- dirección, ciudad y país
- correo
- teléfono
- sitio web
- condiciones de pago por defecto
- nota comercial de validez
- nota de pie del documento

Toda salida se mantiene escapada con `ViewFormatter::e()`.

## Por qué no se usa base de datos todavía

En esta etapa los datos comerciales son configuración estable del sistema, no contenido administrable. Se deja preparado como archivo versionado y fácil de reemplazar cuando estén confirmados los datos reales.

Una etapa futura podría mover estos datos a una pantalla de configuración si el sistema necesita administración desde navegador.

## Datos pendientes

Quedan pendientes de confirmar:

- RUT real de la empresa
- dirección comercial real
- teléfono comercial real
- sitio web definitivo
- condiciones de pago por defecto definitivas

## Qué NO se implementó

No se modificó base de datos, no se ejecutó SQL, no se cambió la emisión, no se generó PDF, no se envió correo, no se creó AJAX ni API JSON, y no se implementaron cambios de estado.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-company-profile-contract.php
```

Comando Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-company-profile-contract.php
```

La herramienta verifica que:

- `company.php` exista y retorne un array.
- `CompanyProfile` exponga los métodos principales.
- la vista imprimible use `CompanyProfile`.
- no se agreguen operaciones de escritura, correo, PDF, AJAX ni API JSON.

## Prueba manual recomendada

1. Abrir una cotización emitida.
2. Entrar a la vista imprimible.
3. Verificar los datos comerciales de empresa.
4. Verificar número, cliente, detalles y totales.
5. Presionar imprimir y confirmar que la vista se mantiene limpia para impresión.

## Próxima etapa recomendada

Usar esta configuración centralizada como base para una futura generación de PDF o para preparar el envío controlado por correo, sin duplicar datos comerciales en cada vista.
