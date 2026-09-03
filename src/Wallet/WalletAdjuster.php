<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Model\WalletAdjustment;
use Webmozart\Assert\Assert;

final readonly class WalletAdjuster implements WalletAdjusterInterface
{
    public function __construct(
        private WalletOperatorInterface $walletOperator,
    ) {
    }

    public function adjust(TokenWalletInterface $wallet, WalletAdjustment $adjustment): void
    {
        Assert::notNull($adjustment->amount);
        Assert::notNull($adjustment->reason);

        $key = sprintf('admin-%s-%s', $wallet->getId(), $adjustment->operationId);

        if ($adjustment->isCredit()) {
            $this->walletOperator->credit($wallet, new TokenCredit(
                amount: $adjustment->amount,
                idempotencyKey: $key,
                origin: TokenBatchOrigin::Adjustment,
                reason: $adjustment->reason,
            ));

            return;
        }

        $this->walletOperator->debit($wallet, new TokenDebit(
            amount: $adjustment->amount,
            idempotencyKey: $key,
            reason: $adjustment->reason,
        ));
    }
}
