# 83 — Revisión de flujo de acciones de cotización

## Objetivo de la etapa

La etapa 7A.39 cierra y formaliza el flujo visible de acciones en el detalle de cotización, sin agregar nuevas funcionalidades.

El objetivo es confirmar que las acciones disponibles dependen del estado de la cotización y que no se mezclan acciones de borrador con acciones de cotización emitida.

## Estado final del flujo de acciones

La vista `sistema/public/cotizacion-detalle.php` mantiene un bloque único de acciones principales.

Siempre se conserva la acción de volver al listado. Las demás acciones dependen del estado y del número oficial.

## Acciones visibles para borrador

Cuando la cotización está en estado `borrador`:

- aparece `Editar borrador`;
- aparece `Emitir cotización`;
- no aparece `Vista imprimible`;
- no aparece `Descargar PDF`.

La emisión sigue siendo un `POST` protegido con CSRF y no se modifica en esta etapa.

## Acciones visibles para emitida con número oficial

Cuando la cotización está en estado `emitida` y `numero_cotizacion` no está vacío:

- aparece `Vista imprimible`;
- aparece `Descargar PDF`;
- no aparece `Editar borrador`;
- no aparece `Emitir cotización`.

`Descargar PDF` apunta a:

```text
cotizacion-pdf.php?id={id}
```

`Vista imprimible` apunta a:

```text
cotizacion-imprimir.php?id={id}
```

## Acciones que no deben aparecer según estado

- Un borrador no debe mostrar acciones de PDF ni vista imprimible.
- Una cotización emitida no debe mostrar edición ni emisión.
- Una cotización emitida sin número oficial no debe mostrar `Vista imprimible` ni `Descargar PDF`.

Ese último caso se considera inconsistente y queda controlado visualmente al no exponer acciones comerciales de salida.

## Relación con emisión

La emisión continúa a cargo de `cotizacion-emitir.php`. Esta etapa no cambia estados, no genera números y no modifica el backend de emisión.

## Relación con vista imprimible

La vista imprimible sigue disponible solo para cotizaciones emitidas con número oficial.

## Relación con descarga PDF

La descarga PDF usa el endpoint autenticado `cotizacion-pdf.php`, que genera el PDF en memoria y valida estado emitido con número oficial.

## Por qué no se agregaron nuevas funcionalidades

Esta etapa es de revisión y cierre del flujo de acciones. No incorpora anulación, aceptación, rechazo, duplicación, correo ni nuevas operaciones de negocio.

## Qué NO se implementó

- No se implementó correo.
- No se implementó anulación.
- No se implementó duplicación.
- No se implementó edición de emitidas.
- No se modificó base de datos.
- No se modificó el endpoint PDF.
- No se modificó emisión.
- No se implementó AJAX ni API JSON.

## Herramienta CLI creada

Se creó `sistema/tools/check-quote-actions-flow-contract.php`.

La herramienta verifica:

- acciones de borrador;
- acciones de emitida;
- condiciones por `estado`;
- presencia de `numero_cotizacion` para vista imprimible y PDF;
- endpoint PDF activo;
- vista imprimible existente;
- ausencia de AJAX, API JSON, correo y escritura de archivos.

## Cómo ejecutar

```bash
php sistema/tools/check-quote-actions-flow-contract.php
```

Con Laragon:

```powershell
C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe sistema/tools/check-quote-actions-flow-contract.php
```

## Prueba manual recomendada

1. Abrir `cotizacion-detalle.php?id=1` o el id de un borrador.
2. Confirmar que aparecen `Editar borrador` y `Emitir cotización`.
3. Confirmar que no aparece `Descargar PDF`.
4. Abrir `cotizacion-detalle.php?id=3` o el id de una emitida.
5. Confirmar que aparecen `Vista imprimible` y `Descargar PDF`.
6. Confirmar que no aparecen `Editar borrador` ni `Emitir cotización`.

## Próxima etapa recomendada

7B.01 — Diagnóstico visual general de la intranet interna.
