<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Product;

interface TokenPackInterface
{
    public function getTokenAmount(): ?int;

    public function setTokenAmount(?int $tokenAmount): void;

    public function isTokenPack(): bool;
}
