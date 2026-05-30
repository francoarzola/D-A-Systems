# 7B.08 — Revisión de textos, acentos y microcopy del módulo de cotizaciones

## Objetivo

Revisar, normalizar y mejorar textos visibles, acentos, etiquetas, títulos, mensajes cortos y microcopy del módulo de cotizaciones para que el sistema se lea profesional, claro y consistente en español formal.

## Pantallas revisadas

- `cotizaciones.php` (Listado)
- `cotizacion-detalle.php` (Detalle)
- `cotizacion-editar.php` (Edición de borrador)

## Criterios de redacción aplicados

- Corrección de acentos: cotización, edición, emisión, descripción, observación, información, válido, número, acción.
- Evitar textos demasiado técnicos para usuario final.
- Mantener mensajes breves y operativos.
- Evitar frases largas y comerciales.
- Mantener tono profesional, claro y sobrio.
- Español formal chileno/neutro sin modismos.
- Mantener consistencia entre pantallas.

## Textos normalizados

### cotizaciones.php

**Cambio 1: Corrección de acento**
- Original: "Administra borradores, emision y documentos comerciales desde una vista centralizada."
- Corregido: "Administra borradores, emisión y documentos comerciales desde una vista centralizada."
- Razón: "emision" faltaba tilde; "emisión" es la forma correcta en español.

**Cambio 2: Mejora de microcopy del formulario**
- Original: "Formulario mínimo real para guardar un borrador con un detalle. Los totales se calculan en el servidor."
- Corregido: "Completa el formulario para crear un nuevo borrador. Los montos se recalculan automáticamente en el servidor."
- Razón: Menos técnico ("mínimo real"), más claro ("completa el formulario"), orientado a acción, y "recalculan automáticamente" es más natural que "se calculan".

### cotizacion-detalle.php

**Cambio 3: Mejora de intro del detalle**
- Original: "Resumen ejecutivo y acciones principales."
- Corregido: "Información completa y acciones disponibles."
- Razón: "Información completa" es más preciso que "resumen ejecutivo" (muy corporativo), y "acciones disponibles" es más claro que "acciones principales".

### cotizacion-editar.php

- **Sin cambios necesarios.** Los textos son claros y bien acentuados.

## Qué se revisó y se confirmó consistente

- Uso consistente de "cotización" en singular y plural.
- Uso consistente de "borrador" para estado de edición.
- Uso consistente de "emitida" para estado oficial.
- Etiquetas de botones: "Crear borrador", "Guardar cambios", "Ver detalle", "Volver al listado", "Vista imprimible", "Descargar PDF".
- Títulos operativos: "Cotizaciones", "Detalle de cotización", "Editar cotización".
- Campos y secciones bien etiquetados: Datos generales, Cliente, Contacto, Detalle, Observaciones.

## Qué se mantuvo funcionalmente intacto

- Listado real y paginación desde base de datos.
- Creación de borrador (`cotizaciones-guardar.php`).
- Edición de borrador (`cotizacion-actualizar.php`).
- Emisión (`cotizacion-emitir.php`).
- Vista imprimible y descarga PDF (`cotizacion-imprimir.php`, `cotizacion-pdf.php`).
- CSRF tokens y nombres de campos.
- Rutas, servicios y repositorios.
- Base de datos y lógica de negocio.

## Riesgos controlados

- Cambios únicamente de texto visible; sin alteración de variables, inputs ni clases CSS.
- Redacción mantenida profesional y operativa.
- No se cambió estructura HTML ni flujos de navegación.

## Qué NO se implementó

- No se hicieron cambios visuales estructurales.
- No se modificó CSS (salvo necesidad mínima, no tocado).
- No se crearon nuevos endpoints.
- No se cambiaron servicios ni repositorios.
- No se modificó base de datos.
- No se implementó AJAX ni API JSON.
- No se agregó JavaScript.

## Herramienta CLI creada

- `sistema/tools/check-quotes-text-microcopy-contract.php` — valida presencia de textos clave, elementos funcionales, y ausencia de patrones prohibidos.

### Cómo ejecutar

```bash
php sistema/tools/check-quotes-text-microcopy-contract.php
```

Comando Laragon:

```
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quotes-text-microcopy-contract.php
```

## Prueba manual recomendada

1. Iniciar sesión en la intranet.
2. Abrir `cotizaciones.php` y verificar que encabezado dice "Administra borradores, emisión y documentos comerciales..."
3. Abrir `cotizacion-detalle.php?id=<id>` (cotización emitida) y verificar que intro es "Información completa y acciones disponibles."
4. Abrir `cotizacion-editar.php?id=<borrador>` y verificar textos de edición.
5. Confirmar que:
   - Crear borrador funciona.
   - Editar borrador funciona.
   - Emitir sigue funcionando.
   - Vista imprimible sigue funcionando.
   - Descargar PDF sigue funcionando.
6. Verificar que los textos se leen naturalmente.

## Próxima etapa recomendada

- 8A.01 — Inicio del módulo de clientes.
- O alternativamente 7B.09 — Revisión responsive del módulo de cotizaciones.

***

## Comprobaciones sugeridas tras los cambios

```bash
php -l sistema/public/cotizaciones.php
php -l sistema/public/cotizacion-detalle.php
php -l sistema/public/cotizacion-editar.php
php -l sistema/tools/check-quotes-text-microcopy-contract.php
php sistema/tools/check-quotes-text-microcopy-contract.php
```

Comandos git:

```bash
git status
git diff --check
```
