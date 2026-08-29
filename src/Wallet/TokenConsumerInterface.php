<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Exception\NotConsumableException;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Sylius\Component\Core\Model\CustomerInterface;

interface TokenConsumerInterface
{
    /**
     * @throws NotConsumableException
     * @throws InsufficientTokenBalanceException
     */
    public function consume(CustomerInterface $customer, TokenPackInterface $consumable, string $reference, int $quantity = 1): void;

    public function canConsume(CustomerInterface $customer, TokenPackInterface $consumable, int $quantity = 1): bool;

    public function getBalance(CustomerInterface $customer): int;
}
