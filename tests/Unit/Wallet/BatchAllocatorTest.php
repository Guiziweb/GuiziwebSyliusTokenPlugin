<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Wallet\BatchAllocator;
use PHPUnit\Framework\TestCase;

final class BatchAllocatorTest extends TestCase
{
    public function testItTakesEverythingFromTheFirstBatchWhenItIsEnough(): void
    {
        $first = $this->createBatch(100);
        $second = $this->createBatch(50);

        $allocations = (new BatchAllocator())->allocate([$first, $second], 60);

        self::assertCount(1, $allocations);
        self::assertSame($first, $allocations[0]->batch);
        self::assertSame(60, $allocations[0]->amount);
    }

    public function testItSpillsOverToTheNextBatchesInOrder(): void
    {
        $first = $this->createBatch(100);
        $second = $this->createBatch(50);
        $third = $this->createBatch(30);

        $allocations = (new BatchAllocator())->allocate([$first, $second, $third], 160);

        self::assertCount(3, $allocations);
        self::assertSame(100, $allocations[0]->amount);
        self::assertSame(50, $allocations[1]->amount);
        self::assertSame(10, $allocations[2]->amount);
    }

    public function testItStopsOnceTheAmountIsCovered(): void
    {
        $first = $this->createBatch(100);
        $second = $this->createBatch(50);

        $allocations = (new BatchAllocator())->allocate([$first, $second], 100);

        self::assertCount(1, $allocations);
    }

    public function testItSkipsEmptiedBatches(): void
    {
        $empty = $this->createBatch(0);
        $filled = $this->createBatch(50);

        $allocations = (new BatchAllocator())->allocate([$empty, $filled], 20);

        self::assertCount(1, $allocations);
        self::assertSame($filled, $allocations[0]->batch);
    }

    public function testItFailsWhenBatchesDoNotHoldEnough(): void
    {
        $batches = [$this->createBatch(30), $this->createBatch(20)];

        try {
            (new BatchAllocator())->allocate($batches, 60);

            self::fail('Expected an InsufficientTokenBalanceException.');
        } catch (InsufficientTokenBalanceException $exception) {
            self::assertSame(60, $exception->getRequestedAmount());
            self::assertSame(50, $exception->getAvailableAmount());
        }
    }

    public function testItFailsWithoutAnyBatch(): void
    {
        $this->expectException(InsufficientTokenBalanceException::class);

        (new BatchAllocator())->allocate([], 1);
    }

    public function testItRefusesANonPositiveAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new BatchAllocator())->allocate([$this->createBatch(10)], 0);
    }

    private function createBatch(int $remainingAmount): TokenBatchInterface
    {
        $batch = $this->createMock(TokenBatchInterface::class);
        $batch->method('getRemainingAmount')->willReturn($remainingAmount);

        return $batch;
    }
}
