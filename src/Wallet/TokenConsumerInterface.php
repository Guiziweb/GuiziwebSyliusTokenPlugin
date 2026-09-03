<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Exception\TokenPriceNotAvailableException;
use Sylius\Component\Core\Model\CustomerInterface;

interface TokenConsumerInterface
{
    /**
     * @throws TokenPriceNotAvailableException
     * @throws InsufficientTokenBalanceException
     */
    public function consume(CustomerInterface $customer, TokenPriceInterface $price, string $reference, int $quantity = 1): void;

    public function canConsume(CustomerInterface $customer, TokenPriceInterface $price, int $quantity = 1): bool;

    public function getBalance(CustomerInterface $customer): int;
}
