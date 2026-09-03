<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenPrice;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_token_price')]
#[UniqueEntity(fields: ['code'], message: 'guiziweb_sylius_token.price.code.unique', groups: ['sylius'])]
class TokenPrice implements TokenPriceInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[Assert\NotBlank(message: 'guiziweb_sylius_token.price.code.not_blank', groups: ['sylius'])]
    #[Assert\Regex(pattern: '/^[\\w-]*$/', message: 'guiziweb_sylius_token.price.code.regex', groups: ['sylius'])]
    #[ORM\Column(type: 'string', length: 64, unique: true)]
    protected ?string $code = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[ORM\Column(type: 'string', length: 255)]
    protected ?string $name = null;

    #[Assert\NotBlank(groups: ['sylius'])]
    #[Assert\Positive(groups: ['sylius'])]
    #[ORM\Column(type: 'integer')]
    protected ?int $cost = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    protected bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCost(): ?int
    {
        return $this->cost;
    }

    public function setCost(?int $cost): void
    {
        $this->cost = $cost;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }
}
