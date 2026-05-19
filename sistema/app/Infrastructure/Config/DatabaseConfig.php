<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Infrastructure\Config;

use RuntimeException;

final class DatabaseConfig
{
    private string $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
    }

    public static function fromDefaultPath(): self
    {
        $defaultPath = __DIR__ . '/../../../config/database.php';

        return new self($defaultPath);
    }

    public function load(): array
    {
        if (!is_file($this->configPath) || !is_readable($this->configPath)) {
            throw new RuntimeException('Database configuration file is missing or inaccessible.');
        }

        $config = include $this->configPath;

        if (!is_array($config)) {
            throw new RuntimeException('Database configuration file must return an array.');
        }

        $this->validate($config);

        return $config;
    }

    private function validate(array $config): void
    {
        $requiredKeys = ['host', 'port', 'database', 'username', 'password', 'charset'];

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

        if (isset($config['options']) && !is_array($config['options'])) {
            throw new RuntimeException('Database configuration options must be an array.');
        }
    }
}
