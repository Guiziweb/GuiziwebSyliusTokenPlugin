<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Twig;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenTransactionRepositoryInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class CustomerWalletRuntime implements RuntimeExtensionInterface
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private RepositoryInterface $walletRepository,
        private TokenTransactionRepositoryInterface $transactionRepository,
    ) {
    }

    public function getWallet(CustomerInterface $customer): ?TokenWalletInterface
    {
        return $this->walletRepository->findOneBy(['customer' => $customer]);
    }

    /** @return array{credited: int, spent: int} */
    public function getStatistics(CustomerInterface $customer): array
    {
        /** @var array{credited: ?string, spent: ?string} $row */
        $row = $this->transactionRepository->createByCustomerQueryBuilder($customer)
            ->select(
                'COALESCE(SUM(CASE WHEN o.amount > 0 THEN o.amount ELSE 0 END), 0) AS credited',
                'COALESCE(SUM(CASE WHEN o.amount < 0 THEN -o.amount ELSE 0 END), 0) AS spent',
            )
            ->getQuery()
            ->getSingleResult()
        ;

        return [
            'credited' => (int) $row['credited'],
            'spent' => (int) $row['spent'],
        ];
    }
}
