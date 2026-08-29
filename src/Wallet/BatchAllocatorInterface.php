<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;

interface BatchAllocatorInterface
{
    /**
     * @param array<int, TokenBatchInterface> $batches batches in burn order
     *
     * @return array<int, BatchAllocation>
     *
     * @throws InsufficientTokenBalanceException when the batches do not hold enough tokens
     */
    public function allocate(array $batches, int $amount): array;
}
