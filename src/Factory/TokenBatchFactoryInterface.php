<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;

interface TokenBatchFactoryInterface
{
    public function createNew(
        TokenWalletInterface $wallet,
        int $amount,
        TokenBatchOrigin $origin,
        \DateTimeImmutable $acquiredAt,
        ?\DateTimeImmutable $expiresAt = null,
        ?PurchasePrice $price = null,
    ): TokenBatchInterface;
}
