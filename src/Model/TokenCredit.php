<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Model;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Sylius\Component\Core\Model\OrderInterface;

final readonly class TokenCredit
{
    public function __construct(
        public int $amount,
        public string $idempotencyKey,
        public TokenBatchOrigin $origin = TokenBatchOrigin::Purchase,
        public ?\DateTimeImmutable $expiresAt = null,
        public ?OrderInterface $order = null,
        public ?string $reason = null,
        public ?PurchasePrice $price = null,
    ) {
    }
}
