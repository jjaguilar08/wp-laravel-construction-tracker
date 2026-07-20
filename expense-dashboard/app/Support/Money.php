<?php

namespace App\Support;

/**
 * Formats an amount using a currency symbol rather than hardcoding "$"
 * everywhere. A simple symbol map covers the currencies actually offered on
 * the profile form (see `ProfileUpdateRequest`); an unmapped code falls back
 * to showing the code itself rather than guessing a symbol.
 */
class Money
{
    private const SYMBOLS = [
        'USD' => '$',
        'PHP' => '₱',
    ];

    public static function format(string|int|float $amount, string $currency): string
    {
        return self::symbol($currency).number_format((float) $amount, 2);
    }

    public static function symbol(string $currency): string
    {
        return self::SYMBOLS[$currency] ?? $currency.' ';
    }
}
