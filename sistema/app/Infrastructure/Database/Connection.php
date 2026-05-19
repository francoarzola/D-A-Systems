<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Infrastructure\Database;

use PDO;
use PDOException;
use RuntimeException;

final class Connection
{
    private array $config;
    private ?PDO $pdo = null;

    public function __construct(array $config)
    {
        $this->config = $this->normalizeConfig($config);
        $this->validateConfig($this->config);
    }

    public function pdo(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = $this->createPdo();
        }

        return $this->pdo;
    }

    private function validateConfig(array $config): void
    {
        $requiredKeys = ['host', 'port', 'database', 'username', 'password'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $config)) {
                throw new RuntimeException('Database configuration is incomplete.');
            }

            if ($key === 'password') {
                if ($config['password'] === null || !is_string($config['password'])) {
                    throw new RuntimeException('Database configuration is incomplete.');
                }

                continue;
            }

            if ($config[$key] === '' || $config[$key] === null) {
                throw new RuntimeException('Database configuration is incomplete.');
            }
        }
    }

    private function normalizeConfig(array $config): array
    {
        $defaultOptions = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $config['port'] = isset($config['port']) ? (int) $config['port'] : 3306;
        $config['charset'] = $config['charset'] ?? 'utf8mb4';
        $config['options'] = $config['options'] ?? $defaultOptions;

        if (!is_array($config['options'])) {
            $config['options'] = $defaultOptions;
        } else {
            $config['options'] = $config['options'] + $defaultOptions;
        }

        return $config;
    }

    private function createPdo(): PDO
    {
        $dsn = $this->buildDsn();

        try {
            return new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                $this->config['options']
            );
        } catch (PDOException $exception) {
            throw new RuntimeException('Unable to establish database connection.');
        }
    }

    private function buildDsn(): string
    {
        $host = $this->config['host'];
        $port = (int) $this->config['port'];
        $database = $this->config['database'];
        $charset = $this->config['charset'];

        return sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $host,
            $port,
            $database,
            $charset
        );
    }
}
