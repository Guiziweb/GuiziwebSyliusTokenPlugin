<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Payment;

use Doctrine\Common\Collections\ArrayCollection;
use Guiziweb\SyliusTokenPlugin\Payment\OrderTokenPriceCalculator;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class OrderTokenPriceCalculatorTest extends TestCase
{
    public function testItSumsTokenPricesTimesQuantities(): void
    {
        $order = $this->createOrder([
            $this->createConsumable(tokenPrice: 5, quantity: 2),
            $this->createConsumable(tokenPrice: 20, quantity: 1),
        ]);

        self::assertSame(30, (new OrderTokenPriceCalculator())->calculate($order));
    }

    public function testItIgnoresLinesThatAreNotConsumables(): void
    {
        $order = $this->createOrder([$this->createConsumable(tokenPrice: 5, quantity: 1), $this->createPlainItem()]);

        self::assertSame(5, (new OrderTokenPriceCalculator())->calculate($order));
    }

    public function testAnOrderWithoutConsumablesCostsNothing(): void
    {
        self::assertSame(0, (new OrderTokenPriceCalculator())->calculate($this->createOrder([$this->createPlainItem()])));
    }

    /** @param array<int, OrderItemInterface> $items */
    private function createOrder(array $items): OrderInterface
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getItems')->willReturn(new ArrayCollection($items));

        return $order;
    }

    private function createConsumable(int $tokenPrice, int $quantity): OrderItemInterface
    {
        $variant = $this->createMockForIntersectionOfInterfaces([ProductVariantInterface::class, TokenPackInterface::class]);
        $variant->method('isConsumable')->willReturn(true);
        $variant->method('getTokenPrice')->willReturn($tokenPrice);

        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getVariant')->willReturn($variant);
        $item->method('getQuantity')->willReturn($quantity);

        return $item;
    }

    private function createPlainItem(): OrderItemInterface
    {
        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getVariant')->willReturn($this->createMock(ProductVariantInterface::class));

        return $item;
    }
}
