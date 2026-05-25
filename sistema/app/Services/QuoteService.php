<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Services;

use DAndASystems\Internal\Repositories\QuoteRepository;
use DAndASystems\Internal\Validation\QuoteDraftValidator;

final class QuoteService
{
    private QuoteRepository $quotes;
    private ?QuoteDraftValidator $draftValidator;
    private ?QuoteTotalsCalculator $totalsCalculator;

    public function __construct(
        QuoteRepository $quotes,
        ?QuoteDraftValidator $draftValidator = null,
        ?QuoteTotalsCalculator $totalsCalculator = null
    ) {
        $this->quotes = $quotes;
        $this->draftValidator = $draftValidator;
        $this->totalsCalculator = $totalsCalculator;
    }

    public function countQuotes(): int
    {
        return $this->quotes->countAll();
    }

    public function getRecentQuotes(int $limit = 10): array
    {
        return $this->quotes->findRecent($limit);
    }

    public function getQuoteDetail(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $quote = $this->quotes->findById($id);

        if ($quote === null) {
            return null;
        }

        return [
            'quote' => $quote,
            'details' => $this->quotes->findDetailsByQuoteId($id),
        ];
    }

    public function createDraft(array $draftData, ?int $createdBy = null): array
    {
        $validation = $this->draftValidator()->validateDraft($draftData);

        if ($validation['valid'] !== true) {
            return [
                'success' => false,
                'quote_id' => null,
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings'],
                'totals' => null,
            ];
        }

        $totals = $this->totalsCalculator()->calculate($draftData['detalles'] ?? []);
        $quoteId = $this->quotes->createDraft($draftData, $totals, $createdBy);

        return [
            'success' => true,
            'quote_id' => $quoteId,
            'errors' => [],
            'warnings' => $validation['warnings'],
            'totals' => $totals,
        ];
    }

    public function updateDraft(int $quoteId, array $draftData): array
    {
        if ($quoteId <= 0) {
            return [
                'success' => false,
                'quote_id' => null,
                'errors' => ['La cotización solicitada no es válida.'],
                'warnings' => [],
                'totals' => null,
            ];
        }

        $validation = $this->draftValidator()->validateDraft($draftData);

        if ($validation['valid'] !== true) {
            return [
                'success' => false,
                'quote_id' => $quoteId,
                'errors' => $validation['errors'],
                'warnings' => $validation['warnings'],
                'totals' => null,
            ];
        }

        $totals = $this->totalsCalculator()->calculate($draftData['detalles'] ?? []);
        $updated = $this->quotes->updateDraft($quoteId, $draftData, $totals);

        if (!$updated) {
            return [
                'success' => false,
                'quote_id' => $quoteId,
                'errors' => ['Solo se pueden actualizar cotizaciones en estado borrador.'],
                'warnings' => $validation['warnings'],
                'totals' => $totals,
            ];
        }

        return [
            'success' => true,
            'quote_id' => $quoteId,
            'errors' => [],
            'warnings' => $validation['warnings'],
            'totals' => $totals,
        ];
    }

    public function issueDraft(int $quoteId): array
    {
        if ($quoteId <= 0) {
            return [
                'success' => false,
                'quote_id' => $quoteId,
                'numero_cotizacion' => null,
                'estado' => null,
                'errors' => ['La cotizacion solicitada no es valida.'],
            ];
        }

        $issued = $this->quotes->issueDraft($quoteId);

        if ($issued === null) {
            return [
                'success' => false,
                'quote_id' => $quoteId,
                'numero_cotizacion' => null,
                'estado' => null,
                'errors' => ['Solo se pueden emitir cotizaciones en estado borrador.'],
            ];
        }

        return [
            'success' => true,
            'quote_id' => (int) $issued['id'],
            'numero_cotizacion' => $issued['numero_cotizacion'],
            'estado' => 'emitida',
            'errors' => [],
        ];
    }

    private function draftValidator(): QuoteDraftValidator
    {
        if (!$this->draftValidator instanceof QuoteDraftValidator) {
            $this->draftValidator = new QuoteDraftValidator();
        }

        return $this->draftValidator;
    }

    private function totalsCalculator(): QuoteTotalsCalculator
    {
        if (!$this->totalsCalculator instanceof QuoteTotalsCalculator) {
            $this->totalsCalculator = new QuoteTotalsCalculator();
        }

        return $this->totalsCalculator;
    }
}
