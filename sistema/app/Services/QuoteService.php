<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Services;

use DAndASystems\Internal\Repositories\QuoteRepository;

final class QuoteService
{
    private QuoteRepository $quotes;

    public function __construct(QuoteRepository $quotes)
    {
        $this->quotes = $quotes;
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
}
