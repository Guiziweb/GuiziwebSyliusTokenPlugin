<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction;

use Doctrine\ORM\Mapping as ORM;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Component\Core\Model\OrderInterface;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_token_transaction')]
#[ORM\UniqueConstraint(name: 'guiziweb_token_transaction_replay_idx', columns: ['idempotency_key', 'type', 'batch_id'])]
class TokenTransaction implements TokenTransactionInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TokenWalletInterface::class)]
    #[ORM\JoinColumn(name: 'wallet_id', nullable: false, onDelete: 'CASCADE')]
    protected TokenWalletInterface $wallet;

    #[ORM\ManyToOne(targetEntity: TokenBatchInterface::class)]
    #[ORM\JoinColumn(name: 'batch_id', nullable: false, onDelete: 'CASCADE')]
    protected TokenBatchInterface $batch;

    #[ORM\Column(type: 'integer')]
    protected int $amount;

    #[ORM\Column(type: 'string', length: 32, enumType: TokenTransactionType::class)]
    protected TokenTransactionType $type;

    #[ORM\Column(name: 'idempotency_key', type: 'string', length: 255)]
    protected string $idempotencyKey;

    #[ORM\ManyToOne(targetEntity: OrderInterface::class)]
    #[ORM\JoinColumn(name: 'order_id', nullable: true, onDelete: 'SET NULL')]
    protected ?OrderInterface $order = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $reason = null;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    protected \DateTimeImmutable $createdAt;

    public function __construct(
        TokenBatchInterface $batch,
        int $amount,
        TokenTransactionType $type,
        string $idempotencyKey,
        \DateTimeImmutable $createdAt,
        ?OrderInterface $order = null,
        ?string $reason = null,
    ) {
        if (0 === $amount) {
            throw new \InvalidArgumentException('A ledger entry cannot have a zero amount.');
        }

        $this->wallet = $batch->getWallet();
        $this->batch = $batch;
        $this->amount = $amount;
        $this->type = $type;
        $this->idempotencyKey = $idempotencyKey;
        $this->createdAt = $createdAt;
        $this->order = $order;
        $this->reason = $reason;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWallet(): TokenWalletInterface
    {
        return $this->wallet;
    }

    public function getBatch(): TokenBatchInterface
    {
        return $this->batch;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getType(): TokenTransactionType
    {
        return $this->type;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }

    public function getOrder(): ?OrderInterface
    {
        return $this->order;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
