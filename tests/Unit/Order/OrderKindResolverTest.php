<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Order;

use Doctrine\Common\Collections\ArrayCollection;
use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolver;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class OrderKindResolverTest extends TestCase
{
    public function testAnOrderWithoutConsumablesIsRegular(): void
    {
        $order = $this->createOrder([$this->createItem(consumable: false)]);

        self::assertSame(OrderKind::Regular, (new OrderKindResolver())->resolve($order));
    }

    public function testAnEmptyOrderIsRegular(): void
    {
        self::assertSame(OrderKind::Regular, (new OrderKindResolver())->resolve($this->createOrder([])));
    }

    public function testAnOrderOfConsumablesOnlyIsConsumables(): void
    {
        $order = $this->createOrder([$this->createItem(consumable: true), $this->createItem(consumable: true)]);

        self::assertSame(OrderKind::Consumables, (new OrderKindResolver())->resolve($order));
    }

    public function testAnOrderMixingBothIsMixed(): void
    {
        $order = $this->createOrder([$this->createItem(consumable: true), $this->createItem(consumable: false)]);

        self::assertSame(OrderKind::Mixed, (new OrderKindResolver())->resolve($order));
    }

    /** @param array<int, OrderItemInterface> $items */
    private function createOrder(array $items): OrderInterface
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getItems')->willReturn(new ArrayCollection($items));

        return $order;
    }

    private function createItem(bool $consumable): OrderItemInterface
    {
        $variant = $this->createMockForIntersectionOfInterfaces([ProductVariantInterface::class, TokenPackInterface::class]);
        $variant->method('isConsumable')->willReturn($consumable);

        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getVariant')->willReturn($variant);

        return $item;
    }
}
