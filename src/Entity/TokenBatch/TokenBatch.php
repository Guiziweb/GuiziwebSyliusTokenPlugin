<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenBatch;

use Doctrine\ORM\Mapping as ORM;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;

#[ORM\Entity]
#[ORM\Table(name: 'guiziweb_sylius_token_batch')]
#[ORM\Index(columns: ['wallet_id', 'expires_at'], name: 'guiziweb_token_batch_availability_idx')]
class TokenBatch implements TokenBatchInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected ?int $id = null;

    #[ORM\ManyToOne(targetEntity: TokenWalletInterface::class)]
    #[ORM\JoinColumn(name: 'wallet_id', nullable: false, onDelete: 'CASCADE')]
    protected TokenWalletInterface $wallet;

    #[ORM\Column(type: 'integer')]
    protected int $amount;

    #[ORM\Column(name: 'remaining_amount', type: 'integer')]
    protected int $remainingAmount;

    #[ORM\Column(name: 'purchase_amount', type: 'integer', nullable: true)]
    protected ?int $purchaseAmount = null;

    #[ORM\Column(name: 'currency_code', type: 'string', length: 3, nullable: true)]
    protected ?string $currencyCode = null;

    #[ORM\Column(type: 'string', length: 32, enumType: TokenBatchOrigin::class)]
    protected TokenBatchOrigin $origin;

    #[ORM\Column(name: 'acquired_at', type: 'datetime_immutable')]
    protected \DateTimeImmutable $acquiredAt;

    #[ORM\Column(name: 'expires_at', type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $expiresAt = null;

    public function __construct(
        TokenWalletInterface $wallet,
        int $amount,
        TokenBatchOrigin $origin,
        \DateTimeImmutable $acquiredAt,
        ?\DateTimeImmutable $expiresAt = null,
        ?PurchasePrice $price = null,
    ) {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('A token batch must hold a positive amount of tokens.');
        }

        $this->wallet = $wallet;
        $this->amount = $amount;
        $this->remainingAmount = $amount;
        $this->origin = $origin;
        $this->acquiredAt = $acquiredAt;
        $this->expiresAt = $expiresAt;
        $this->purchaseAmount = $price?->amount;
        $this->currencyCode = $price?->currencyCode;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWallet(): TokenWalletInterface
    {
        return $this->wallet;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getRemainingAmount(): int
    {
        return $this->remainingAmount;
    }

    public function getPurchasePrice(): ?PurchasePrice
    {
        if (null === $this->purchaseAmount || null === $this->currencyCode) {
            return null;
        }

        return new PurchasePrice($this->purchaseAmount, $this->currencyCode);
    }

    public function getOrigin(): TokenBatchOrigin
    {
        return $this->origin;
    }

    public function getAcquiredAt(): \DateTimeImmutable
    {
        return $this->acquiredAt;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isExpiredAt(\DateTimeInterface $date): bool
    {
        return null !== $this->expiresAt && $this->expiresAt <= $date;
    }

    public function deduct(int $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('The deducted amount must be positive.');
        }

        if ($amount > $this->remainingAmount) {
            throw new \InvalidArgumentException(sprintf(
                'Cannot deduct %d tokens from a batch holding %d.',
                $amount,
                $this->remainingAmount,
            ));
        }

        $this->remainingAmount -= $amount;
    }
}
