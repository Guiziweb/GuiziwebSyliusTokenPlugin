<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenWallet;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\CustomerInterface;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_token_wallet')]
class TokenWallet implements TokenWalletInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\OneToOne(targetEntity: CustomerInterface::class)]
    #[ORM\JoinColumn(name: 'customer_id', nullable: true, unique: true, onDelete: 'SET NULL')]
    protected ?CustomerInterface $customer = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;

    public function __construct(\DateTimeImmutable $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?CustomerInterface
    {
        return $this->customer;
    }

    public function setCustomer(?CustomerInterface $customer): void
    {
        $this->customer = $customer;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
