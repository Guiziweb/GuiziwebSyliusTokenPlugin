<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Repository;

use Doctrine\DBAL\LockMode;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class TokenOperationRepository extends EntityRepository implements TokenOperationRepositoryInterface
{
    public function isRecorded(TokenWalletInterface $wallet, string $idempotencyKey, TokenTransactionType $type): bool
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
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult()
        ;

        return null !== $existing;
    }
}
