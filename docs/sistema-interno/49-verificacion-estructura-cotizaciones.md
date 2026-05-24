# Etapa 7A.5 - Verificacion tecnica de estructura de tablas de Cotizaciones

## Objetivo

Crear una herramienta CLI segura para verificar que la estructura inicial del modulo Cotizaciones existe correctamente en una base local o de prueba, despues de ejecutar manualmente el SQL inicial.

Esta etapa no modifica datos ni reemplaza una revision manual de la base. Solo consulta metadatos de `INFORMATION_SCHEMA`.

## Archivo creado

`sistema/tools/check-quotes-schema.php`

## Que valida la herramienta

La herramienta verifica estructura critica del modulo Cotizaciones:

- Existencia de las tablas `cotizaciones`, `cotizacion_detalles` y `cotizacion_correlativos`.
- Existencia de las columnas principales definidas en el SQL inicial.
- Existencia de indices principales.
- Existencia de la relacion `cotizacion_detalles.cotizacion_id` hacia `cotizaciones.id`.
- Existencia del `CHECK` `chk_cotizaciones_numero_estado`, si la version de MySQL/MariaDB permite confirmarlo desde metadatos.

## Tablas verificadas

### cotizaciones

Columnas principales:

- `id`
- `numero_cotizacion`
- `fecha_cotizacion`
- `valido_hasta`
- `nombre_cliente`
- `rut_cliente`
- `nombre_contacto`
- `correo_contacto`
- `telefono_contacto`
- `descripcion`
- `estado`
- `subtotal_neto`
- `descuento_monto`
- `iva_porcentaje`
- `iva_monto`
- `total`
- `condiciones_comerciales`
- `observaciones`
- `creado_por`
- `creado_en`
- `actualizado_en`

Indices esperados:

- `uq_cotizaciones_numero_cotizacion`
- `idx_cotizaciones_estado`
- `idx_cotizaciones_fecha_cotizacion`
- `idx_cotizaciones_nombre_cliente`
- `idx_cotizaciones_creado_por`

CHECK esperado:

- `chk_cotizaciones_numero_estado`

### cotizacion_detalles

Columnas principales:

- `id`
- `cotizacion_id`
- `numero_linea`
- `descripcion`
- `cantidad`
- `unidad`
- `precio_unitario_neto`
- `descuento_monto`
- `subtotal_linea_neto`
- `total_linea_neto`
- `creado_en`
- `actualizado_en`

Indice esperado:

- `uq_cotizacion_detalles_cotizacion_linea`

Relacion esperada:

- `cotizacion_detalles.cotizacion_id` referencia `cotizaciones.id`

### cotizacion_correlativos

Columnas principales:

- `id`
- `tipo_documento`
- `anio`
- `ultimo_numero`
- `creado_en`
- `actualizado_en`

Indice esperado:

- `uq_cotizacion_correlativos_tipo_anio`

## Como ejecutarla

Desde la raiz del proyecto:

```bash
php sistema/tools/check-quotes-schema.php
```

Con PHP de Laragon en Windows:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quotes-schema.php
```

La herramienta requiere que exista `sistema/config/database.php` con credenciales locales o de prueba. No debe usarse para imprimir credenciales ni informacion sensible.

## Significado de la salida

`[OK]` significa que la tabla, columna, indice, relacion o validacion revisada existe correctamente.

`[WARNING]` significa que algo no pudo confirmarse de forma concluyente, pero no necesariamente bloquea la verificacion critica. El caso principal es el `CHECK`, porque la compatibilidad y exposicion en metadatos puede variar entre versiones de MySQL/MariaDB.

`[ERROR]` significa que falta una pieza critica de estructura, como una tabla, columna, indice o clave foranea esperada. Si aparece un `ERROR`, el script termina con codigo de salida `1`.

## Codigos de salida

- `0`: no se detectaron errores criticos.
- `1`: falta una tabla, columna, indice, FK critica o no fue posible completar la verificacion.

## Seguridad

La herramienta:

- Solo se ejecuta por CLI.
- No crea paginas publicas.
- No inserta datos.
- No actualiza datos.
- No elimina datos.
- No crea tablas.
- No ejecuta el SQL inicial.
- No imprime host, usuario, password ni nombre de la base si ocurre un error.
- No muestra stack trace.

## Que NO se implemento

- No se modifico la base de datos.
- No se ejecuto SQL.
- No se modifico PHP funcional del sistema interno.
- No se modifico CSS.
- No se creo CRUD.
- No se crearon formularios.
- No se implemento `POST`.
- No se crearon repositories.
- No se crearon services.
- No se crearon controllers.

## Proxima etapa recomendada

La siguiente etapa recomendada es revisar el resultado de esta herramienta en el entorno local o de prueba y, si la estructura esta correcta, avanzar hacia una primera capa de lectura controlada para Cotizaciones, todavia sin modificar datos ni implementar CRUD completo.
