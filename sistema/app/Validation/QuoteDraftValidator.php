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

        if (array_key_exists('quote_number', $data) && $this->stringValue($data['quote_number']) !== '') {
            $warnings[] = 'El número de cotización se ignora al guardar borrador.';
        }

        $clientName = $this->stringValue($data['client_name'] ?? null);

        if ($clientName === '') {
            $errors[] = 'El nombre del cliente es obligatorio para guardar un borrador.';
        }

        $quoteDate = $this->parseDate($data['quote_date'] ?? null);

        if ($quoteDate === null) {
            $errors[] = 'La fecha de cotización es obligatoria y debe ser válida.';
        }

        $validUntil = null;

        if ($this->stringValue($data['valid_until'] ?? null) !== '') {
            $validUntil = $this->parseDate($data['valid_until']);

            if ($validUntil === null) {
                $errors[] = 'La fecha de validez debe ser válida.';
            }
        }

        if ($quoteDate !== null && $validUntil !== null && $validUntil < $quoteDate) {
            $errors[] = 'La fecha de validez no puede ser anterior a la fecha de cotización.';
        }

        $contactEmail = $this->stringValue($data['contact_email'] ?? null);

        if ($contactEmail !== '' && filter_var($contactEmail, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = 'El correo de contacto no tiene un formato válido.';
        }

        $items = $data['items'] ?? [];

        if (!is_array($items)) {
            $errors[] = 'Los ítems deben recibirse como una lista.';
            $items = [];
        }

        $validItemCount = 0;

        foreach ($items as $index => $item) {
            if (!is_array($item)) {
                $errors[] = sprintf('El ítem %d debe ser una estructura válida.', (int) $index + 1);
                continue;
            }

            $itemResult = $this->validateItem($item, (int) $index + 1);
            $errors = array_merge($errors, $itemResult['errors']);
            $warnings = array_merge($warnings, $itemResult['warnings']);

            if ($itemResult['valid_item']) {
                $validItemCount++;
            }
        }

        if ($validItemCount === 0) {
            $warnings[] = 'El borrador se puede guardar sin ítems, pero los totales quedarán en cero.';
        }

        return [
            'valid' => $errors === [],
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function validateItem(array $item, int $lineNumber): array
    {
        $errors = [];
        $warnings = [];
        $description = $this->stringValue($item['description'] ?? null);
        $quantityRaw = $this->stringValue($item['quantity'] ?? null);
        $unitPriceRaw = $this->stringValue($item['unit_price_net'] ?? null);
        $discountRaw = $this->stringValue($item['discount_amount'] ?? null);
        $unit = $this->stringValue($item['unit'] ?? null);
        $hasAnyValue = $description !== ''
            || $quantityRaw !== ''
            || $unitPriceRaw !== ''
            || $discountRaw !== ''
            || $unit !== '';

        if (!$hasAnyValue) {
            return [
                'valid_item' => false,
                'errors' => [],
                'warnings' => [],
            ];
        }

        if ($description === '') {
            $errors[] = sprintf('El ítem %d requiere descripción.', $lineNumber);
        }

        $quantity = $this->parseDecimal($quantityRaw);

        if ($quantity === null || $quantity <= 0) {
            $errors[] = sprintf('El ítem %d requiere cantidad mayor que cero.', $lineNumber);
        }

        $unitPrice = $this->parseDecimal($unitPriceRaw);

        if ($unitPrice === null || $unitPrice < 0) {
            $errors[] = sprintf('El ítem %d requiere precio unitario neto mayor o igual a cero.', $lineNumber);
        }

        $discount = $discountRaw !== '' ? $this->parseDecimal($discountRaw) : 0.00;

        if ($discount === null || $discount < 0) {
            $errors[] = sprintf('El ítem %d requiere descuento mayor o igual a cero.', $lineNumber);
        }

        if ($unit === '') {
            $warnings[] = sprintf('El ítem %d no tiene unidad informada.', $lineNumber);
        }

        if ($quantity !== null && $unitPrice !== null && $discount !== null) {
            $lineSubtotal = $quantity * $unitPrice;
            $lineTotal = $lineSubtotal - $discount;

            if ($lineTotal < 0) {
                $errors[] = sprintf('El ítem %d no puede tener total de línea negativo.', $lineNumber);
            }
        }

        return [
            'valid_item' => $errors === [],
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
