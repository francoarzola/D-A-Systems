# Etapa 7A.8 - Datos de prueba controlados para Cotizaciones

## Objetivo

Crear una herramienta CLI segura para insertar una cotización de prueba en un entorno local o de prueba, con detalles asociados, para validar visualmente el listado real de cotizaciones.

Esta etapa ayuda a comprobar que `cotizaciones.php` ya puede mostrar datos reales desde la base, pero no implementa CRUD ni formularios.

## Archivo creado

`sistema/tools/create-sample-quote.php`

## Qué datos inserta

La herramienta inserta una cotización de prueba en estado `borrador`.

Datos principales:

- `numero_cotizacion`: `NULL`
- `estado`: `borrador`
- `fecha_cotizacion`: fecha actual
- `valido_hasta`: fecha actual + 30 días
- `nombre_cliente`: `Cliente de Prueba D&A Systems`
- `rut_cliente`: `76.000.000-0`
- `nombre_contacto`: `Andrea Pérez`
- `correo_contacto`: `andrea@example.test`
- `telefono_contacto`: `+56 9 0000 0000`
- `descripcion`: `Cotización de prueba para validar listado interno`
- `condiciones_comerciales`: `Valores de prueba, no usar comercialmente`
- `observaciones`: `Registro generado por herramienta CLI local`
- `creado_por`: `NULL`

También inserta dos detalles en `cotizacion_detalles`:

1. Servicio de soporte técnico mensual.
2. Implementación y configuración inicial.

## Cálculos realizados

El script recalcula los montos antes de insertar:

- `subtotal_linea_neto = cantidad * precio_unitario_neto`
- `total_linea_neto = subtotal_linea_neto - descuento_monto`
- `subtotal_neto = suma de total_linea_neto`
- `descuento_monto` de cabecera = `0`
- `iva_porcentaje = 19.00`
- `iva_monto = subtotal_neto * 0.19`
- `total = subtotal_neto + iva_monto`

La herramienta no genera número oficial de cotización porque el registro queda como borrador.

## Protección contra ejecución accidental

La herramienta exige el argumento explícito `--confirm-local`.

Si se ejecuta sin confirmación, no inserta datos y muestra:

```text
[ERROR] Esta herramienta solo debe ejecutarse en entorno local o de prueba. Usa --confirm-local para confirmar.
```

Esto reduce el riesgo de ejecutarla accidentalmente en un ambiente incorrecto.

## Validación dura de entorno

Además de `--confirm-local`, la herramienta valida la configuración cargada desde `sistema/config/database.php` antes de conectarse o insertar datos.

Solo permite continuar si:

- `host` es `localhost` o `127.0.0.1`.
- `database` es `dasystems_internal_local`, o contiene claramente `_local`, `_test` o `prueba`.

Si la configuración no cumple esas reglas, la herramienta se detiene y muestra:

```text
[ERROR] Entorno no permitido para insertar datos de prueba. Revise la configuración local.
```

Ese mensaje no muestra host, usuario, contraseña ni nombre completo de la base.

## Idempotencia

La herramienta protege la creación con un bloqueo nombrado de MySQL:

```sql
GET_LOCK('dasystems_create_sample_quote', 5)
```

Si no puede obtener el bloqueo, se detiene con:

```text
[ERROR] No fue posible obtener bloqueo de seguridad para crear datos de prueba.
```

Dentro del flujo protegido por bloqueo y transacción, revisa si ya existe una cotización de prueba con:

- `nombre_cliente = 'Cliente de Prueba D&A Systems'`
- `estado = 'borrador'`
- `descripcion = 'Cotización de prueba para validar listado interno'`

Si ya existe, no inserta otra y muestra:

```text
[OK] Ya existe una cotización de prueba. No se insertó una nueva.
```

El bloqueo se libera al finalizar la ejecución. Esto reduce el riesgo de duplicados si dos procesos intentan crear el dato de prueba al mismo tiempo.

## Cómo ejecutarlo

Desde la raíz del proyecto:

```bash
php sistema/tools/create-sample-quote.php --confirm-local
```

Con PHP de Laragon en Windows:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/create-sample-quote.php --confirm-local
```

Debe ejecutarse solo en una base local o de prueba con `sistema/config/database.php` configurado. La herramienta bloqueará la ejecución si la configuración no parece local o de prueba.

## Qué validar después

Después de ejecutar la herramienta en local o prueba:

1. Ejecutar:

```bash
php sistema/tools/check-quote-repository.php
```

2. Abrir `sistema/public/cotizaciones.php` en el sistema interno.
3. Confirmar que el listado real muestra la cotización de prueba.
4. Confirmar que aparece como `Sin emitir`, porque `numero_cotizacion` permanece en `NULL`.
5. Confirmar que el estado visible es `Borrador`.

## Qué NO se implementó

- No se modificó `cotizaciones.php`.
- No se modificó `QuoteRepository`.
- No se modificó `InternalPage`.
- No se modificaron login, logout, AuthGuard, SessionManager ni timeout.
- No se implementó CRUD.
- No se implementaron formularios.
- No se implementó `POST`.
- No se crearon controllers.
- No se crearon services.
- No se implementó emisión.
- No se implementó PDF.
- No se implementó correo.
- No se borraron datos.
- No se actualizaron datos existentes.
- No se truncaron tablas.
- No se ejecutó SQL DDL.
- No se modificó estructura de base de datos.
- No se tocó `cotizacion_correlativos`.
- No se muestran credenciales ni detalles sensibles de conexión.

## Próxima etapa recomendada

La siguiente etapa recomendada es validar visualmente el listado con el dato de prueba y luego preparar una vista de detalle de solo lectura para una cotización específica.
