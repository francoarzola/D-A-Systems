# Revisión Etapa 2 — Coherencia comercial y de contenido (sin cambios HTML)

## Alcance y criterio

- **Fuente de verdad usada:** contenido actual del repositorio en la rama de trabajo (no se usaron archivos externos).
- **Archivos revisados:**
  - `index.html`
  - `servicios-ti.html`
  - `soluciones-ti.html`
  - `nosotros.html`
  - `terminos-condiciones.html`
  - `politica-privacidad.html`
- **Objetivo de esta etapa:** diagnóstico y propuesta de ajustes de contenido **sin modificar HTML**, CSS, JS, imágenes ni assets.

---

## Resumen ejecutivo

El sitio está bien encaminado en tono B2B y evita promesas imposibles, pero aún presenta oportunidades de mejora en:

1. **Redundancia de mensajes** entre páginas (mismo bloque conceptual repetido con variaciones mínimas).
2. **CTAs demasiado genéricos** (“Contactar”, “Solicitar evaluación”) sin explicitar qué obtiene la empresa en el siguiente paso.
3. **Cobertura de servicios no totalmente homogénea** entre listado principal, soluciones y footer (ya más coherente, pero requiere alinear lenguaje exacto por prioridad comercial).
4. **Riesgo de sobre-extensión percibida** en algunos textos legales/comerciales que pueden sonar más maduros que una empresa TI nueva si no se acotan expectativas (plazos, alcances, dependencia de terceros).

La estructura general es rescatable. Se recomienda **ajuste de copy**, no rediseño.

---

## Diagnóstico por página

## 1) `index.html`

### Secciones que conviene mantener
- **Hero**: comunica propuesta clara (“soporte TI, infraestructura y continuidad”).
- **Áreas críticas**: útil para conectar dolor operativo con servicio.
- **Servicios TI**: buen inventario de capacidades.
- **FAQ + Contacto**: reduce fricción comercial.

### Secciones que conviene ajustar
- **Hero (subtexto + CTA):**
  - Hoy se explica “qué hacemos”, pero falta reforzar “qué obtiene el cliente en 1er contacto”.
  - CTA recomendado: “Solicitar diagnóstico inicial” + microcopy: “Respuesta con alcance sugerido y próximos pasos”.
- **Áreas críticas:**
  - Buen enfoque, pero repetir “orden, continuidad, respaldo” en varias tarjetas reduce impacto.
  - Recomendación: que cada tarjeta nombre **síntoma + resultado esperado**.
- **Servicios TI:**
  - La lista es amplia; conviene priorizar 5–7 servicios “core” y dejar otros como complementarios.
- **Soluciones:**
  - Escenarios correctos, pero algunas descripciones se solapan con “Servicios TI”.
  - Recomendado: estructurar cada solución como: “Situación actual / Riesgo / Acción recomendada”.
- **Footer:**
  - Coherente, pero la frase descriptiva puede simplificarse para evitar saturación.

### Secciones que podrían eliminarse o compactarse
- Ninguna sección crítica para eliminar en esta etapa.
- Sí conviene **compactar texto** en “Por qué elegirnos” y “Soluciones” para reducir densidad.

### Textos específicos a mejorar
- Frases repetidas del tipo: “ordenar, proteger y mantener operativa la infraestructura”.
- Sustituir repeticiones por variantes con beneficio concreto (tiempo de respuesta, trazabilidad, continuidad).

### Riesgos de credibilidad
- Bajo-medio: si se mantiene un tono demasiado amplio sin límites de alcance (“todo TI para todos”).
- Mitigación: incluir frases de encuadre (“según diagnóstico”, “según capacidad instalada del cliente”, “con terceros cuando corresponda”).

---

## 2) `servicios-ti.html`

### Secciones que conviene mantener
- Introducción de servicio + metodología de atención.
- Formulario de contacto específico del servicio.

### Secciones que conviene ajustar
- **Título y primer bloque:** hoy repite mucho el claim de portada.
  - Recomendación: diferenciar esta página con enfoque “catálogo operativo” y no “manifiesto”.
- **Bloques de features:**
  - Alinear naming exacto con lista comercial prioritaria:
    - Soporte técnico
    - Infraestructura y redes
    - Respaldos y continuidad
    - Migración de correos
    - Licenciamiento y antivirus
    - Inventario TI e informes
    - Mantención de equipos

### Secciones que podrían eliminarse o compactarse
- Compactar párrafos introductorios extensos que repiten contexto ya visible en home.

### Textos específicos a mejorar
- CTA final: pasar de “enviar solicitud” a algo más concreto:
  - “Solicitar revisión de requerimiento TI”
  - “Recibir propuesta de próximos pasos”

### Riesgos de credibilidad
- Medio: si se percibe amplitud sin aclarar prioridad y forma de atención.
- Mitigación: agregar lenguaje de “evaluación inicial + plan por etapas”.

---

## 3) `soluciones-ti.html`

### Secciones que conviene mantener
- Enfoque por escenarios reales (empresa sin respaldos, red inestable, etc.).
- Estructura narrativa “problema → enfoque”.

### Secciones que conviene ajustar
- Evitar duplicar explicación institucional ya presente en `index.html` y `nosotros.html`.
- Reforzar cada solución con un cierre accionable:
  - “Qué revisamos primero”,
  - “Qué evidencia entregamos” (informe breve, checklist, hallazgos).

### Secciones que podrían eliminarse o compactarse
- Bloques introductorios largos que no agregan información nueva frente a Home.

### Textos específicos a mejorar
- Cambiar frases aspiracionales largas por mensajes directos con límites realistas.

### Riesgos de credibilidad
- Medio: prometer continuidad sin matizar dependencias externas (ISP, nube, proveedores).
- Mitigación: precisar que se trabaja sobre “mejores prácticas y mitigación de riesgo”, no eliminación total de incidentes.

---

## 4) `nosotros.html`

### Secciones que conviene mantener
- Presentación de enfoque y propuesta de acompañamiento.
- CTA de contacto al cierre.

### Secciones que conviene ajustar
- Evitar frases demasiado absolutas como “apoyo confiable” sin evidencia contextual.
- Reemplazar por mensajes verificables de proceso:
  - “atención documentada”,
  - “priorización por impacto”,
  - “seguimiento de tareas”.

### Secciones que podrían eliminarse o compactarse
- Reducir repetición entre “sobre nosotros” y “por qué elegirnos” del home.

### Textos específicos a mejorar
- Menos “quiénes somos” abstracto, más “cómo trabajamos en la práctica”.

### Riesgos de credibilidad
- Bajo-medio: tono institucional correcto, pero necesita más aterrizaje operativo.

---

## 5) `terminos-condiciones.html`

### Secciones que conviene mantener
- Estructura legal amplia y ordenada.
- Capítulos sobre responsabilidad, terceros, limitación de responsabilidad.

### Secciones que conviene ajustar
- Revisar placeholders pendientes (RUT, domicilio, correo formal de términos) para no afectar confianza.
- Unificar vocabulario de alcance del servicio con el discurso comercial (sin sobrepromesas).

### Secciones que podrían eliminarse o compactarse
- No eliminar; solo simplificar redacción en párrafos demasiado extensos.

### Textos específicos a mejorar
- Donde sea posible, usar frases más directas y menos redundantes en “uso del sitio” y “servicios ofrecidos”.

### Riesgos de credibilidad
- Alto si quedan placeholders visibles.
- Mitigación prioritaria: completar datos corporativos mínimos o retirar temporalmente campos incompletos.

---

## 6) `politica-privacidad.html`

### Secciones que conviene mantener
- Cobertura completa del ciclo de datos (captura, finalidad, terceros, derechos).

### Secciones que conviene ajustar
- Igual que en términos: resolver placeholders corporativos faltantes.
- Compactar pasajes repetitivos entre introducción, alcance y finalidades.

### Secciones que podrían eliminarse o compactarse
- No eliminar apartados; compactar para lectura ejecutiva.

### Textos específicos a mejorar
- Clarificar “qué datos técnicos se recopilan sí/no” en términos prácticos para no sonar ambiguo.

### Riesgos de credibilidad
- Alto con placeholders sin completar.
- Mitigación: completar datos o aclarar “en actualización” con fecha de publicación real.

---

## Recomendaciones de CTA (sitio completo)

## CTAs primarios sugeridos
- “Solicitar diagnóstico inicial TI”
- “Solicitar revisión de requerimiento”
- “Hablar por WhatsApp con un especialista TI”

## Microcopy recomendado bajo CTA
- “Te respondemos con alcance sugerido y próximos pasos.”
- “Sin compromiso de contratación inicial.”
- “Atención remota o presencial según necesidad.”

## CTAs a evitar
- “Garantizamos cero caídas”
- “Solución definitiva inmediata”
- Cualquier promesa absoluta no verificable.

---

## Riesgos de credibilidad (transversales)

1. **Placeholders legales visibles** (`[Ingresar ...]`) en páginas legales.
2. **Sobreposición de mensajes** entre Home/Servicios/Soluciones/Nosotros que puede parecer relleno.
3. **Promesa implícita amplia** en continuidad operativa sin matices de dependencia externa.
4. **Falta de claridad del “siguiente paso”** tras completar formularios.

---

## Prioridades de cambio (propuesta para Etapa 2 de edición)

## Prioridad alta
1. Completar/normalizar datos legales visibles en `terminos-condiciones.html` y `politica-privacidad.html`.
2. Ajustar CTAs de Home y Servicios para hacer explícito el resultado del primer contacto.
3. Reducir repeticiones de copy transversal (home/servicios/soluciones/nosotros).

## Prioridad media
4. Estandarizar redacción de lista de servicios entre todas las páginas (mismo naming y orden).
5. Compactar párrafos extensos en Soluciones y Nosotros.

## Prioridad baja
6. Afinar tono y estilo (menos abstracto, más operativo) en algunos subtítulos y descripciones.

---

## Qué NO cambiar en la próxima implementación

- No alterar estructura visual, clases CSS, JS, imágenes ni assets.
- No incorporar claims no verificables (clientes, certificaciones, métricas o garantías absolutas).

---

## Entregable de esta fase

Este documento constituye la **revisión previa** solicitada para Etapa 2.
No se realizaron cambios en HTML en esta fase.
