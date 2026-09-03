<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenOperation\TokenOperationInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;

final readonly class TokenOperationFactory implements TokenOperationFactoryInterface
{
    /** @param class-string<TokenOperationInterface> $className */
    public function __construct(
        private string $className,
    ) {
    }

    public function createNew(
        TokenWalletInterface $wallet,
        string $idempotencyKey,
        TokenTransactionType $type,
        \DateTimeImmutable $createdAt,
    ): TokenOperationInterface {
        return new $this->className($wallet, $idempotencyKey, $type, $createdAt);
    }
}
