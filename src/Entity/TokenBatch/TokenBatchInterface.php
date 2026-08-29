<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenBatch;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;
use Sylius\Resource\Model\ResourceInterface;

interface TokenBatchInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getWallet(): TokenWalletInterface;

    public function getAmount(): int;

    public function getRemainingAmount(): int;

    public function getPurchasePrice(): ?PurchasePrice;

    public function getOrigin(): TokenBatchOrigin;

    public function getAcquiredAt(): \DateTimeImmutable;

    public function getExpiresAt(): ?\DateTimeImmutable;

    public function isExpiredAt(\DateTimeInterface $date): bool;

    /**
     * @throws \InvalidArgumentException when the batch does not hold enough tokens
     */
    public function deduct(int $amount): void;
}
