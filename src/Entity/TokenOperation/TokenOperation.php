<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenOperation;

use Doctrine\ORM\Mapping as ORM;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_token_operation')]
#[ORM\UniqueConstraint(name: 'guiziweb_token_operation_replay_idx', columns: ['wallet_id', 'idempotency_key', 'type'])]
class TokenOperation implements TokenOperationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TokenWalletInterface::class)]
    #[ORM\JoinColumn(name: 'wallet_id', nullable: false, onDelete: 'CASCADE')]
    protected TokenWalletInterface $wallet;

    #[ORM\Column(name: 'idempotency_key', type: 'string', length: 255)]
    protected string $idempotencyKey;

    #[ORM\Column(type: 'string', length: 32, enumType: TokenTransactionType::class)]
    protected TokenTransactionType $type;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;

    public function __construct(
        TokenWalletInterface $wallet,
        string $idempotencyKey,
        TokenTransactionType $type,
        \DateTimeImmutable $createdAt,
    ) {
        $this->wallet = $wallet;
        $this->idempotencyKey = $idempotencyKey;
        $this->type = $type;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWallet(): TokenWalletInterface
    {
        return $this->wallet;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getType(): TokenTransactionType
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
