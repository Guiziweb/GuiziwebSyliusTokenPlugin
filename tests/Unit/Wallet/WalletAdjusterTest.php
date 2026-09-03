<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Model\WalletAdjustment;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletAdjuster;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class WalletAdjusterTest extends TestCase
{
    private WalletOperatorInterface&MockObject $walletOperator;

    private TokenWalletInterface&MockObject $wallet;

    protected function setUp(): void
    {
        $this->walletOperator = $this->createMock(WalletOperatorInterface::class);
        $this->wallet = $this->createMock(TokenWalletInterface::class);
        $this->wallet->method('getId')->willReturn(7);
    }

    public function testItCreditsTheWalletAsAnAdjustment(): void
    {
        $this->walletOperator->expects(self::never())->method('debit');
        $this->walletOperator
            ->expects(self::once())
            ->method('credit')
            ->with($this->wallet, self::callback(static function (TokenCredit $credit): bool {
                self::assertSame(300, $credit->amount);
                self::assertSame('Geste commercial', $credit->reason);
                self::assertSame(TokenBatchOrigin::Adjustment, $credit->origin);

                return true;
            }))
        ;

        $this->adjust(WalletAdjustment::DIRECTION_CREDIT, 300, 'Geste commercial');
    }

    public function testItDebitsTheWalletAsAnAdjustment(): void
    {
        $this->walletOperator->expects(self::never())->method('credit');
        $this->walletOperator
            ->expects(self::once())
            ->method('debit')
            ->with($this->wallet, self::callback(static function (TokenDebit $debit): bool {
                self::assertSame(120, $debit->amount);
                self::assertSame('Correction', $debit->reason);

                return true;
            }))
        ;

        $this->adjust(WalletAdjustment::DIRECTION_DEBIT, 120, 'Correction');
    }

    public function testItDerivesTheIdempotencyKeyFromTheWalletAndTheOperation(): void
    {
        $adjustment = $this->createAdjustment(WalletAdjustment::DIRECTION_CREDIT, 10, 'Motif');

        $this->walletOperator
            ->expects(self::once())
            ->method('credit')
            ->with($this->wallet, self::callback(static function (TokenCredit $credit) use ($adjustment): bool {
                self::assertSame('admin-7-' . $adjustment->operationId, $credit->idempotencyKey);

                return true;
            }))
        ;

        (new WalletAdjuster($this->walletOperator))->adjust($this->wallet, $adjustment);
    }

    public function testReplayingTheSameOperationKeepsTheSameKey(): void
    {
        $adjustment = $this->createAdjustment(WalletAdjustment::DIRECTION_CREDIT, 10, 'Motif');

        $keys = [];
        $this->walletOperator
            ->expects(self::exactly(2))
            ->method('credit')
            ->with($this->wallet, self::callback(static function (TokenCredit $credit) use (&$keys): bool {
                $keys[] = $credit->idempotencyKey;

                return true;
            }))
        ;

        $adjuster = new WalletAdjuster($this->walletOperator);
        $adjuster->adjust($this->wallet, $adjustment);
        $adjuster->adjust($this->wallet, $adjustment);

        self::assertSame($keys[0], $keys[1]);
    }

    public function testTwoOperationsOnTheSameWalletGetDistinctKeys(): void
    {
        $keys = [];
        $this->walletOperator
            ->method('credit')
            ->with($this->wallet, self::callback(static function (TokenCredit $credit) use (&$keys): bool {
                $keys[] = $credit->idempotencyKey;

                return true;
            }))
        ;

        $adjuster = new WalletAdjuster($this->walletOperator);
        $adjuster->adjust($this->wallet, $this->createAdjustment(WalletAdjustment::DIRECTION_CREDIT, 10, 'Motif'));
        $adjuster->adjust($this->wallet, $this->createAdjustment(WalletAdjustment::DIRECTION_CREDIT, 10, 'Motif'));

        self::assertNotSame($keys[0], $keys[1]);
    }

    private function adjust(string $direction, int $amount, string $reason): void
    {
        (new WalletAdjuster($this->walletOperator))
            ->adjust($this->wallet, $this->createAdjustment($direction, $amount, $reason))
        ;
    }

    private function createAdjustment(string $direction, int $amount, string $reason): WalletAdjustment
    {
        $adjustment = new WalletAdjustment();
        $adjustment->direction = $direction;
        $adjustment->amount = $amount;
        $adjustment->reason = $reason;

        return $adjustment;
    }
}
