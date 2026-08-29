<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Sylius\Component\Core\Model\OrderInterface;

interface OrderTokenCreditorInterface
{
    public function credit(OrderInterface $order): void;
}
