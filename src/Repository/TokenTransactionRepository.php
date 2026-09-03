<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Doctrine\ORM\QueryBuilder;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\CustomerInterface;

class TokenTransactionRepository extends EntityRepository implements TokenTransactionRepositoryInterface
{
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
