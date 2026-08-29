<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Product;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

trait TokenPackTrait
{
    #[Assert\Positive(groups: ['sylius'])]
    #[ORM\Column(name: 'token_amount', type: 'integer', nullable: true)]
    protected ?int $tokenAmount = null;

    #[Assert\Positive(groups: ['sylius'])]
    #[ORM\Column(name: 'token_price', type: 'integer', nullable: true)]
    protected ?int $tokenPrice = null;

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

    public function getTokenPrice(): ?int
    {
        return $this->tokenPrice;
    }

    public function setTokenPrice(?int $tokenPrice): void
    {
        $this->tokenPrice = $tokenPrice;
    }

    public function isConsumable(): bool
    {
        return null !== $this->tokenPrice && $this->tokenPrice > 0;
    }

    #[Assert\Callback(groups: ['sylius'])]
    public function validateTokenFieldsAreExclusive(ExecutionContextInterface $context): void
    {
        if ($this->isTokenPack() && $this->isConsumable()) {
            $context->buildViolation('guiziweb_sylius_token.product_variant.pack_and_consumable')
                ->atPath('tokenPrice')
                ->addViolation()
            ;
        }
    }
}
