<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Entity;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatch;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use PHPUnit\Framework\TestCase;

final class TokenBatchTest extends TestCase
{
    public function testItStartsFullyAvailable(): void
    {
        $batch = $this->createBatch(100);

        self::assertSame(100, $batch->getAmount());
        self::assertSame(100, $batch->getRemainingAmount());
    }

    public function testItCannotBeCreatedEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createBatch(0);
    }

    public function testItDeductsTokens(): void
    {
        $batch = $this->createBatch(100);

        $batch->deduct(30);

        self::assertSame(30, $batch->getAmount() - $batch->getRemainingAmount());
        self::assertSame(70, $batch->getRemainingAmount());
    }

    public function testItRefusesToDeductMoreThanItHolds(): void
    {
        $batch = $this->createBatch(100);

        $this->expectException(\InvalidArgumentException::class);

        $batch->deduct(101);
    }

    public function testItIsExpiredOnceItsExpirationDateIsReached(): void
    {
        $batch = $this->createBatch(100, new \DateTimeImmutable('2026-01-01 00:00:00'));

        self::assertFalse($batch->isExpiredAt(new \DateTimeImmutable('2025-12-31 23:59:59')));
        self::assertTrue($batch->isExpiredAt(new \DateTimeImmutable('2026-01-01 00:00:00')));
    }

    public function testItNeverExpiresWithoutAnExpirationDate(): void
    {
        $batch = $this->createBatch(100);

        self::assertFalse($batch->isExpiredAt(new \DateTimeImmutable('2999-01-01')));
    }

    private function createBatch(int $amount, ?\DateTimeImmutable $expiresAt = null): TokenBatch
    {
        return new TokenBatch(
            $this->createMock(TokenWalletInterface::class),
            $amount,
            TokenBatchOrigin::Purchase,
            new \DateTimeImmutable('2025-01-01'),
            $expiresAt,
        );
    }
}
