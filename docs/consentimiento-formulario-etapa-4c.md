# Consentimiento de formulario - Etapa 4c

## Objetivo de la etapa
Agregar consentimiento explícito de privacidad al formulario de contacto de D&A Systems, respetando el diseño existente, la navegación y las buenas prácticas de protección de datos para empresas TI en Chile.

## Archivos modificados
- `index.html`
- `forms/contact.php`
- `docs/consentimiento-formulario-etapa-4c.md`

## Campo agregado en frontend
Se agregó un checkbox requerido con los atributos:
- `name="privacy_consent"`
- `id="privacyConsent"`
- `value="accepted"`
- `required`

Texto visible:
> Acepto que D&A Systems SpA trate mis datos para responder mi solicitud, conforme a la Política de privacidad.

El enlace a la `Política de privacidad` apunta a `politica-privacidad.html` y no abre en una nueva pestaña.

## Validación agregada en backend
En `forms/contact.php` se agregó la validación que comprueba que:
- `$_POST['privacy_consent']` exista
- el valor sea exactamente `accepted`

Si la validación falla, el servidor responde con HTTP 400 y el mensaje:
> Debes aceptar la política de privacidad para enviar la solicitud.

## Relación con política de privacidad
El consentimiento está vinculado a `politica-privacidad.html`, permitiendo que el usuario revise la política antes de enviar el formulario.

## Pruebas recomendadas en hosting/cPanel
- Verificar que el checkbox aparece en el formulario de contacto.
- Intentar enviar el formulario sin marcar el checkbox y confirmar que el servidor devuelve el mensaje de error.
- Enviar el formulario con el checkbox marcado y comprobar que el envío se procesa correctamente.
- Confirmar que el link de `Política de privacidad` abre en la misma pestaña y apunta a `politica-privacidad.html`.
- Revisar que no se han modificado archivos fuera de los permitidos.
