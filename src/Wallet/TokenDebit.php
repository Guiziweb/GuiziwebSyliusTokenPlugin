<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Sylius\Component\Core\Model\OrderInterface;

final readonly class TokenDebit
{
    public function __construct(
        public int $amount,
        public string $idempotencyKey,
        public ?OrderInterface $order = null,
    ) {
    }
}
