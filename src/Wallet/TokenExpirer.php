<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransaction;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepositoryInterface;
use Psr\Clock\ClockInterface;

final readonly class TokenExpirer implements TokenExpirerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TokenBatchRepositoryInterface $batchRepository,
        private ClockInterface $clock,
    ) {
    }

    public function expire(): int
    {
        /** @var int $expired */
        $expired = 0;

        $now = $this->clock->now();

        foreach ($this->batchRepository->findExpired($now) as $batch) {
            $wallet = $batch->getWallet();

            $this->entityManager->wrapInTransaction(function () use ($batch, $wallet, $now, &$expired): void {
                $this->entityManager->lock($wallet, LockMode::PESSIMISTIC_WRITE);

                $amount = $batch->getRemainingAmount();

                if ($amount <= 0) {
                    return;
                }

                $batch->deduct($amount);

                $this->entityManager->persist(new TokenTransaction(
                    $batch,
                    -$amount,
                    TokenTransactionType::Expiration,
                    sprintf('expiration-batch-%s', (string) $batch->getId()),
                    $now,
                ));

                $this->entityManager->flush();
                $wallet->setBalance($this->batchRepository->getBalance($wallet, $now));

                $expired += $amount;
            });
        }

        return $expired;
    }
}
