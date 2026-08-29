<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Component\Core\Model\CustomerInterface;

interface TokenTransactionRepositoryInterface
{
    public function hasIdempotencyKey(TokenWalletInterface $wallet, string $idempotencyKey, TokenTransactionType $type): bool;

    public function createByCustomerQueryBuilder(CustomerInterface $customer): QueryBuilder;
}
