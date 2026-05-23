# Etapa 6D.24 — Extraer estilos estáticos de Cotizaciones a CSS interno

## Objetivo

Reducir estilos inline en la maqueta del módulo Cotizaciones, moviendo estilos repetidos o estructurales a `sistema/public/assets/css/internal.css`, sin cambiar funcionalidad ni diseño visual esperado.

## Archivos modificados

- `sistema/public/cotizaciones.php`
- `sistema/public/assets/css/internal.css`
- `docs/sistema-interno/38-estilos-internos-cotizaciones.md`

## Qué estilos se movieron

- Márgenes de secciones de maqueta.
- Tarjetas internas sin sombra.
- Bloques de dos columnas dentro de la grilla.
- Labels, textos de label e inputs deshabilitados de la maqueta.
- Contenedores con scroll horizontal para tablas.
- Estilos estructurales de tablas:
  - ancho
  - colapso de bordes
  - ancho mínimo
  - padding de celdas
  - bordes entre filas
  - alineación derecha para montos
- Texto de acción visual no funcional.
- Contenedor de botones visuales.
- Variantes visuales de botones de maqueta:
  - primario
  - fuerte
  - atenuado

## Qué se mantuvo igual

- La maqueta sigue siendo estática.
- `cotizaciones.php` sigue usando `InternalPage::render()`.
- La navegación activa sigue siendo `cotizaciones`.
- Los datos siguen siendo ficticios.
- Los botones siguen siendo elementos visuales sin acción real.

## Qué NO se implementó

- No se creó CRUD.
- No se crearon formularios funcionales.
- No se creó base de datos.
- No se creó SQL.
- No se crearon controllers, repositories ni models.
- No se modificó `InternalPage`.
- No se cambió login, logout, `AuthGuard`, `SessionManager` ni timeout.
- No se implementaron cálculos.
- No se implementó PDF.
- No se implementó envío de correo.
- No se cambió comportamiento funcional.

## Pruebas recomendadas

1. Ejecutar `php -l sistema/public/cotizaciones.php`.
2. Ejecutar `php -l sistema/app/Support/InternalPage.php`.
3. Abrir `cotizaciones.php` y comparar visualmente la maqueta.
4. Revisar tablas en ancho móvil para confirmar el scroll horizontal.

## Revisión visual pendiente

Validar en navegador que la maqueta mantiene la intención visual anterior, especialmente en tarjetas internas, tablas y botones visuales.
