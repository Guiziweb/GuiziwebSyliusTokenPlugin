<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;

interface TokenWalletFactoryInterface
{
    public function createNew(\DateTimeImmutable $createdAt): TokenWalletInterface;
}
