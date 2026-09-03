<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenOperation;

use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Resource\Model\ResourceInterface;

interface TokenOperationInterface extends ResourceInterface
{
    public function getWallet(): TokenWalletInterface;

    public function getIdempotencyKey(): string;

    public function getType(): TokenTransactionType;

    public function getCreatedAt(): \DateTimeImmutable;
}
