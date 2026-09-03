<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Model\BatchAllocation;

final class BatchAllocator implements BatchAllocatorInterface
{
    public function allocate(array $batches, int $amount): array
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('The allocated amount must be positive.');
        }

        $allocations = [];
        $remaining = $amount;
        $available = 0;

        foreach ($batches as $batch) {
            $available += $batch->getRemainingAmount();

            $taken = min($remaining, $batch->getRemainingAmount());

            if ($taken > 0) {
                $allocations[] = new BatchAllocation($batch, $taken);
                $remaining -= $taken;
            }

            if (0 === $remaining) {
                return $allocations;
            }
        }

        throw new InsufficientTokenBalanceException($amount, $available);
    }
}
