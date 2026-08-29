<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Resource\Model\ResourceInterface;

interface TokenTransactionInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getWallet(): TokenWalletInterface;

    public function getBatch(): TokenBatchInterface;

    public function getAmount(): int;

    public function getType(): TokenTransactionType;

    public function getIdempotencyKey(): string;

    public function getOrder(): ?OrderInterface;

    public function getReason(): ?string;

    public function getCreatedAt(): \DateTimeImmutable;
}
