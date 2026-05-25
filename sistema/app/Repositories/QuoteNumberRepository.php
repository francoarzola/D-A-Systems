<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Repositories;

use InvalidArgumentException;
use PDO;

final class QuoteNumberRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function reserveNextNumber(string $documentType, int $year): string
    {
        $this->pdo->beginTransaction();

        try {
            $quoteNumber = $this->reserveNextNumberInCurrentTransaction($documentType, $year);
            $this->pdo->commit();

            return $quoteNumber;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function reserveNextNumberInCurrentTransaction(string $documentType, int $year): string
    {
        $documentType = $this->normalizeDocumentType($documentType);
        $this->validateYear($year);

        $this->createCounterIfMissing($documentType, $year);

        $lastNumber = $this->findLastNumberForUpdate($documentType, $year);

        if ($lastNumber === null) {
            throw new InvalidArgumentException('No fue posible reservar el correlativo.');
        }

        $nextNumber = $lastNumber + 1;
        $this->updateCounter($documentType, $year, $nextNumber);

        return $this->formatQuoteNumber($documentType, $year, $nextNumber);
    }

    private function findLastNumberForUpdate(string $documentType, int $year): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT ultimo_numero
             FROM cotizacion_correlativos
             WHERE tipo_documento = :tipo_documento
               AND anio = :anio
             LIMIT 1
             FOR UPDATE'
        );
        $statement->execute([
            'tipo_documento' => $documentType,
            'anio' => $year,
        ]);

        $lastNumber = $statement->fetchColumn();

        return $lastNumber !== false ? (int) $lastNumber : null;
    }

    private function createCounterIfMissing(string $documentType, int $year): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO cotizacion_correlativos (
                tipo_documento,
                anio,
                ultimo_numero
            ) VALUES (
                :tipo_documento,
                :anio,
                0
            )
            ON DUPLICATE KEY UPDATE ultimo_numero = ultimo_numero'
        );
        $statement->execute([
            'tipo_documento' => $documentType,
            'anio' => $year,
        ]);
    }

    private function updateCounter(string $documentType, int $year, int $lastNumber): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE cotizacion_correlativos
             SET ultimo_numero = :ultimo_numero
             WHERE tipo_documento = :tipo_documento
               AND anio = :anio'
        );
        $statement->execute([
            'ultimo_numero' => $lastNumber,
            'tipo_documento' => $documentType,
            'anio' => $year,
        ]);
    }

    private function formatQuoteNumber(string $documentType, int $year, int $number): string
    {
        return sprintf('%s-%d-%04d', $documentType, $year, $number);
    }

    private function normalizeDocumentType(string $documentType): string
    {
        $documentType = strtoupper(trim($documentType));

        if ($documentType === '' || !preg_match('/^[A-Z0-9]{2,10}$/', $documentType)) {
            throw new InvalidArgumentException('El tipo de documento no es válido.');
        }

        return $documentType;
    }

    private function validateYear(int $year): void
    {
        if ($year < 2000 || $year > 2100) {
            throw new InvalidArgumentException('El año del correlativo no es válido.');
        }
    }
}
