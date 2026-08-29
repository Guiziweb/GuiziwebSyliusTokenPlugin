<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Order;

use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class OrderKindResolver implements OrderKindResolverInterface
{
    public function resolve(OrderInterface $order): OrderKind
    {
        $consumables = 0;
        $others = 0;

        foreach ($order->getItems() as $item) {
            $variant = $item->getVariant();

            if ($variant instanceof TokenPackInterface && $variant->isConsumable()) {
                ++$consumables;
            } else {
                ++$others;
            }
        }

        if (0 === $consumables) {
            return OrderKind::Regular;
        }

        return 0 === $others ? OrderKind::Consumables : OrderKind::Mixed;
    }
}
