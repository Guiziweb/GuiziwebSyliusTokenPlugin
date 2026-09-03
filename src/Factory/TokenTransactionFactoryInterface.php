<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Sylius\Component\Core\Model\OrderInterface;

interface TokenTransactionFactoryInterface
{
    public function createNew(
        TokenBatchInterface $batch,
        int $amount,
        TokenTransactionType $type,
        string $idempotencyKey,
        \DateTimeImmutable $createdAt,
        ?OrderInterface $order = null,
        ?string $reason = null,
    ): TokenTransactionInterface;
}
