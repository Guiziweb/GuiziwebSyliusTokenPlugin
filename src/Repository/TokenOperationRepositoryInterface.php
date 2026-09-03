<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Guiziweb\SyliusTokenPlugin\Entity\TokenOperation\TokenOperationInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

/**
 * @extends RepositoryInterface<TokenOperationInterface>
 */
interface TokenOperationRepositoryInterface extends RepositoryInterface
{
    public function isRecorded(TokenWalletInterface $wallet, string $idempotencyKey, TokenTransactionType $type): bool;
}
