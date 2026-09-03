<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Factory\TokenBatchFactoryInterface;
use Guiziweb\SyliusTokenPlugin\Factory\TokenTransactionFactoryInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenTransactionRepositoryInterface;
use Psr\Clock\ClockInterface;

final readonly class WalletOperator implements WalletOperatorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenBatchRepositoryInterface $batchRepository,
        private TokenTransactionRepositoryInterface $transactionRepository,
        private BatchAllocatorInterface $batchAllocator,
        private ClockInterface $clock,
        private TokenBatchFactoryInterface $batchFactory,
        private TokenTransactionFactoryInterface $transactionFactory,
    ) {
    }

    public function credit(TokenWalletInterface $wallet, TokenCredit $credit): ?TokenBatchInterface
    {
        $batch = null;

        $this->record(
            $wallet,
            $credit->idempotencyKey,
            TokenTransactionType::Credit,
            function (\DateTimeImmutable $now) use ($wallet, $credit, &$batch): void {
                $batch = $this->batchFactory->createNew(
                    $wallet,
                    $credit->amount,
                    $credit->origin,
                    $now,
                    $credit->expiresAt,
                    $credit->price,
                );
                $this->entityManager->persist($batch);

                $this->entityManager->persist($this->transactionFactory->createNew(
                    $batch,
                    $credit->amount,
                    TokenTransactionType::Credit,
                    $credit->idempotencyKey,
                    $now,
                    $credit->order,
                    $credit->reason,
                ));
            },
        );

        return $batch;
    }

    public function debit(TokenWalletInterface $wallet, TokenDebit $debit): void
    {
        $insufficientBalance = null;

        $this->record(
            $wallet,
            $debit->idempotencyKey,
            TokenTransactionType::Debit,
            function (\DateTimeImmutable $now) use ($wallet, $debit, &$insufficientBalance): void {
                try {
                    $allocations = $this->batchAllocator->allocate(
                        $this->batchRepository->findAvailable($wallet, $now),
                        $debit->amount,
                    );
                } catch (InsufficientTokenBalanceException $exception) {
                    $insufficientBalance = $exception;

                    return;
                }

                foreach ($allocations as $allocation) {
                    $allocation->batch->deduct($allocation->amount);

                    $this->entityManager->persist($this->transactionFactory->createNew(
                        $allocation->batch,
                        -$allocation->amount,
                        TokenTransactionType::Debit,
                        $debit->idempotencyKey,
                        $now,
                        $debit->order,
                        $debit->reason,
                    ));
                }
            },
        );

        if (null !== $insufficientBalance) {
            throw $insufficientBalance;
        }
    }

    public function getBalance(TokenWalletInterface $wallet): int
    {
        return $this->batchRepository->getBalance($wallet, $this->clock->now());
    }

    /** @param \Closure(\DateTimeImmutable): void $operation */
    private function record(
        TokenWalletInterface $wallet,
        string $idempotencyKey,
        TokenTransactionType $type,
        \Closure $operation,
    ): void {
        $this->entityManager->wrapInTransaction(
            function () use ($wallet, $idempotencyKey, $type, $operation): void {
                if (null === $wallet->getId()) {
                    $this->entityManager->flush();
                }

                $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_WRITE);

                if ($this->transactionRepository->hasIdempotencyKey($wallet, $idempotencyKey, $type)) {
                    return;
                }

                $now = $this->clock->now();

                $this->settleExpiredBatches($wallet, $now);
                $operation($now);

                $this->entityManager->flush();
            },
        );
    }

    private function settleExpiredBatches(TokenWalletInterface $wallet, \DateTimeImmutable $now): void
    {
        foreach ($this->batchRepository->findExpiredForWallet($wallet, $now) as $batch) {
            $amount = $batch->getRemainingAmount();

            if ($amount <= 0) {
                continue;
            }

            $batch->deduct($amount);

            $this->entityManager->persist($this->transactionFactory->createNew(
                $batch,
                -$amount,
                TokenTransactionType::Expiration,
                sprintf('expiration-batch-%s', (string) $batch->getId()),
                $now,
            ));
        }
    }
}
