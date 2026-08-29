<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

final readonly class ExpirationDateResolver implements ExpirationDateResolverInterface
{
    public function __construct(
        private bool $enabled,
        private string $period,
    ) {
    }

    public function resolve(\DateTimeImmutable $acquiredAt): ?\DateTimeImmutable
    {
        if (!$this->enabled) {
            return null;
        }

        return $acquiredAt->add(new \DateInterval($this->period));
    }
}
