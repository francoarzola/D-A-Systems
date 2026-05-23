# Etapa 7A.4 — Ejecución controlada del SQL inicial de Cotizaciones

## Objetivo

Definir una guía clara para ejecutar manualmente el SQL inicial del módulo Cotizaciones en un entorno local o de prueba, sin automatizar la ejecución desde PHP y sin modificar el sistema funcional.

Archivo SQL a ejecutar manualmente:

`sistema/database/sql/001_create_quotes_tables.sql`

Esta guía no ejecuta SQL. Solo documenta el procedimiento recomendado.

## Entorno recomendado

La ejecución debe realizarse primero en:

- Entorno local.
- Base de datos de prueba.
- Copia controlada del entorno real.

No se debe ejecutar en producción sin respaldo válido, revisión del SQL y ventana de mantenimiento definida.

## Checklist previo

Antes de ejecutar:

- [ ] Confirmar que existe `sistema/config/database.php` en el entorno local o de prueba.
- [ ] Confirmar que `sistema/config/database.php` no está versionado ni contiene credenciales compartidas.
- [ ] Confirmar que la base de datos seleccionada es local o de prueba.
- [ ] Hacer respaldo completo de la base antes de ejecutar.
- [ ] Confirmar que no existen tablas conflictivas con nombres `cotizaciones`, `cotizacion_detalles` o `cotizacion_correlativos`.
- [ ] Revisar si existen tablas antiguas `quotes` o `quote_items` y decidir si convivirán temporalmente.
- [ ] Confirmar versión de MySQL/MariaDB.
- [ ] Confirmar soporte efectivo para `CHECK` constraints en la versión usada.
- [ ] Revisar que el usuario MySQL tenga permisos suficientes para crear tablas, índices y claves foráneas.
- [ ] Confirmar que no se está ejecutando en producción por accidente.

## Respaldo previo

### Desde phpMyAdmin

1. Entrar a phpMyAdmin.
2. Seleccionar la base de datos del entorno local o de prueba.
3. Ir a la pestaña **Exportar**.
4. Seleccionar exportación rápida o personalizada según necesidad.
5. Descargar el archivo `.sql`.
6. Guardar el respaldo con fecha y nombre del entorno.

Ejemplo de nombre:

`backup_pre_7a4_cotizaciones_2026-05-23.sql`

### Desde cliente MySQL

Si se usa consola:

```bash
mysqldump -u USUARIO -p NOMBRE_BASE_DATOS > backup_pre_7a4_cotizaciones.sql
```

No guardar credenciales dentro de scripts versionados.

## Revisar tablas existentes

Antes de ejecutar el SQL, revisar si ya existen tablas relacionadas.

Consulta sugerida:

```sql
SHOW TABLES LIKE 'cotizacion%';
SHOW TABLES LIKE 'cotizaciones';
SHOW TABLES LIKE 'quotes';
SHOW TABLES LIKE 'quote_items';
```

Tablas esperadas por esta etapa:

- `cotizaciones`
- `cotizacion_detalles`
- `cotizacion_correlativos`

Si alguna ya existe, detenerse y revisar antes de continuar.

## Ejecución manual con phpMyAdmin

1. Abrir phpMyAdmin.
2. Seleccionar la base de datos local o de prueba.
3. Abrir el archivo:

   `sistema/database/sql/001_create_quotes_tables.sql`

4. Copiar el contenido completo.
5. Ir a la pestaña **SQL**.
6. Pegar el contenido.
7. Revisar visualmente que el script crea solo:

   - `cotizaciones`
   - `cotizacion_detalles`
   - `cotizacion_correlativos`

8. Ejecutar.
9. Revisar si phpMyAdmin reporta errores.
10. Si hay error, no continuar con nuevas pruebas hasta entender la causa.

## Ejecución manual con cliente MySQL

Desde la raíz del proyecto o indicando la ruta completa:

```bash
mysql -u USUARIO -p NOMBRE_BASE_DATOS < sistema/database/sql/001_create_quotes_tables.sql
```

En Windows, si `mysql` no está en PATH, usar la ruta del cliente MySQL/MariaDB instalado por Laragon o el entorno local.

No colocar password directamente en el comando si el historial de consola puede quedar guardado.

## Validación posterior

Después de ejecutar el SQL, validar que existan las tablas:

```sql
SHOW TABLES LIKE 'cotizaciones';
SHOW TABLES LIKE 'cotizacion_detalles';
SHOW TABLES LIKE 'cotizacion_correlativos';
```

Validar estructura:

```sql
DESCRIBE cotizaciones;
DESCRIBE cotizacion_detalles;
DESCRIBE cotizacion_correlativos;
```

Validar índices:

```sql
SHOW INDEX FROM cotizaciones;
SHOW INDEX FROM cotizacion_detalles;
SHOW INDEX FROM cotizacion_correlativos;
```

Validar constraints si la versión lo soporta:

```sql
SHOW CREATE TABLE cotizaciones;
```

Revisar que aparezca:

`chk_cotizaciones_numero_estado`

Esta regla refuerza que:

- `estado = 'borrador'` requiere `numero_cotizacion IS NULL`.
- `estado <> 'borrador'` requiere `numero_cotizacion IS NOT NULL`.

## Probar conexión PDO

Una vez creada o confirmada la configuración real:

`sistema/config/database.php`

Ejecutar la prueba controlada:

```bash
php sistema/tools/check-db-connection.php
```

En Windows con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-db-connection.php
```

Resultado esperado:

`Conexión OK`

Si aparece:

`Error de conexión controlado`

revisar credenciales, nombre de base, host, permisos del usuario MySQL y disponibilidad del servidor local.

## Checklist posterior

Después de ejecutar:

- [ ] Confirmar que `cotizaciones` existe.
- [ ] Confirmar que `cotizacion_detalles` existe.
- [ ] Confirmar que `cotizacion_correlativos` existe.
- [ ] Confirmar índice único `uq_cotizaciones_numero_cotizacion`.
- [ ] Confirmar índice único `uq_cotizacion_detalles_cotizacion_linea`.
- [ ] Confirmar índice único `uq_cotizacion_correlativos_tipo_anio`.
- [ ] Confirmar clave foránea de `cotizacion_detalles.cotizacion_id`.
- [ ] Confirmar `CHECK` `chk_cotizaciones_numero_estado` si la versión lo soporta.
- [ ] Ejecutar `sistema/tools/check-db-connection.php`.
- [ ] Registrar resultado de la prueba en notas internas, sin credenciales.

## Riesgos

- Ejecutar en producción sin respaldo puede dejar cambios difíciles de revertir.
- Si existen tablas antiguas `quotes` o `quote_items`, puede haber confusión de modelos.
- Algunas versiones de MySQL/MariaDB pueden aceptar `CHECK` pero no aplicarlo de la misma forma que versiones modernas.
- `ON DELETE CASCADE` en `cotizacion_detalles` elimina detalles si se elimina la cotización padre; revisar si se permitirá eliminar cotizaciones comerciales.
- Ejecutar con un usuario MySQL con permisos excesivos aumenta el riesgo operativo.
- Copiar credenciales reales en documentación, chat o commits compromete el entorno.

## Qué NO se implementó

- No se ejecutó SQL.
- No se creó PHP nuevo.
- No se modificó PHP existente.
- No se modificó CSS.
- No se modificó base de datos.
- No se creó CRUD.
- No se crearon formularios.
- No se implementó `POST`.
- No se crearon repositories.
- No se crearon services.
- No se crearon controllers.
- No se automatizó la ejecución del SQL.

## Próxima etapa recomendada

La siguiente etapa recomendada es ejecutar el SQL solo en un entorno local o de prueba preparado, registrar el resultado y luego diseñar una verificación técnica de estructura sin implementar todavía CRUD del módulo Cotizaciones.
