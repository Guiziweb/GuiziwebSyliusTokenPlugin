<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Wallet;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatch;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenOperation\TokenOperation;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransaction;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Factory\TokenBatchFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenOperationFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenTransactionFactory;
use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenOperationRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\BatchAllocator;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Clock\MockClock;

final class WalletOperatorTest extends TestCase
{
    private const NOW = '2026-03-01 12:00:00';

    /** @var array<int, object> */
    private array $persisted = [];

    private TokenWalletInterface&MockObject $wallet;

    private OrderInterface&MockObject $order;

    private TokenBatchRepositoryInterface&MockObject $batchRepository;

    private TokenOperationRepositoryInterface&MockObject $operationRepository;

    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->persisted = [];
        $this->wallet = $this->createMock(TokenWalletInterface::class);
        $this->order = $this->createMock(OrderInterface::class);
        $this->batchRepository = $this->createMock(TokenBatchRepositoryInterface::class);
        $this->operationRepository = $this->createMock(TokenOperationRepositoryInterface::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager->method('wrapInTransaction')->willReturnCallback(
            fn (callable $operation): mixed => $operation($this->entityManager),
        );
        $this->entityManager->method('persist')->willReturnCallback(
            function (object $entity): void {
                $this->persisted[] = $entity;
            },
        );
    }

    public function testItLocksTheWalletBeforeCrediting(): void
    {
        $this->entityManager
            ->expects(self::once())
            ->method('lock')
            ->with($this->wallet, LockMode::PESSIMISTIC_WRITE)
        ;

        $this->createOperator()->credit($this->wallet, new TokenCredit(100, 'order-1'));
    }

    public function testItCreditsABatchAndItsLedgerEntry(): void
    {
        $batch = $this->createOperator()->credit(
            $this->wallet,
            new TokenCredit(
                amount: 100,
                idempotencyKey: 'order-1',
                order: $this->order,
                price: new PurchasePrice(1000, 'EUR'),
            ),
        );

        self::assertInstanceOf(TokenBatchInterface::class, $batch);
        self::assertSame(100, $batch->getAmount());
        self::assertEquals(new PurchasePrice(1000, 'EUR'), $batch->getPurchasePrice());
        self::assertSame(self::NOW, $batch->getAcquiredAt()->format('Y-m-d H:i:s'));

        $transactions = $this->persistedTransactions();
        self::assertCount(1, $transactions);
        self::assertSame(100, $transactions[0]->getAmount());
        self::assertSame(TokenTransactionType::Credit, $transactions[0]->getType());
        self::assertSame('order-1', $transactions[0]->getIdempotencyKey());
        self::assertSame($this->order, $transactions[0]->getOrder());
    }

    public function testItDoesNotCreditTwiceUnderTheSameIdempotencyKey(): void
    {
        $this->operationRepository->method('isRecorded')->willReturn(true);

        $batch = $this->createOperator()->credit($this->wallet, new TokenCredit(100, 'order-1'));

        self::assertNull($batch);
        self::assertSame([], $this->persisted);
    }

    public function testItDebitsTheBatchesExpiringFirst(): void
    {
        $expiringSoon = $this->createBatch(60);
        $expiringLater = $this->createBatch(40);
        $this->batchRepository->method('findAvailable')->willReturn([$expiringSoon, $expiringLater]);

        $this->createOperator()->debit($this->wallet, new TokenDebit(75, 'order-2', $this->order));

        self::assertSame(0, $expiringSoon->getRemainingAmount());
        self::assertSame(25, $expiringLater->getRemainingAmount());

        $transactions = $this->persistedTransactions();
        self::assertCount(2, $transactions);
        self::assertSame(-60, $transactions[0]->getAmount());
        self::assertSame(-15, $transactions[1]->getAmount());

        foreach ($transactions as $transaction) {
            self::assertSame(TokenTransactionType::Debit, $transaction->getType());
            self::assertSame('order-2', $transaction->getIdempotencyKey());
        }
    }

    public function testItDoesNotDebitTwiceUnderTheSameIdempotencyKey(): void
    {
        $batch = $this->createBatch(60);
        $this->batchRepository->method('findAvailable')->willReturn([$batch]);
        $this->operationRepository->method('isRecorded')->willReturn(true);

        $this->createOperator()->debit($this->wallet, new TokenDebit(10, 'order-2'));

        self::assertSame(60, $batch->getRemainingAmount());
        self::assertSame([], $this->persisted);
    }

    public function testItTellsCreditsAndDebitsApartUnderTheSameKey(): void
    {
        $batch = $this->createBatch(60);
        $this->batchRepository->method('findAvailable')->willReturn([$batch]);
        $this->operationRepository->method('isRecorded')->willReturnCallback(
            static fn (TokenWalletInterface $wallet, string $key, TokenTransactionType $type): bool => TokenTransactionType::Credit === $type,
        );

        $this->createOperator()->debit($this->wallet, new TokenDebit(10, 'order-1'));

        self::assertSame(50, $batch->getRemainingAmount());
    }

    public function testItCarriesTheGivenExpirationDateOntoTheBatch(): void
    {
        $batch = $this->createOperator()->credit(
            $this->wallet,
            new TokenCredit(100, 'order-1', expiresAt: new \DateTimeImmutable('2026-06-01 12:00:00')),
        );

        self::assertNotNull($batch);
        self::assertNotNull($batch->getExpiresAt());
        self::assertSame('2026-06-01 12:00:00', $batch->getExpiresAt()->format('Y-m-d H:i:s'));
    }

    public function testABatchNeverExpiresWhenNoDateIsGiven(): void
    {
        $batch = $this->createOperator()->credit($this->wallet, new TokenCredit(100, 'order-1'));

        self::assertNotNull($batch);
        self::assertNull($batch->getExpiresAt());
    }

    public function testAnInsufficientBalanceDoesNotAbortTheTransaction(): void
    {
        $batch = $this->createBatch(30);
        $this->batchRepository->method('findAvailable')->willReturn([$batch]);

        $this->entityManager
            ->expects(self::once())
            ->method('wrapInTransaction')
            ->willReturnCallback(fn (callable $operation): mixed => $operation($this->entityManager))
        ;

        $this->expectException(InsufficientTokenBalanceException::class);

        $this->createOperator()->debit($this->wallet, new TokenDebit(31, 'order-3'));
    }

    public function testItLeavesTheBatchesUntouchedWhenTheBalanceIsTooLow(): void
    {
        $batch = $this->createBatch(30);
        $this->batchRepository->method('findAvailable')->willReturn([$batch]);

        $this->expectException(InsufficientTokenBalanceException::class);

        try {
            $this->createOperator()->debit($this->wallet, new TokenDebit(31, 'order-3'));
        } finally {
            self::assertSame(30, $batch->getRemainingAmount());
            self::assertSame([], $this->persisted);
        }
    }

    private function createOperator(): WalletOperator
    {
        return new WalletOperator(
            $this->entityManager,
            $this->batchRepository,
            $this->operationRepository,
            new BatchAllocator(),
            new MockClock(new \DateTimeImmutable(self::NOW)),
            new TokenBatchFactory(TokenBatch::class),
            new TokenTransactionFactory(TokenTransaction::class),
            new TokenOperationFactory(TokenOperation::class),
        );
    }

    private function createBatch(int $amount): TokenBatch
    {
        return new TokenBatch(
            $this->wallet,
            $amount,
            TokenBatchOrigin::Purchase,
            new \DateTimeImmutable('2026-01-01'),
        );
    }

    /** @return array<int, TokenTransaction> */
    private function persistedTransactions(): array
    {
        return array_values(array_filter(
            $this->persisted,
            static fn (object $entity): bool => $entity instanceof TokenTransaction,
        ));
    }
}
