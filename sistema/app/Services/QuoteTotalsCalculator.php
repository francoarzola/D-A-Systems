<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Services;

final class QuoteTotalsCalculator
{
    public function calculate(array $details, float $headerDiscount = 0.00, float $taxRate = 19.00): array
    {
        $calculatedDetails = [];
        $lineNumber = 1;

        foreach ($details as $detail) {
            if (!is_array($detail) || $this->isEmptyDetail($detail)) {
                continue;
            }

            $quantity = $this->parseDecimal($detail['cantidad'] ?? 0.00);
            $unitPrice = $this->parseDecimal($detail['precio_unitario_neto'] ?? 0.00);
            $lineDiscount = $this->parseDecimal($detail['descuento_monto'] ?? 0.00);
            $lineSubtotal = $this->roundMoney($quantity * $unitPrice);
            $lineTotal = $this->roundMoney($lineSubtotal - $lineDiscount);

            $calculatedDetails[] = [
                'numero_linea' => $lineNumber,
                'descripcion' => $this->stringValue($detail['descripcion'] ?? null),
                'cantidad' => $this->roundQuantity($quantity),
                'unidad' => $this->stringValue($detail['unidad'] ?? null),
                'precio_unitario_neto' => $this->roundMoney($unitPrice),
                'descuento_monto' => $this->roundMoney($lineDiscount),
                'subtotal_linea_neto' => $lineSubtotal,
                'total_linea_neto' => $lineTotal,
            ];

            $lineNumber++;
        }

        $subtotalNet = $this->roundMoney(array_sum(array_column($calculatedDetails, 'total_linea_neto')));
        $headerDiscount = $this->roundMoney($headerDiscount);
        $taxRate = $this->roundMoney($taxRate);
        $taxableBase = $this->roundMoney($subtotalNet - $headerDiscount);
        $taxAmount = $this->roundMoney($taxableBase * ($taxRate / 100));
        $total = $this->roundMoney($taxableBase + $taxAmount);

        return [
            'details' => $calculatedDetails,
            'subtotal_neto' => $subtotalNet,
            'descuento_monto' => $headerDiscount,
            'iva_porcentaje' => $taxRate,
            'iva_monto' => $taxAmount,
            'total' => $total,
        ];
    }

    private function isEmptyDetail(array $detail): bool
    {
        return $this->stringValue($detail['descripcion'] ?? null) === ''
            && $this->stringValue($detail['cantidad'] ?? null) === ''
            && $this->stringValue($detail['unidad'] ?? null) === ''
            && $this->stringValue($detail['precio_unitario_neto'] ?? null) === ''
            && $this->stringValue($detail['descuento_monto'] ?? null) === '';
    }

    private function parseDecimal(mixed $value): float
    {
        $number = $this->stringValue($value);

        if ($number === '') {
            return 0.00;
        }

        $normalized = str_replace(' ', '', $number);

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (str_contains($normalized, ',')) {
            $normalized = str_replace(',', '.', $normalized);
        }

        if (!is_numeric($normalized)) {
            return 0.00;
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

    private function roundMoney(float $value): float
    {
        return round($value, 2);
    }

    private function roundQuantity(float $value): float
    {
        return round($value, 4);
    }
}
