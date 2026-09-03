<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Factory;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatch;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransaction;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWallet;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Factory\TokenBatchFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenTransactionFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenWalletFactory;
use PHPUnit\Framework\TestCase;

final class TokenEntityFactoriesTest extends TestCase
{
    private \DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->now = new \DateTimeImmutable('2026-01-15 10:00:00');
    }

    public function testTheWalletFactoryBuildsTheConfiguredClass(): void
    {
        $wallet = (new TokenWalletFactory(SubstitutedWallet::class))->createNew($this->now);

        self::assertInstanceOf(SubstitutedWallet::class, $wallet, 'An application overriding the model must get its own class.');
        self::assertSame($this->now, $wallet->getCreatedAt());
    }

    public function testTheBatchFactoryBuildsTheConfiguredClassAndKeepsItsInvariants(): void
    {
        $factory = new TokenBatchFactory(TokenBatch::class);

        $batch = $factory->createNew($this->wallet(), 500, TokenBatchOrigin::Purchase, $this->now);

        self::assertInstanceOf(TokenBatch::class, $batch);
        self::assertSame(500, $batch->getAmount());

        $this->expectException(\InvalidArgumentException::class);
        $factory->createNew($this->wallet(), 0, TokenBatchOrigin::Purchase, $this->now);
    }

    public function testTheTransactionFactoryBuildsTheConfiguredClassAndKeepsItsInvariants(): void
    {
        $batch = (new TokenBatchFactory(TokenBatch::class))
            ->createNew($this->wallet(), 500, TokenBatchOrigin::Purchase, $this->now)
        ;
        $factory = new TokenTransactionFactory(TokenTransaction::class);

        $transaction = $factory->createNew($batch, -20, TokenTransactionType::Debit, 'key-1', $this->now);

        self::assertInstanceOf(TokenTransaction::class, $transaction);
        self::assertSame(-20, $transaction->getAmount());

        $this->expectException(\InvalidArgumentException::class);
        $factory->createNew($batch, 0, TokenTransactionType::Debit, 'key-2', $this->now);
    }

    private function wallet(): TokenWalletInterface
    {
        return new TokenWallet($this->now);
    }
}

final class SubstitutedWallet extends TokenWallet
{
}
