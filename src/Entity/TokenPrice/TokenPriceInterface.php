<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenPrice;

use Sylius\Resource\Model\CodeAwareInterface;
use Sylius\Resource\Model\ResourceInterface;

interface TokenPriceInterface extends ResourceInterface, CodeAwareInterface
{
    public function getId(): ?int;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getCost(): ?int;

    public function setCost(?int $cost): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;
}
