<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\CustomerInterface;

class TokenTransactionRepository extends EntityRepository implements TokenTransactionRepositoryInterface
{
    public function hasIdempotencyKey(TokenWalletInterface $wallet, string $idempotencyKey, TokenTransactionType $type): bool
    {
        $existing = $this->createQueryBuilder('o')
            ->select('o.id')
            ->andWhere('o.wallet = :wallet')
            ->andWhere('o.idempotencyKey = :idempotencyKey')
            ->andWhere('o.type = :type')
            ->setParameter('wallet', $wallet)
            ->setParameter('idempotencyKey', $idempotencyKey)
            ->setParameter('type', $type->value)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        return null !== $existing;
    }

    public function createByCustomerQueryBuilder(CustomerInterface $customer): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.wallet', 'wallet')
            ->andWhere('wallet.customer = :customer')
            ->setParameter('customer', $customer)
        ;
    }

    public function createByWalletQueryBuilder(TokenWalletInterface $wallet): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.wallet = :wallet')
            ->setParameter('wallet', $wallet)
        ;
    }
}
