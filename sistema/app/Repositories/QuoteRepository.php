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

    private function readColumnsSql(): string
    {
        return implode(', ', self::READ_COLUMNS);
    }

    private function detailColumnsSql(): string
    {
        return implode(', ', self::DETAIL_COLUMNS);
    }
}
