<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Services;

use InvalidArgumentException;

final class QuotePdfService
{
    public function canGeneratePdf(array $quote): bool
    {
        $status = is_string($quote['estado'] ?? null) ? trim($quote['estado']) : '';
        $quoteNumber = is_string($quote['numero_cotizacion'] ?? null) ? trim($quote['numero_cotizacion']) : '';

        return $status === 'emitida' && $quoteNumber !== '';
    }

    public function assertCanGeneratePdf(array $quote): void
    {
        if ($this->canGeneratePdf($quote)) {
            return;
        }

        throw new InvalidArgumentException('La cotización debe estar emitida y tener número oficial para preparar su archivo.');
    }

    public function buildPdfFilename(array $quote): string
    {
        $this->assertCanGeneratePdf($quote);

        $quoteNumber = is_string($quote['numero_cotizacion'] ?? null) ? trim($quote['numero_cotizacion']) : '';
        $filename = $this->sanitizeFilename($quoteNumber);

        if ($filename === '') {
            $filename = 'cotizacion';
        }

        return $filename . '.pdf';
    }

    private function sanitizeFilename(string $value): string
    {
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '-', $value);

        if (!is_string($value)) {
            return '';
        }

        return trim($value, '.-_');
    }
}
