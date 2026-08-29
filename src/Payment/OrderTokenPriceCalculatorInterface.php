<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderTokenPriceCalculatorInterface
{
    public function calculate(OrderInterface $order): int;
}
