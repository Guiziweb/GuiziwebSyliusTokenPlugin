<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Order;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderKindResolverInterface
{
    public function resolve(OrderInterface $order): OrderKind;
}
