<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepositoryInterface;
use Psr\Clock\ClockInterface;

final readonly class TokenExpirer implements TokenExpirerInterface
{
    public function __construct(
        private TokenBatchRepositoryInterface $batchRepository,
        private WalletOperatorInterface $walletOperator,
        private ClockInterface $clock,
    ) {
    }

    public function expire(): int
    {
        $expired = 0;

        foreach ($this->batchRepository->findExpired($this->clock->now()) as $batch) {
            $expired += $this->walletOperator->expireBatch($batch);
        }

        return $expired;
    }
}
