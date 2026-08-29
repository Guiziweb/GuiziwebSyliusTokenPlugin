<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenTariff;

use Sylius\Resource\Model\ResourceInterface;

interface TokenTariffInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getCode(): ?string;

    public function setCode(?string $code): void;

    public function getName(): ?string;

    public function setName(?string $name): void;

    public function getCost(): ?int;

    public function setCost(?int $cost): void;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): void;
}
