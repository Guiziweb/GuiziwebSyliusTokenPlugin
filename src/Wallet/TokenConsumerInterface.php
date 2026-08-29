<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenTariff\TokenTariffInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Exception\TariffNotAvailableException;
use Sylius\Component\Core\Model\CustomerInterface;

interface TokenConsumerInterface
{
    /**
     * @throws TariffNotAvailableException
     * @throws InsufficientTokenBalanceException
     */
    public function consume(CustomerInterface $customer, TokenTariffInterface $tariff, string $reference, int $quantity = 1): void;

    public function canConsume(CustomerInterface $customer, TokenTariffInterface $tariff, int $quantity = 1): bool;

    public function getBalance(CustomerInterface $customer): int;
}
