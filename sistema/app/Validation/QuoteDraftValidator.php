<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Validation;

use DateTimeImmutable;

final class QuoteDraftValidator
{
    private const ALLOWED_ACTION = 'guardar_borrador';

    public function validateDraft(array $data): array
    {
        $errors = [];
        $warnings = [];

        $formAction = $this->stringValue($data['form_action'] ?? null);

        if ($formAction !== '' && $formAction !== self::ALLOWED_ACTION) {
            $errors[] = 'La acción del formulario no es válida para guardar borrador.';
        }

        if (array_key_exists('numero_cotizacion', $data) && $this->stringValue($data['numero_cotizacion']) !== '') {
            $warnings[] = 'El número de cotización se ignora al guardar borrador.';
        }

        $clientName = $this->stringValue($data['nombre_cliente'] ?? null);

        if ($clientName === '') {
            $errors[] = 'El nombre del cliente es obligatorio para guardar un borrador.';
        }

        $quoteDate = $this->parseDate($data['fecha_cotizacion'] ?? null);

        if ($quoteDate === null) {
            $errors[] = 'La fecha de cotización es obligatoria y debe ser válida.';
        }

        $validUntil = null;

        if ($this->stringValue($data['valido_hasta'] ?? null) !== '') {
            $validUntil = $this->parseDate($data['valido_hasta']);

            if ($validUntil === null) {
                $errors[] = 'La fecha de validez debe ser válida.';
            }
        }

        if ($quoteDate !== null && $validUntil !== null && $validUntil < $quoteDate) {
            $errors[] = 'La fecha de validez no puede ser anterior a la fecha de cotización.';
        }

        $contactEmail = $this->stringValue($data['correo_contacto'] ?? null);

        if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'El correo de contacto no tiene un formato válido.';
        }

        $details = $data['detalles'] ?? [];

        if (!is_array($details)) {
            $errors[] = 'Los detalles deben recibirse como una lista.';
            $details = [];
        }

        $validDetailCount = 0;

        foreach ($details as $index => $detail) {
            if (!is_array($detail)) {
                $errors[] = sprintf('El detalle %d debe ser una estructura válida.', (int) $index + 1);
                continue;
            }

            $detailResult = $this->validateDetail($detail, (int) $index + 1);
            $errors = array_merge($errors, $detailResult['errors']);
            $warnings = array_merge($warnings, $detailResult['warnings']);

            if ($detailResult['valid_detail']) {
                $validDetailCount++;
            }
        }

        if ($validDetailCount === 0) {
            $warnings[] = 'El borrador se puede guardar sin detalles, pero los totales quedarán en cero.';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function validateDetail(array $detail, int $lineNumber): array
    {
        $errors = [];
        $warnings = [];
        $description = $this->stringValue($detail['descripcion'] ?? null);
        $quantityRaw = $this->stringValue($detail['cantidad'] ?? null);
        $unitPriceRaw = $this->stringValue($detail['precio_unitario_neto'] ?? null);
        $discountRaw = $this->stringValue($detail['descuento_monto'] ?? null);
        $unit = $this->stringValue($detail['unidad'] ?? null);
        $hasAnyValue = $description !== ''
            || $quantityRaw !== ''
            || $unitPriceRaw !== ''
            || $discountRaw !== ''
            || $unit !== '';

        if (!$hasAnyValue) {
            return [
                'valid_detail' => false,
                'errors' => [],
                'warnings' => [],
            ];
        }

        if ($description === '') {
            $errors[] = sprintf('El detalle %d requiere descripción.', $lineNumber);
        }

        $quantity = $this->parseDecimal($quantityRaw);

        if ($quantity === null || $quantity <= 0) {
            $errors[] = sprintf('El detalle %d requiere cantidad mayor que cero.', $lineNumber);
        }

        $unitPrice = $this->parseDecimal($unitPriceRaw);

        if ($unitPrice === null || $unitPrice < 0) {
            $errors[] = sprintf('El detalle %d requiere precio unitario neto mayor o igual a cero.', $lineNumber);
        }

        $discount = $discountRaw !== '' ? $this->parseDecimal($discountRaw) : 0.00;

        if ($discount === null || $discount < 0) {
            $errors[] = sprintf('El detalle %d requiere descuento mayor o igual a cero.', $lineNumber);
        }

        if ($unit === '') {
            $warnings[] = sprintf('El detalle %d no tiene unidad informada.', $lineNumber);
        }

        if ($quantity !== null && $unitPrice !== null && $discount !== null) {
            $lineSubtotal = round($quantity * $unitPrice, 2);
            $lineTotal = round($lineSubtotal - $discount, 2);

            if ($lineTotal < 0) {
                $errors[] = sprintf('El detalle %d no puede tener total de línea negativo.', $lineNumber);
            }
        }

        return [
            'valid_detail' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function parseDate(mixed $value): ?DateTimeImmutable
    {
        $date = $this->stringValue($value);

        if ($date === '') {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = DateTimeImmutable::getLastErrors();

        if ($parsed === false || (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            return null;
        }

        return $parsed;
    }

    private function parseDecimal(mixed $value): ?float
    {
        $number = $this->stringValue($value);

        if ($number === '') {
            return null;
        }

        $normalized = str_replace(' ', '', $number);

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        if (!is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return '';
    }
}
