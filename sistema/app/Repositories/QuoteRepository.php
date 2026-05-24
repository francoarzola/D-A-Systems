<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Repositories;

use PDO;

final class QuoteRepository
{
    private const READ_COLUMNS = [
        'id',
        'numero_cotizacion',
        'fecha_cotizacion',
        'valido_hasta',
        'nombre_cliente',
        'rut_cliente',
        'nombre_contacto',
        'correo_contacto',
        'telefono_contacto',
        'descripcion',
        'estado',
        'subtotal_neto',
        'descuento_monto',
        'iva_porcentaje',
        'iva_monto',
        'total',
        'condiciones_comerciales',
        'observaciones',
        'creado_en',
        'actualizado_en',
    ];

    private const DETAIL_COLUMNS = [
        'numero_linea',
        'descripcion',
        'cantidad',
        'unidad',
        'precio_unitario_neto',
        'descuento_monto',
        'subtotal_linea_neto',
        'total_linea_neto',
    ];

    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function countAll(): int
    {
        $statement = $this->pdo->query('SELECT COUNT(*) FROM cotizaciones');

        if ($statement === false) {
            return 0;
        }

        return (int) $statement->fetchColumn();
    }

    public function findRecent(int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));
        $columns = $this->readColumnsSql();

        $statement = $this->pdo->prepare(
            "SELECT {$columns}
             FROM cotizaciones
             ORDER BY creado_en DESC, id DESC
             LIMIT :limit"
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function findById(int $id): ?array
    {
        if ($id <= 0) {
            return null;
        }

        $columns = $this->readColumnsSql();
        $statement = $this->pdo->prepare(
            "SELECT {$columns}
             FROM cotizaciones
             WHERE id = :id
             LIMIT 1"
        );
        $statement->bindValue(':id', $id, PDO::PARAM_INT);
        $statement->execute();

        $quote = $statement->fetch();

        return is_array($quote) ? $quote : null;
    }

    public function findDetailsByQuoteId(int $quoteId): array
    {
        if ($quoteId <= 0) {
            return [];
        }

        $columns = $this->detailColumnsSql();
        $statement = $this->pdo->prepare(
            "SELECT {$columns}
             FROM cotizacion_detalles
             WHERE cotizacion_id = :quote_id
             ORDER BY numero_linea ASC, id ASC"
        );
        $statement->bindValue(':quote_id', $quoteId, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    public function draftExistsByClientAndDescription(string $clientName, string $description): bool
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM cotizaciones
             WHERE nombre_cliente = :nombre_cliente
               AND estado = :estado
               AND descripcion = :descripcion'
        );
        $statement->execute([
            'nombre_cliente' => $clientName,
            'estado' => 'borrador',
            'descripcion' => $description,
        ]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function createDraft(array $header, array $calculatedTotals, ?int $createdBy = null): int
    {
        $this->pdo->beginTransaction();

        try {
            $quoteId = $this->insertDraftHeader($header, $calculatedTotals, $createdBy);
            $this->insertDraftDetails($quoteId, $calculatedTotals['details'] ?? []);
            $this->pdo->commit();

            return $quoteId;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    private function readColumnsSql(): string
    {
        return implode(', ', self::READ_COLUMNS);
    }

    private function detailColumnsSql(): string
    {
        return implode(', ', self::DETAIL_COLUMNS);
    }

    private function insertDraftHeader(array $header, array $calculatedTotals, ?int $createdBy): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO cotizaciones (
                numero_cotizacion,
                fecha_cotizacion,
                valido_hasta,
                nombre_cliente,
                rut_cliente,
                nombre_contacto,
                correo_contacto,
                telefono_contacto,
                descripcion,
                estado,
                subtotal_neto,
                descuento_monto,
                iva_porcentaje,
                iva_monto,
                total,
                condiciones_comerciales,
                observaciones,
                creado_por
            ) VALUES (
                NULL,
                :fecha_cotizacion,
                :valido_hasta,
                :nombre_cliente,
                :rut_cliente,
                :nombre_contacto,
                :correo_contacto,
                :telefono_contacto,
                :descripcion,
                :estado,
                :subtotal_neto,
                :descuento_monto,
                :iva_porcentaje,
                :iva_monto,
                :total,
                :condiciones_comerciales,
                :observaciones,
                :creado_por
            )'
        );
        $statement->execute([
            'fecha_cotizacion' => $this->dateValue($header['fecha_cotizacion'] ?? null) ?? date('Y-m-d'),
            'valido_hasta' => $this->dateValue($header['valido_hasta'] ?? null),
            'nombre_cliente' => $this->stringOrNull($header['nombre_cliente'] ?? null),
            'rut_cliente' => $this->stringOrNull($header['rut_cliente'] ?? null),
            'nombre_contacto' => $this->stringOrNull($header['nombre_contacto'] ?? null),
            'correo_contacto' => $this->stringOrNull($header['correo_contacto'] ?? null),
            'telefono_contacto' => $this->stringOrNull($header['telefono_contacto'] ?? null),
            'descripcion' => $this->stringOrNull($header['descripcion'] ?? null),
            'estado' => 'borrador',
            'subtotal_neto' => $this->decimalValue($calculatedTotals['subtotal_neto'] ?? 0.00),
            'descuento_monto' => $this->decimalValue($calculatedTotals['descuento_monto'] ?? 0.00),
            'iva_porcentaje' => $this->decimalValue($calculatedTotals['iva_porcentaje'] ?? 0.00),
            'iva_monto' => $this->decimalValue($calculatedTotals['iva_monto'] ?? 0.00),
            'total' => $this->decimalValue($calculatedTotals['total'] ?? 0.00),
            'condiciones_comerciales' => $this->stringOrNull($header['condiciones_comerciales'] ?? null),
            'observaciones' => $this->stringOrNull($header['observaciones'] ?? null),
            'creado_por' => $createdBy,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function insertDraftDetails(int $quoteId, array $details): void
    {
        if ($details === []) {
            return;
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO cotizacion_detalles (
                cotizacion_id,
                numero_linea,
                descripcion,
                cantidad,
                unidad,
                precio_unitario_neto,
                descuento_monto,
                subtotal_linea_neto,
                total_linea_neto
            ) VALUES (
                :cotizacion_id,
                :numero_linea,
                :descripcion,
                :cantidad,
                :unidad,
                :precio_unitario_neto,
                :descuento_monto,
                :subtotal_linea_neto,
                :total_linea_neto
            )'
        );

        foreach ($details as $detail) {
            if (!is_array($detail)) {
                continue;
            }

            $statement->execute([
                'cotizacion_id' => $quoteId,
                'numero_linea' => (int) ($detail['numero_linea'] ?? 0),
                'descripcion' => $this->stringOrNull($detail['descripcion'] ?? null),
                'cantidad' => $this->decimalValue($detail['cantidad'] ?? 0.00),
                'unidad' => $this->stringOrNull($detail['unidad'] ?? null),
                'precio_unitario_neto' => $this->decimalValue($detail['precio_unitario_neto'] ?? 0.00),
                'descuento_monto' => $this->decimalValue($detail['descuento_monto'] ?? 0.00),
                'subtotal_linea_neto' => $this->decimalValue($detail['subtotal_linea_neto'] ?? 0.00),
                'total_linea_neto' => $this->decimalValue($detail['total_linea_neto'] ?? 0.00),
            ]);
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text !== '' ? $text : null;
    }

    private function dateValue(mixed $value): ?string
    {
        $date = $this->stringOrNull($value);

        return $date !== null ? $date : null;
    }

    private function decimalValue(mixed $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
