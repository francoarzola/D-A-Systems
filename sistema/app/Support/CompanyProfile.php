<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Support;

use RuntimeException;

final class CompanyProfile
{
    private const DEFAULT_VALUE = 'Pendiente';

    private array $data;

    public function __construct(?string $configPath = null)
    {
        $this->data = $this->load($configPath ?? $this->defaultConfigPath());
    }

    public static function fromDefaultConfig(): self
    {
        return new self();
    }

    public function all(): array
    {
        return [
            'commercial_name' => $this->commercialName(),
            'legal_name' => $this->legalName(),
            'tax_id' => $this->taxId(),
            'business_activity' => $this->businessActivity(),
            'address' => $this->address(),
            'city' => $this->city(),
            'country' => $this->country(),
            'email' => $this->email(),
            'phone' => $this->phone(),
            'website' => $this->website(),
            'default_payment_terms' => $this->defaultPaymentTerms(),
            'default_footer_note' => $this->defaultFooterNote(),
            'quote_validity_note' => $this->quoteValidityNote(),
        ];
    }

    public function commercialName(): string
    {
        return $this->value('commercial_name');
    }

    public function legalName(): string
    {
        return $this->value('legal_name');
    }

    public function taxId(): string
    {
        return $this->value('tax_id');
    }

    public function businessActivity(): string
    {
        return $this->value('business_activity');
    }

    public function address(): string
    {
        return $this->value('address');
    }

    public function city(): string
    {
        return $this->value('city');
    }

    public function country(): string
    {
        return $this->value('country');
    }

    public function email(): string
    {
        return $this->value('email');
    }

    public function phone(): string
    {
        return $this->value('phone');
    }

    public function website(): string
    {
        return $this->value('website');
    }

    public function defaultPaymentTerms(): string
    {
        return $this->value('default_payment_terms');
    }

    public function defaultFooterNote(): string
    {
        return $this->value('default_footer_note');
    }

    public function quoteValidityNote(): string
    {
        return $this->value('quote_validity_note');
    }

    private function load(string $configPath): array
    {
        if (!is_file($configPath) || !is_readable($configPath)) {
            throw new RuntimeException('Company profile configuration file is missing or inaccessible.');
        }

        $config = require $configPath;

        if (!is_array($config)) {
            throw new RuntimeException('Company profile configuration must return an array.');
        }

        return $config;
    }

    private function value(string $key): string
    {
        $value = $this->data[$key] ?? null;

        if (!is_scalar($value)) {
            return self::DEFAULT_VALUE;
        }

        $value = trim((string) $value);

        return $value !== '' ? $value : self::DEFAULT_VALUE;
    }

    private function defaultConfigPath(): string
    {
        return __DIR__ . '/../../config/company.php';
    }
}
