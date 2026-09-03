<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Model;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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

    #[Assert\Positive(groups: ['sylius'])]
    #[ORM\Column(name: 'token_validity_months', type: 'integer', nullable: true)]
    protected ?int $tokenValidityMonths = null;

    public function getTokenValidityMonths(): ?int
    {
        return $this->tokenValidityMonths;
    }

    public function setTokenValidityMonths(?int $tokenValidityMonths): void
    {
        $this->tokenValidityMonths = $tokenValidityMonths;
    }

    public function isTokenPack(): bool
    {
        return null !== $this->tokenAmount && $this->tokenAmount > 0;
    }

    public function resolveExpirationDate(\DateTimeImmutable $acquiredAt): ?\DateTimeImmutable
    {
        if (null === $this->tokenValidityMonths || $this->tokenValidityMonths <= 0) {
            return null;
        }

        $expiresAt = $acquiredAt->add(new \DateInterval(sprintf('P%dM', $this->tokenValidityMonths)));

        if ($expiresAt->format('j') !== $acquiredAt->format('j')) {
            $expiresAt = $expiresAt->modify('last day of previous month');
        }

        return $expiresAt;
    }

    #[Assert\Callback(groups: ['sylius'])]
    public function validateTokenPackIsNotShippable(ExecutionContextInterface $context): void
    {
        if (!$this->isTokenPack()) {
            return;
        }

        if (!$this instanceof ProductVariantInterface || !$this->isShippingRequired()) {
            return;
        }

        $context
            ->buildViolation('guiziweb_sylius_token.product_variant.token_pack_is_not_shippable')
            ->atPath('shippingRequired')
            ->addViolation()
        ;
    }
}
