<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

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
            ->getResult()
        ;

        return $batches;
    }

    public function getBalance(TokenWalletInterface $wallet, \DateTimeInterface $at): int
    {
        $balance = $this->createAvailableQueryBuilder($wallet, $at)
            ->select('COALESCE(SUM(o.remainingAmount), 0)')
            ->getQuery()
            ->getSingleScalarResult()
        ;

        return (int) $balance;
    }

    private function createAvailableQueryBuilder(
        TokenWalletInterface $wallet,
        \DateTimeInterface $at,
    ): \Doctrine\ORM\QueryBuilder {
        return $this->createQueryBuilder('o')
            ->andWhere('o.wallet = :wallet')
            ->andWhere('o.remainingAmount > 0')
            ->andWhere('o.expiresAt IS NULL OR o.expiresAt > :at')
            ->setParameter('wallet', $wallet)
            ->setParameter('at', $at)
        ;
    }

    public function findExpired(\DateTimeInterface $at): array
    {
        /** @var array<int, TokenBatchInterface> $batches */
        $batches = $this->createQueryBuilder('o')
            ->andWhere('o.remainingAmount > 0')
            ->andWhere('o.expiresAt IS NOT NULL')
            ->andWhere('o.expiresAt <= :at')
            ->setParameter('at', $at)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;

        return $batches;
    }
}
