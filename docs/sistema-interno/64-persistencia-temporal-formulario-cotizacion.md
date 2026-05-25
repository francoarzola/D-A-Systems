# 64. Persistencia temporal del formulario de cotización

## Objetivo

Conservar temporalmente en sesión los datos ingresados en el formulario mínimo cuando falla una validación de negocio, para repoblar `cotizaciones.php` después de la redirección.

No se guarda nada en base de datos si la validación falla.

## Clase FormState

Archivo:

```text
sistema/app/Support/FormState.php
```

Namespace:

```php
DAndASystems\Internal\Support
```

## Métodos disponibles

- `set(string $key, array $data): void`
- `get(string $key): ?array`
- `pull(string $key): ?array`
- `clear(string $key): void`

## Qué guarda temporalmente

Para la clave `quote_draft`, se conserva:

- `nombre_cliente`
- `rut_cliente`
- `nombre_contacto`
- `correo_contacto`
- `telefono_contacto`
- `descripcion`
- `fecha_cotizacion`
- `valido_hasta`
- `condiciones_comerciales`
- `observaciones`
- `detalles[0][descripcion]`
- `detalles[0][cantidad]`
- `detalles[0][unidad]`
- `detalles[0][precio_unitario_neto]`
- `detalles[0][descuento_monto]`

## Qué no guarda

No se guardan:

- token CSRF
- `numero_cotizacion`
- `estado`
- `subtotal_neto`
- `iva_monto`
- `total`
- otros totales calculados
- contraseñas o datos sensibles

## Integración con cotizaciones-guardar.php

Cuando `QuoteService::createDraft()` devuelve `success => false` por validación de negocio:

```text
FormState::set('quote_draft', $draftData)
FlashMessage::set(...)
redirect 303 a cotizaciones.php
```

Si el guardado es exitoso, se ejecuta:

```text
FormState::clear('quote_draft')
```

También se limpia explícitamente `quote_draft` antes de redirigir cuando la solicitud no es POST, cuando el CSRF es inválido o cuando ocurre un error técnico. Así el estado temporal solo sobrevive ante errores de validación de negocio.

## Integración con cotizaciones.php

La página lee el estado con:

```php
FormState::pull('quote_draft')
```

Luego usa esos valores para repoblar el formulario mínimo. Todas las salidas se imprimen con `ViewFormatter::e()`.

## Casos donde se conserva estado

- Validación de negocio fallida.

## Casos donde no se conserva estado

- Método distinto de POST.
- CSRF inválido.
- Error técnico.
- Guardado exitoso.

## Herramienta CLI

Se creó:

```bash
php sistema/tools/check-form-state-contract.php
```

Con Laragon:

```bash
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-form-state-contract.php
```

La herramienta verifica que exista `FormState`, que el endpoint guarde/limpie estado temporal y que `cotizaciones.php` repueble campos sin agregar inputs prohibidos.

## Prueba manual recomendada

1. Ingresar datos en el formulario.
2. Dejar cliente vacío o usar correo inválido.
3. Guardar.
4. Verificar mensaje y datos repoblados.
5. Corregir.
6. Guardar correctamente.
7. Verificar redirección al detalle.

## Qué NO se implementó

- No se implementó edición.
- No se implementó emisión.
- No se implementaron cambios de estado.
- No se generó número oficial de cotización.
- No se tocó `cotizacion_correlativos`.
- No se implementó PDF.
- No se implementó correo.
- No se creó AJAX.
- No se creó API JSON.
- No se crearon controllers.
- No se agregaron múltiples líneas dinámicas.
- No se calcularon totales en frontend.

## Próxima etapa recomendada

La próxima etapa recomendada es mejorar la validación visible del formulario y preparar una experiencia más completa para errores por campo, manteniendo el backend como fuente única de validación y cálculo.
