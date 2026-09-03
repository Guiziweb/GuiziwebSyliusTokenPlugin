<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;

final readonly class TokenWalletFactory implements TokenWalletFactoryInterface
{
    /** @param class-string<TokenWalletInterface> $className */
    public function __construct(
        private string $className,
    ) {
    }

    public function createNew(\DateTimeImmutable $createdAt): TokenWalletInterface
    {
        return new $this->className($createdAt);
    }
}
