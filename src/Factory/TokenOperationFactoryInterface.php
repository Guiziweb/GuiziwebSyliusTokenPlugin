<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenOperation\TokenOperationInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;

interface TokenOperationFactoryInterface
{
    public function createNew(
        TokenWalletInterface $wallet,
        string $idempotencyKey,
        TokenTransactionType $type,
        \DateTimeImmutable $createdAt,
    ): TokenOperationInterface;
}
