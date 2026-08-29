<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment;

use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Sylius\Component\Core\Model\OrderInterface;

final class OrderTokenPriceCalculator implements OrderTokenPriceCalculatorInterface
{
    public function calculate(OrderInterface $order): int
    {
        $total = 0;

        foreach ($order->getItems() as $item) {
            $variant = $item->getVariant();

            if (!$variant instanceof TokenPackInterface || !$variant->isConsumable()) {
                continue;
            }

            /** @var int $tokenPrice */
            $tokenPrice = $variant->getTokenPrice();

            $total += $tokenPrice * $item->getQuantity();
        }

        return $total;
    }
}
