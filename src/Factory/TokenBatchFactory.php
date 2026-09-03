<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;

final readonly class TokenBatchFactory implements TokenBatchFactoryInterface
{
    /** @param class-string<TokenBatchInterface> $className */
    public function __construct(
        private string $className,
    ) {
    }

    public function createNew(
        TokenWalletInterface $wallet,
        int $amount,
        TokenBatchOrigin $origin,
        \DateTimeImmutable $acquiredAt,
        ?\DateTimeImmutable $expiresAt = null,
        ?PurchasePrice $price = null,
    ): TokenBatchInterface {
        return new $this->className($wallet, $amount, $origin, $acquiredAt, $expiresAt, $price);
    }
}
