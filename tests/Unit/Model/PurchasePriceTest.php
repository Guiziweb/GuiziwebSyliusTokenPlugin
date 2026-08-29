<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Model;

use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;
use PHPUnit\Framework\TestCase;

final class PurchasePriceTest extends TestCase
{
    public function testItKeepsAmountAndCurrencyTogether(): void
    {
        $price = new PurchasePrice(1000, 'EUR');

        self::assertSame(1000, $price->amount);
        self::assertSame('EUR', $price->currencyCode);
    }

    public function testItRefusesANegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PurchasePrice(-1, 'EUR');
    }

    public function testItRefusesAnInvalidCurrencyCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new PurchasePrice(1000, 'EURO');
    }
}
