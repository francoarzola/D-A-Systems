# Siguiente nivel recomendado — Etapa 6 (post-publicación)

Fecha base: 2026-05-17

## Objetivo
Reducir abuso automatizado, aumentar trazabilidad operativa y fortalecer seguridad de capa aplicación/servidor sin romper conversiones del formulario.

## Prioridad Alta
1. **Rate limiting por IP en endpoint del formulario**
   - Política sugerida: 5 envíos / 10 minutos por IP.
   - Respuesta: `429 Too Many Requests` con mensaje controlado.

2. **Protección bot adicional**
   - Activar CAPTCHA/Turnstile solo ante señales de abuso.
   - Mantener honeypot como capa silenciosa primaria.

3. **CSP inicial en modo seguro**
   - Comenzar con política mínima compatible con scripts/estilos actuales.
   - Evolucionar luego a política más estricta con `report-only` previo.

## Prioridad Media
4. **Observabilidad de formularios**
   - Métricas: envíos totales, bloqueos honeypot, 4xx, 5xx, latencia.
   - Tablero simple semanal para ventas/operaciones.

5. **Retención y minimización de datos**
   - Definir ventana de retención para consultas no convertidas.
   - Procedimiento de eliminación bajo solicitud del titular.

6. **Plan de respuesta a incidentes web**
   - Responsable técnico y ruta de escalamiento.
   - Plantilla de comunicación interna/cliente en caso de caída.

## Prioridad Baja
7. **QA semestral SEO + seguridad ligera**
   - Re-validar sitemap, canonical, robots, headers y TLS.
   - Revisión de enlaces rotos y rendimiento básico.

## Criterio de éxito
- Reducción de spam sin caída significativa de conversiones.
- Tiempos de respuesta estables del formulario.
- Incidentes detectados tempranamente por monitoreo.
