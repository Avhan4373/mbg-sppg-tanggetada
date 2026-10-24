<?php

namespace App\Support;

use Carbon\CarbonInterface;

class NumberFormat
{
    public static function id(mixed $value, int $decimals = 2): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    public static function compact(mixed $value, int $decimals = 0): string
    {
        $value = (float) $value;

        if (abs($value) >= 1_000_000_000) {
            return self::id($value / 1_000_000_000, 1) . ' M';
        }

        if (abs($value) >= 1_000_000) {
            return self::id($value / 1_000_000, 1) . ' jt';
        }

        if (abs($value) >= 1_000) {
            return self::id($value / 1_000, 1) . ' rb';
        }

        return self::id($value, $decimals);
    }

    public static function rupiah(mixed $value, int $decimals = 0): string
    {
        return 'Rp ' . self::id($value, $decimals);
    }

    public static function percent(mixed $value, int $decimals = 2): string
    {
        return self::id($value, $decimals) . '%';
    }

    public static function date(mixed $value, string $format = 'd M Y'): string
    {
        if ($value instanceof CarbonInterface) {
            return $value->translatedFormat($format);
        }

        if (blank($value)) {
            return '-';
        }

        return \Carbon\CarbonImmutable::parse($value)->translatedFormat($format);
    }

    public static function dateTime(mixed $value): string
    {
        return self::date($value, 'd M Y H:i');
    }
}
