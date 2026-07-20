<?php

use App\Support\Money;

if (! function_exists('money')) {
    /**
     * Formats an amount using the given currency, or the authenticated
     * user's currency preference if none is passed.
     */
    function money(string|int|float $amount, ?string $currency = null): string
    {
        return Money::format($amount, $currency ?? auth()->user()->currency);
    }
}
