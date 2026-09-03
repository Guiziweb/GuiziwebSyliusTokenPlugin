<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Model;

interface TokenPackInterface
{
    public function getTokenAmount(): ?int;

    public function setTokenAmount(?int $tokenAmount): void;

    public function getTokenValidityMonths(): ?int;

    public function setTokenValidityMonths(?int $tokenValidityMonths): void;

    public function isTokenPack(): bool;

    public function resolveExpirationDate(\DateTimeImmutable $acquiredAt): ?\DateTimeImmutable;
}
