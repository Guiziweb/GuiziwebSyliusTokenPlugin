<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;

interface TokenBatchRepositoryInterface
{
    /**
     * @return array<int, TokenBatchInterface>
     */
    public function findAvailable(TokenWalletInterface $wallet, \DateTimeInterface $at): array;

    public function getBalance(TokenWalletInterface $wallet, \DateTimeInterface $at): int;
}
