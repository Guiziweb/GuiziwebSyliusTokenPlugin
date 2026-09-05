<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class TokenBatchRepository extends EntityRepository implements TokenBatchRepositoryInterface
{
    public function findAvailable(TokenWalletInterface $wallet, \DateTimeInterface $at): array
    {
        /** @var array<int, TokenBatchInterface> $batches */
        $batches = $this->createAvailableQueryBuilder($wallet, $at)
            ->addSelect('CASE WHEN o.expiresAt IS NULL THEN 1 ELSE 0 END AS HIDDEN neverExpires')
            ->orderBy('neverExpires', 'ASC')
            ->addOrderBy('o.expiresAt', 'ASC')
            ->addOrderBy('o.acquiredAt', 'ASC')
            ->addOrderBy('o.id', 'ASC')
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult()
        ;

        return $batches;
    }

    public function findNextExpiring(TokenWalletInterface $wallet, \DateTimeInterface $at): ?TokenBatchInterface
    {
        /** @var ?TokenBatchInterface $batch */
        $batch = $this->createAvailableQueryBuilder($wallet, $at)
            ->andWhere('o.expiresAt IS NOT NULL')
            ->orderBy('o.expiresAt', 'ASC')
            ->addOrderBy('o.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return $batch;
    }

    public function getBalance(TokenWalletInterface $wallet, \DateTimeInterface $at): int
    {
        $balance = $this->createAvailableQueryBuilder($wallet, $at)
            ->select('SUM(o.remainingAmount)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) $balance;
    }

    private function createAvailableQueryBuilder(
        TokenWalletInterface $wallet,
        \DateTimeInterface $at,
    ): QueryBuilder {
        return $this->createQueryBuilder('o')
            ->andWhere('o.wallet = :wallet')
            ->andWhere('o.remainingAmount > 0')
            ->andWhere('o.expiresAt IS NULL OR o.expiresAt > :at')
            ->setParameter('wallet', $wallet)
            ->setParameter('at', $at)
        ;
    }

    public function findExpiredForWallet(TokenWalletInterface $wallet, \DateTimeInterface $at): array
    {
        /** @var array<int, TokenBatchInterface> $batches */
        $batches = $this->createQueryBuilder('o')
            ->andWhere('o.wallet = :wallet')
            ->andWhere('o.remainingAmount > 0')
            ->andWhere('o.expiresAt IS NOT NULL')
            ->andWhere('o.expiresAt <= :at')
            ->setParameter('wallet', $wallet)
            ->setParameter('at', $at)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->setHint(Query::HINT_REFRESH, true)
            ->getResult()
        ;

        return $batches;
    }
}
