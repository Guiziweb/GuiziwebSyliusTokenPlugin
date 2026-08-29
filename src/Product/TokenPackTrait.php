<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait TokenPackTrait
{
    #[Assert\Positive(groups: ['sylius'])]
    #[ORM\Column(name: 'token_amount', type: 'integer', nullable: true)]
    protected ?int $tokenAmount = null;

    public function getTokenAmount(): ?int
    {
        return $this->tokenAmount;
    }

    public function setTokenAmount(?int $tokenAmount): void
    {
        $this->tokenAmount = $tokenAmount;
    }

    public function isTokenPack(): bool
    {
        return null !== $this->tokenAmount && $this->tokenAmount > 0;
    }
}
