# 77 - Evaluación de estrategia PDF para cotizaciones

## 1. Objetivo de la evaluación

El sistema ya cuenta con una vista HTML A4 imprimible en `cotizacion-imprimir.php` y con un endpoint autenticado simulado en `cotizacion-pdf.php` que responde `HTTP 501` sin generar PDF real. También existe `QuotePdfService`, que centraliza las precondiciones para preparar un PDF.

Esta etapa evalúa alternativas técnicas antes de implementar generación real, para elegir una estrategia compatible con desarrollo local en Laragon y futura publicación en hosting compartido o cPanel.

## 2. Estado actual del flujo

El flujo actual de cotizaciones permite:

- crear borradores
- editar borradores
- emitir cotizaciones
- asignar número oficial al emitir
- ver detalle de cotización
- abrir vista imprimible HTML A4
- acceder a endpoint PDF simulado autenticado
- validar precondiciones con `QuotePdfService`

Todavía no existe PDF real, no hay botón `Descargar PDF`, no se instaló librería y no se envía correo.

## 3. Requisitos del PDF real

La generación PDF futura debe cumplir:

- ser autenticada
- operar solo sobre cotizaciones emitidas con número oficial
- reutilizar datos existentes desde `QuoteService`
- reutilizar datos comerciales desde `CompanyProfile`
- escapar contenido dinámico con `ViewFormatter`
- mantener formato comercial sobrio
- no modificar base de datos
- no cambiar estados ni números de cotización
- ser compatible con hosting/cPanel
- mantener una estrategia simple de mantenimiento
- generar inicialmente PDF en memoria sin guardar archivos

## 4. Alternativa 1: Dompdf

Dompdf es una librería PHP que convierte HTML y CSS a PDF. Puede integrarse mediante Composer y ejecutarse dentro del mismo proceso PHP.

Ventajas:

- funciona como dependencia PHP sin binarios externos
- integración directa con endpoints PHP
- buena opción para HTML relativamente simple
- adecuada para documentos comerciales controlados
- permite generar PDF en memoria

Desventajas:

- soporte CSS limitado frente a navegador real
- puede requerir ajustar estilos específicos para PDF
- consume memoria según tamaño del HTML y tablas
- depende de Composer o instalación manual controlada

Compatibilidad probable:

La vista A4 actual usa HTML y CSS moderados. Dompdf debería ser viable si se mantiene CSS simple, tablas controladas y sin JavaScript.

Impacto en Composer:

Requiere incorporar dependencia, idealmente con Composer. Esto implica definir si el hosting permite subir `vendor/` o ejecutar Composer en despliegue.

Impacto en cPanel:

Suele ser más compatible con hosting compartido que opciones basadas en binarios externos. Debe revisarse memoria PHP, versión de PHP y permisos de escritura temporal.

Riesgos:

- diferencias visuales respecto al navegador
- errores con fuentes, acentos o UTF-8 si no se configura bien
- límites de memoria en cotizaciones extensas

Cuándo conviene usarlo:

Conviene como primera alternativa si el documento se mantiene controlado, sin CSS avanzado, sin JavaScript y con necesidad de integrarse directamente en PHP.

## 5. Alternativa 2: mPDF

mPDF es otra librería PHP orientada a generar PDF desde HTML, con buen soporte para documentos administrativos y texto con acentos.

Ventajas:

- buen soporte para UTF-8 y documentos administrativos
- opciones robustas de paginación, encabezados y pies
- útil para tablas y documentos más extensos

Desventajas:

- puede ser más pesada en memoria
- también requiere Composer o instalación controlada
- puede exigir ajustes específicos de HTML/CSS

Impacto en rendimiento:

Puede consumir más recursos que Dompdf, especialmente con documentos largos o estilos complejos. En cPanel esto debe probarse con límites reales de memoria.

Impacto en Composer:

Requiere dependencia externa. Debe definirse cómo se instalará y desplegará `vendor/`.

Impacto en cPanel:

Puede funcionar en hosting compartido, pero el consumo de memoria y tiempo de ejecución debe validarse temprano.

Riesgos:

- mayor peso de dependencia
- consumo de memoria
- configuración adicional para formato fino

Cuándo conviene usarlo:

Conviene si Dompdf presenta problemas con tablas, acentos, paginación o necesidades más formales de documento.

## 6. Alternativa 3: wkhtmltopdf

wkhtmltopdf usa un motor externo basado en WebKit para convertir HTML a PDF mediante un binario del sistema.

Ventajas:

- alta fidelidad visual con HTML/CSS renderizado como navegador antiguo
- útil cuando se necesita reproducir diseños web más complejos
- puede manejar mejor algunas reglas CSS visuales que librerías PHP puras

Desventajas:

- requiere binario externo instalado en el servidor
- mayor complejidad operativa
- ejecución de procesos desde PHP
- despliegue más difícil en hosting compartido

Necesidad de binario externo:

Debe existir `wkhtmltopdf` instalado y accesible por PHP. Esto normalmente no está garantizado en cPanel.

Complejidad en hosting compartido/cPanel:

No se recomienda como primera opción porque muchos hostings compartidos no permiten instalar binarios ni ejecutar procesos externos libremente.

Riesgos:

- falta de binario
- restricciones de hosting
- rutas y permisos
- seguridad al invocar procesos
- diferencias por versión del binario

Cuándo conviene usarlo:

Conviene en VPS o servidores controlados, donde se puede instalar y mantener el binario.

## 7. Alternativa 4: impresión desde navegador

La impresión desde navegador ya está disponible mediante `cotizacion-imprimir.php` y `window.print()`.

Ventajas:

- no requiere librerías
- no requiere Composer
- ya funciona hoy
- usa el motor real del navegador
- sirve como respaldo operativo inmediato

Desventajas:

- depende del navegador del usuario
- no entrega un archivo PDF de forma controlada desde el servidor
- puede variar según navegador, impresora o configuración local
- no sirve bien para correo automático futuro

Limitaciones comerciales:

Para procesos formales puede ser insuficiente, porque no garantiza nombre, descarga, adjunto o archivo generado desde servidor.

Cuándo conviene mantenerla:

Debe mantenerse como respaldo funcional y alternativa manual, incluso si se implementa PDF real.

## 8. Comparativa resumida

| Estrategia | Facilidad de implementación | Compatibilidad cPanel | Fidelidad visual | Dependencias | Riesgo operativo | Recomendación |
| --- | --- | --- | --- | --- | --- | --- |
| Dompdf | Alta | Buena, con pruebas de memoria | Media a buena en HTML simple | Composer/PHP | Medio | Recomendada como primera opción |
| mPDF | Media | Buena, pero revisar memoria | Buena para documentos administrativos | Composer/PHP | Medio a alto | Alternativa si Dompdf no basta |
| wkhtmltopdf | Baja en cPanel | Baja en hosting compartido | Alta | Binario externo | Alto | No recomendada inicialmente |
| Impresión desde navegador | Ya disponible | Total | Alta en navegador local | Ninguna | Bajo | Mantener como respaldo |

## 9. Recomendación para D&A Systems

Estrategia recomendada: Dompdf.

Para este proyecto, Dompdf es la primera alternativa razonable porque permite integrar generación PDF en PHP sin depender de binarios externos. La vista actual es HTML controlado, con estilos A4 sobrios y tablas simples, lo que encaja con una prueba inicial de Dompdf.

mPDF queda como alternativa si Dompdf presenta problemas de tablas, paginación, acentos o formato. wkhtmltopdf no se recomienda como primera opción para hosting compartido porque depende de binarios externos. La impresión desde navegador debe mantenerse como respaldo funcional.

## 10. Arquitectura propuesta para etapa posterior

Una etapa futura 7A.34 podría:

- preparar dependencia Dompdf de forma controlada
- crear un servicio renderizador de PDF
- reutilizar `QuotePdfService` para precondiciones
- reutilizar `QuoteService` para cargar datos
- reutilizar `CompanyProfile` para datos comerciales
- reutilizar `ViewFormatter` o escape equivalente para HTML
- generar PDF en memoria
- responder desde `cotizacion-pdf.php` con headers correctos
- no guardar archivos al principio

La descarga autenticada debería quedar limitada a cotizaciones emitidas con número oficial.

## 11. Riesgos y controles

Riesgos identificados:

- tamaño del HTML y cantidad de ítems
- compatibilidad CSS limitada
- caracteres UTF-8 y acentos
- consumo de memoria en hosting compartido
- rutas internas y recursos externos
- exposición accidental de PDF sin autenticación
- generación de PDF para borradores
- escritura de archivos antes de definir política de almacenamiento

Controles propuestos:

- mantener HTML simple
- probar con cotizaciones extensas
- validar UTF-8
- generar en memoria al inicio
- no escribir archivos hasta etapa posterior
- usar `QuotePdfService` para bloquear borradores
- mantener `AuthGuard` en el endpoint
- no aceptar rutas ni nombres de archivo desde el usuario

## 12. Decisión propuesta

Estrategia recomendada: Dompdf.

Implementación futura: descarga autenticada sin persistencia inicial. El PDF debe generarse en memoria, usando datos ya cargados por el backend y sin modificar base de datos.

La vista imprimible se mantiene como respaldo. El correo no debe implementarse hasta que el PDF real esté probado.

## 13. Qué NO se implementó

No se instaló librería, no se creó `composer.json`, no se modificó `composer.lock`, no se creó `vendor/`, no se generó PDF, no se agregó botón, no se modificó `cotizacion-pdf.php`, no se modificó base de datos, no se tocó emisión, no se implementó correo, AJAX ni API JSON.

## 14. Próxima etapa recomendada

7A.34 — Preparar dependencia Dompdf de forma controlada.

Como alternativa, 7A.34 podría ser un prototipo interno de render PDF sin exponer descarga final, si se prefiere validar primero compatibilidad visual y consumo de memoria.
