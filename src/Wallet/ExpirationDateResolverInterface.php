<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

interface ExpirationDateResolverInterface
{
    public function resolve(\DateTimeImmutable $acquiredAt): ?\DateTimeImmutable;
}
