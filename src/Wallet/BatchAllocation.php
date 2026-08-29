<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;

final readonly class BatchAllocation
{
    public function __construct(
        public TokenBatchInterface $batch,
        public int $amount,
    ) {
    }
}
