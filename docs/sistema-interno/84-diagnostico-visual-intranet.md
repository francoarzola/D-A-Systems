# 84 — Diagnóstico visual general de la intranet interna

## 1. Objetivo del diagnóstico

La etapa 7B.01 inicia el bloque 7B, enfocado en diseño visual, experiencia de usuario y consistencia de la intranet interna.

El objetivo es revisar el estado visual actual del sistema interno después del cierre funcional del bloque 7A de cotizaciones. Esta etapa no aplica cambios todavía: no modifica HTML, CSS, PHP funcional, base de datos, PDF, emisión ni autenticación.

## 2. Alcance revisado

Se revisaron las siguientes pantallas y recursos:

- Login: `sistema/public/login.php`.
- Cotizaciones: `sistema/public/cotizaciones.php`.
- Detalle de cotización: `sistema/public/cotizacion-detalle.php`.
- Edición de cotización: `sistema/public/cotizacion-editar.php`.
- Vista imprimible: `sistema/public/cotizacion-imprimir.php`.
- Hoja de estilos: `sistema/public/assets/css/internal.css`.

## 3. Estado visual actual

La intranet se percibe como un sistema interno funcional en evolución. Tiene una base visual clara: fondo claro, tarjetas blancas, sombras suaves, botones redondeados y paneles de estado. La estética se acerca más a un prototipo operativo de panel administrativo que a una intranet corporativa ya consolidada.

La consistencia general es razonable porque se reutilizan clases como `container`, `header`, `card`, `grid`, `status-panel`, `quote-actions`, `quote-action`, `quote-table` y variantes de impresión. Sin embargo, aún conviven pantallas funcionales, secciones de referencia visual y estilos heredados de etapas previas. Eso genera ruido visual y hace que algunas pantallas parezcan más largas o menos jerarquizadas de lo necesario.

## 4. Diagnóstico por pantalla

### Login

Propósito: permitir acceso autenticado al sistema interno.

Fortalezas:

- Estructura simple y directa.
- Mensajes de error controlados.
- Formulario compacto.
- Buen contraste general entre fondo y tarjeta.

Problemas visuales detectados:

- El botón usa la clase `button-disabled` aunque es funcional, lo que puede transmitir una señal incorrecta.
- Hay estilos inline en el formulario, lo que dificulta mantener consistencia.
- El encabezado tiene presencia de landing simple más que de acceso corporativo interno.

Problemas de UX detectados:

- El mensaje de error es genérico, correcto por seguridad, pero visualmente podría diferenciarse mejor.
- Falta una jerarquía más clara entre marca, título y formulario.

Riesgo de modificarla: medio. Es una pantalla crítica de autenticación.

Prioridad de mejora: alta, porque es la primera impresión y base visual de acceso.

### Cotizaciones

Propósito: listar cotizaciones reales y crear borradores mínimos.

Fortalezas:

- Integra listado real, formulario real y feedback de errores.
- Usa mensajes flash, errores por campo y estado vacío.
- Mantiene acciones de detalle y edición por fila.

Problemas visuales detectados:

- La pantalla mezcla formulario funcional, maqueta visual antigua, listado real y vistas de referencia.
- Hay demasiadas secciones en una sola página.
- Las tarjetas anidadas hacen que algunas zonas se sientan pesadas.
- La sección de acciones dice que hay referencias no funcionales aunque ya existen acciones reales.

Problemas de UX detectados:

- El usuario puede confundirse entre formulario real y maqueta visual.
- El listado queda visualmente más abajo que el formulario y las referencias.
- La página cumple varias funciones al mismo tiempo.

Riesgo de modificarla: alto. Contiene creación real de borradores y listado conectado a base de datos.

Prioridad de mejora: alta, pero debe abordarse de forma gradual.

### Detalle de cotización

Propósito: mostrar datos reales de una cotización y sus acciones disponibles según estado.

Fortalezas:

- Flujo de acciones final ya está claro por estado.
- Usa tarjetas para separar datos generales, cliente, resumen, descripción, detalles y registro.
- Las acciones críticas están condicionadas por estado.

Problemas visuales detectados:

- Las acciones quedan al final, después de toda la información.
- No hay un encabezado compacto que agrupe número, estado, cliente, total y acciones principales.
- El detalle puede requerir mucho desplazamiento antes de actuar.

Problemas de UX detectados:

- Para una cotización emitida, descargar PDF o abrir vista imprimible puede quedar demasiado abajo.
- Para borrador, editar o emitir también queda después de detalles y resumen.

Riesgo de modificarla: medio. Es una pantalla central, pero el flujo de acciones está bien encapsulado.

Prioridad de mejora: media-alta.

### Edición de cotización

Propósito: editar borradores existentes.

Fortalezas:

- Bloquea estados no editables.
- Reutiliza estructura de formulario y mensajes.
- Mantiene enlaces a detalle y listado.

Problemas visuales detectados:

- La estructura de formulario es funcional, pero todavía se siente extensa y parecida a un formulario técnico.
- El texto de estado y número está contenido en una línea secundaria, sin destacar suficientemente el contexto del borrador.
- La agrupación de campos puede beneficiarse de una jerarquía más operacional.

Problemas de UX detectados:

- El usuario necesita entender rápido que solo edita borradores.
- El primer detalle está separado de la cabecera, pero no hay señal clara de que todavía no existen múltiples líneas dinámicas.

Riesgo de modificarla: medio-alto. Es escritura real.

Prioridad de mejora: media.

### Vista imprimible

Propósito: mostrar una cotización emitida como documento A4 imprimible.

Fortalezas:

- Tiene estructura A4 sobria.
- Usa `CompanyProfile`.
- Distingue impresión con `@media print`.
- Oculta acciones al imprimir.
- Presenta datos comerciales, cliente, tabla, resumen y pie.

Problemas visuales detectados:

- Es la pantalla más cercana a un documento comercial final.
- Puede requerir ajustes finos de tipografía, espacios y saltos de página tras pruebas con más datos.

Problemas de UX detectados:

- Está separada correctamente de la descarga PDF.
- La navegación de vuelta es clara.

Riesgo de modificarla: medio. Afecta percepción comercial y PDF visual futuro.

Prioridad de mejora: baja-media, salvo que se detecten problemas en documentos reales largos.

## 5. Diagnóstico de botones y acciones

Los botones y acciones usan principalmente `quote-action`, `quote-action-primary`, `quote-action-strong` y `quote-action-muted`. La base es consistente, pero hay oportunidades:

- Botones primarios: deben reservarse para la acción principal de la pantalla.
- Botones secundarios: deberían tener jerarquía más clara frente a acciones críticas.
- Acciones críticas: emisión debería mantenerse visualmente distinta, pero no demasiado parecida a descarga PDF.
- Enlaces de navegación: volver al listado y ver detalle deberían verse secundarios.
- Estados borrador/emitida: el sistema ya condiciona acciones correctamente, pero la presentación podría agruparse por contexto.
- Coherencia visual: el login usa `button-disabled` para un botón real, mientras cotizaciones usa `quote-action`, generando inconsistencia.

## 6. Diagnóstico de navegación

La navegación básica existe y funciona:

- Volver al listado está disponible en detalle y edición.
- Ver detalle aparece en listado y edición.
- Vista imprimible vuelve al detalle.

Oportunidades:

- Ubicar acciones principales más cerca del encabezado de cada pantalla.
- Reforzar el patrón de navegación entre listado, detalle, edición, impresión y PDF.
- Hacer que la intranet se sienta más como panel interno que como páginas independientes.

## 7. Diagnóstico de jerarquía visual

Títulos y subtítulos:

- Hay títulos claros, pero algunos textos siguen describiendo estados de implementación anterior.
- Conviene normalizar títulos de página, subtítulos operativos y textos de ayuda.

Bloques de contenido:

- Las tarjetas ayudan a ordenar, aunque hay exceso de tarjetas en algunas pantallas.
- Las tarjetas anidadas pueden aumentar el peso visual.

Tablas:

- Las tablas son legibles y tienen alineación de montos.
- En móvil dependen de scroll horizontal.

Formularios:

- Son completos, pero pueden simplificarse visualmente.
- Los errores por campo existen y son útiles.

Totales:

- Totales y montos están presentes, pero podrían destacarse mejor en detalle y edición.

Mensajes de estado:

- `status-panel` y `flash-message` cumplen la función, aunque conviene estandarizar cuándo usar cada uno.

## 8. Diagnóstico de formularios

Campos:

- Los campos reales están bien identificados.
- Hay valores por defecto razonables en creación de borradores.

Etiquetas:

- Son claras, aunque algunas podrían alinearse mejor para lectura rápida.

Agrupación:

- La agrupación por datos generales, cliente, contacto, detalle y observaciones es correcta.
- En creación, convive con una maqueta visual que debería retirarse o separarse después.

Legibilidad:

- Buena en desktop.
- En pantallas pequeñas puede haber mucha longitud vertical.

Botones:

- Guardar borrador y guardar cambios son claros.
- Emitir cotización es crítica y debe conservar tratamiento especial.

Errores/mensajes:

- La experiencia de validación mejoró con errores por campo.
- Falta una capa visual más compacta para errores y resumen de acción.

Experiencia de edición:

- La edición está enfocada en borradores, pero podría tener un encabezado más claro con estado y número.

## 9. Diagnóstico de tablas

Tabla de cotizaciones:

- Muestra campos esenciales.
- Tiene acciones por fila.
- Requiere scroll horizontal en pantallas pequeñas.

Tabla de detalles:

- La lectura es clara.
- Montos alineados a la derecha ayudan.
- Puede mejorar la densidad para evitar pantallas largas.

Legibilidad:

- Buena para pocos registros.
- Debe revisarse con listas más largas.

Alineación de montos:

- Correcta mediante clases de alineación.

Acciones por fila:

- En listado aparecen ver detalle y editar cuando corresponde.
- Conviene revisar espaciado entre acciones para evitar apariencia de texto suelto.

## 10. Diagnóstico de responsive

Desde CSS/HTML se infiere soporte responsive básico:

- `.grid` pasa a una columna bajo 900px.
- Tablas usan contenedores con overflow horizontal.
- Vista imprimible adapta grillas a una columna en pantalla pequeña.

Riesgos:

- Pantallas de cotizaciones y edición pueden quedar muy largas en móvil.
- Acciones en forma de chips/botones pueden envolver correctamente, pero necesitan revisión visual.
- Tablas con muchas columnas dependen de scroll lateral, lo que es aceptable para intranet, pero debe ser explícito visualmente.

No se modifica nada en esta etapa.

## 11. Matriz de problemas detectados

| Pantalla / área | Problema | Impacto | Riesgo de cambio | Prioridad | Recomendación |
|---|---|---:|---:|---:|---|
| Login | Botón funcional usa clase `button-disabled` | Medio | Medio | Alta | Normalizar estilos de botones sin tocar autenticación |
| Login | Estilos inline en formulario | Medio | Bajo | Alta | Mover estilo a `internal.css` en etapa visual |
| Cotizaciones | Mezcla formulario real, maqueta y referencias | Alto | Alto | Alta | Separar contenido funcional de referencias |
| Cotizaciones | Listado queda después de muchas secciones | Alto | Medio | Alta | Reordenar flujo hacia listado y creación rápida |
| Detalle | Acciones principales al final | Medio | Medio | Media-alta | Crear encabezado de acciones compacto |
| Detalle | Número, estado y total no forman cabecera operacional | Medio | Bajo | Media | Diseñar resumen superior |
| Edición | Formulario largo y técnico | Medio | Medio-alto | Media | Mejorar agrupación y jerarquía de edición |
| Edición | Contexto de borrador poco destacado | Medio | Bajo | Media | Encabezado con estado, número y acciones |
| Vista imprimible | Posibles saltos de página con datos extensos | Medio | Medio | Baja-media | Revisar con cotizaciones reales largas |
| Tablas | Scroll horizontal no señalizado | Medio | Bajo | Media | Mejorar contenedor y densidad |
| Mensajes | `status-panel` y `flash-message` conviven sin regla visual explícita | Medio | Bajo | Media | Definir sistema de mensajes |
| Navegación | Patrón entre listado/detalle/edición puede ser más uniforme | Medio | Medio | Media | Normalizar layout interno y acciones |

## 12. Priorización recomendada

Orden sugerido:

1. 7B.02 — Login y layout base.
2. 7B.03 — Header/navegación interna.
3. 7B.04 — Listado de cotizaciones.
4. 7B.05 — Detalle de cotización.
5. 7B.06 — Formulario de edición.
6. 7B.07 — Vista imprimible/PDF visual si corresponde.

## 13. Recomendación de estilo visual

Lineamientos recomendados:

- Mantener una intranet sobria.
- Usar estilo corporativo TI, claro y operativo.
- Mejorar claridad sin sobrecargar la interfaz.
- Priorizar lectura, estado, acciones y datos comerciales.
- Mantener foco operativo.
- No convertir pantallas internas en landing page.
- No usar estética gamer.
- No sobrediseñar.
- Evitar cambios simultáneos en muchas pantallas.

## 14. Riesgos de rediseño

- Romper flujos funcionales ya validados.
- Mezclar cambios visuales con lógica de negocio.
- Cambiar demasiadas pantallas a la vez.
- Afectar PDF o vista imprimible.
- Afectar responsive.
- Afectar formularios reales de creación/edición.
- Debilitar señales de acciones críticas como emitir cotización.

## 15. Decisión propuesta

La siguiente etapa debe ser acotada y de bajo riesgo. Se recomienda partir por una normalización visual base, preferentemente desde `internal.css`, y solo aplicar cambios mínimos de clases en una pantalla piloto si fuera necesario.

## 16. Qué NO se implementó

- No se aplicaron cambios visuales.
- No se modificó CSS.
- No se modificó HTML.
- No se modificó PHP funcional.
- No se modificó base de datos.
- No se modificó PDF.
- No se modificó emisión.
- No se modificó autenticación.
- No se implementó AJAX ni API JSON.

## 17. Próxima etapa recomendada

7B.02 — Normalización visual base de la intranet interna.

Esa etapa debería trabajar solo sobre `internal.css` y, si es necesario, cambios mínimos de clases en una pantalla piloto.
