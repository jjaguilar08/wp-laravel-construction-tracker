<?php

namespace Tests\Unit\Support;

use App\Support\Money;
use Tests\TestCase;

class MoneyTest extends TestCase
{
    public function test_formats_usd_with_a_dollar_sign(): void
    {
        $this->assertSame('$1,234.50', Money::format(1234.5, 'USD'));
    }

    public function test_formats_php_with_a_peso_sign(): void
    {
        $this->assertSame('₱1,234.50', Money::format('1234.50', 'PHP'));
    }

    public function test_falls_back_to_the_currency_code_for_an_unmapped_currency(): void
    {
        $this->assertSame('EUR 10.00', Money::format(10, 'EUR'));
    }
}
