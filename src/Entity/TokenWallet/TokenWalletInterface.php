<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenWallet;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Model\ResourceInterface;

interface TokenWalletInterface extends ResourceInterface
{
    public function getId(): ?int;

    public function getCustomer(): ?CustomerInterface;

    public function setCustomer(?CustomerInterface $customer): void;

    public function getCreatedAt(): \DateTimeImmutable;
}
