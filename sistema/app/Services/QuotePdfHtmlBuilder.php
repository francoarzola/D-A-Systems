<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Services;

use InvalidArgumentException;

final class QuotePdfHtmlBuilder
{
    public function build(array $quote, array $companyProfile): string
    {
        if ($quote === []) {
            throw new InvalidArgumentException('La cotización para construir PDF no puede estar vacía.');
        }

        if ($companyProfile === []) {
            throw new InvalidArgumentException('Los datos comerciales para construir PDF no pueden estar vacíos.');
        }

        $details = isset($quote['details']) && is_array($quote['details']) ? $quote['details'] : [];

        return '<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Cotización ' . $this->e($quote['numero_cotizacion'] ?? '') . '</title>
  <style>
    @page { size: A4; margin: 14mm; }
    body { font-family: DejaVu Sans, sans-serif; color: #1f2937; font-size: 11px; line-height: 1.45; }
    h1, h2, h3, p { margin: 0; }
    .header { border-bottom: 2px solid #111827; padding-bottom: 12px; margin-bottom: 18px; }
    .brand { font-size: 22px; font-weight: bold; }
    .muted { color: #6b7280; }
    .quote-title { text-align: right; font-size: 20px; font-weight: bold; }
    .quote-number { text-align: right; font-size: 13px; margin-top: 3px; }
    .grid { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
    .grid td { vertical-align: top; width: 50%; padding: 0 10px 0 0; }
    .panel { border: 1px solid #d1d5db; padding: 10px; margin-bottom: 14px; }
    .panel-title { font-size: 13px; font-weight: bold; margin-bottom: 8px; color: #111827; }
    .line { margin-bottom: 3px; }
    .label { font-weight: bold; }
    .items { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 14px; }
    .items th, .items td { border: 1px solid #d1d5db; padding: 7px; }
    .items th { background: #f3f4f6; color: #111827; text-align: left; }
    .right { text-align: right; }
    .center { text-align: center; }
    .summary { width: 44%; margin-left: auto; border-collapse: collapse; margin-bottom: 16px; }
    .summary td { border-bottom: 1px solid #e5e7eb; padding: 6px; }
    .summary .total td { border-top: 2px solid #111827; border-bottom: 0; font-size: 15px; font-weight: bold; }
    .notes { margin-top: 12px; }
    .footer { border-top: 1px solid #d1d5db; margin-top: 18px; padding-top: 10px; font-size: 10px; color: #4b5563; }
  </style>
</head>
<body>
  <table class="grid header">
    <tr>
      <td>
        <div class="brand">' . $this->e($companyProfile['commercial_name'] ?? null) . '</div>
        <div class="muted">' . $this->e($companyProfile['business_activity'] ?? null) . '</div>
      </td>
      <td>
        <div class="quote-title">Cotización</div>
        <div class="quote-number">' . $this->e($quote['numero_cotizacion'] ?? null) . '</div>
      </td>
    </tr>
  </table>

  <table class="grid">
    <tr>
      <td>
        <div class="panel">
          <div class="panel-title">Datos empresa</div>
          <div class="line"><span class="label">Razón social:</span> ' . $this->e($companyProfile['legal_name'] ?? null) . '</div>
          <div class="line"><span class="label">RUT:</span> ' . $this->e($companyProfile['tax_id'] ?? null) . '</div>
          <div class="line"><span class="label">Giro:</span> ' . $this->e($companyProfile['business_activity'] ?? null) . '</div>
          <div class="line"><span class="label">Correo:</span> ' . $this->e($companyProfile['email'] ?? null) . '</div>
          <div class="line"><span class="label">Teléfono:</span> ' . $this->e($companyProfile['phone'] ?? null) . '</div>
        </div>
      </td>
      <td>
        <div class="panel">
          <div class="panel-title">Datos cotización</div>
          <div class="line"><span class="label">Estado:</span> ' . $this->e($quote['estado'] ?? null) . '</div>
          <div class="line"><span class="label">Fecha:</span> ' . $this->e($this->formatDate($quote['fecha_cotizacion'] ?? null)) . '</div>
          <div class="line"><span class="label">Validez:</span> ' . $this->e($this->formatDate($quote['valido_hasta'] ?? null)) . '</div>
        </div>
      </td>
    </tr>
  </table>

  <div class="panel">
    <div class="panel-title">Cliente</div>
    <div class="line"><span class="label">Nombre:</span> ' . $this->e($quote['nombre_cliente'] ?? null) . '</div>
    <div class="line"><span class="label">RUT:</span> ' . $this->e($quote['rut_cliente'] ?? null) . '</div>
    <div class="line"><span class="label">Contacto:</span> ' . $this->e($quote['nombre_contacto'] ?? null) . '</div>
    <div class="line"><span class="label">Correo:</span> ' . $this->e($quote['correo_contacto'] ?? null) . '</div>
    <div class="line"><span class="label">Teléfono:</span> ' . $this->e($quote['telefono_contacto'] ?? null) . '</div>
  </div>

  <div class="panel">
    <div class="panel-title">Descripción</div>
    <p>' . $this->e($quote['descripcion'] ?? null) . '</p>
  </div>

  <div class="panel">
    <div class="panel-title">Detalle</div>
    <table class="items">
      <thead>
        <tr>
          <th class="center">Línea</th>
          <th>Descripción</th>
          <th class="right">Cantidad</th>
          <th>Unidad</th>
          <th class="right">Precio unitario neto</th>
          <th class="right">Descuento</th>
          <th class="right">Total línea neto</th>
        </tr>
      </thead>
      <tbody>
        ' . $this->renderRows($details) . '
      </tbody>
    </table>
  </div>

  <table class="summary">
    <tr><td>Subtotal neto</td><td class="right">' . $this->e($this->money($quote['subtotal_neto'] ?? null)) . '</td></tr>
    <tr><td>Descuento</td><td class="right">' . $this->e($this->money($quote['descuento_monto'] ?? null)) . '</td></tr>
    <tr><td>IVA ' . $this->e((string) (float) ($quote['iva_porcentaje'] ?? 0)) . '%</td><td class="right">' . $this->e($this->money($quote['iva_monto'] ?? null)) . '</td></tr>
    <tr class="total"><td>Total</td><td class="right">' . $this->e($this->money($quote['total'] ?? null)) . '</td></tr>
  </table>

  <div class="panel notes">
    <div class="panel-title">Condiciones comerciales</div>
    <p>' . $this->e($quote['condiciones_comerciales'] ?? $companyProfile['default_payment_terms'] ?? null) . '</p>
  </div>

  <div class="panel notes">
    <div class="panel-title">Observaciones</div>
    <p>' . $this->e($quote['observaciones'] ?? null) . '</p>
  </div>

  <div class="footer">
    <p>' . $this->e($companyProfile['quote_validity_note'] ?? null) . '</p>
    <p>' . $this->e($companyProfile['default_footer_note'] ?? null) . '</p>
  </div>
</body>
</html>';
    }

    private function e(mixed $value): string
    {
        return htmlspecialchars($this->textOrPending($value), ENT_QUOTES, 'UTF-8');
    }

    private function money(mixed $value): string
    {
        return '$' . number_format((float) $value, 0, ',', '.');
    }

    private function textOrPending(mixed $value): string
    {
        if ($value === null || !is_scalar($value)) {
            return 'Pendiente';
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : 'Pendiente';
    }

    private function formatDate(mixed $value): string
    {
        $date = $this->textOrPending($value);

        if ($date === 'Pendiente') {
            return $date;
        }

        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = \DateTimeImmutable::getLastErrors();

        if ($parsed === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return $date;
        }

        return $parsed->format('d-m-Y');
    }

    private function renderRows(array $details): string
    {
        if ($details === []) {
            return '<tr><td colspan="7" class="center">Sin detalles registrados</td></tr>';
        }

        $rows = [];

        foreach ($details as $detail) {
            if (!is_array($detail)) {
                continue;
            }

            $rows[] = '<tr>
          <td class="center">' . $this->e($detail['numero_linea'] ?? null) . '</td>
          <td>' . $this->e($detail['descripcion'] ?? null) . '</td>
          <td class="right">' . $this->e((string) (float) ($detail['cantidad'] ?? 0)) . '</td>
          <td>' . $this->e($detail['unidad'] ?? null) . '</td>
          <td class="right">' . $this->e($this->money($detail['precio_unitario_neto'] ?? null)) . '</td>
          <td class="right">' . $this->e($this->money($detail['descuento_monto'] ?? null)) . '</td>
          <td class="right">' . $this->e($this->money($detail['total_linea_neto'] ?? null)) . '</td>
        </tr>';
        }

        if ($rows === []) {
            return '<tr><td colspan="7" class="center">Sin detalles registrados</td></tr>';
        }

        return implode("\n", $rows);
    }
}
