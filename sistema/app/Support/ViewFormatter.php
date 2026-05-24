<?php

declare(strict_types=1);

namespace DAndASystems\Internal\Support;

final class ViewFormatter
{
    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function text(mixed $value): string
    {
        $text = is_string($value) ? trim($value) : '';

        return $text !== '' ? $text : 'Pendiente';
    }

    public static function quoteNumber(mixed $value): string
    {
        $number = is_string($value) ? trim($value) : '';

        return $number !== '' ? $number : 'Sin emitir';
    }

    public static function quoteDate(mixed $value): string
    {
        $date = is_string($value) ? trim($value) : '';

        return $date !== '' ? $date : 'Pendiente';
    }

    public static function quoteStatus(mixed $value): string
    {
        $status = is_string($value) ? trim($value) : '';

        return $status !== '' ? ucfirst($status) : 'Sin estado';
    }

    public static function money(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '$0';
        }

        return '$' . number_format((float) $value, 0, ',', '.');
    }

    public static function percent(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '0%';
        }

        return number_format((float) $value, 2, ',', '.') . '%';
    }

    public static function quantity(mixed $value): string
    {
        if (!is_numeric($value)) {
            return '0';
        }

        return number_format((float) $value, 2, ',', '.');
    }
}
