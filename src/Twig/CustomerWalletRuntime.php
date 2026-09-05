<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Twig;

use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionType;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenTransactionRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final readonly class CustomerWalletRuntime implements RuntimeExtensionInterface
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private RepositoryInterface $walletRepository,
        private TokenTransactionRepositoryInterface $transactionRepository,
        private WalletOperatorInterface $walletOperator,
    ) {
    }

    public function getWallet(CustomerInterface $customer): ?TokenWalletInterface
    {
        return $this->walletRepository->findOneBy(['customer' => $customer]);
    }

    public function getSpendableBalance(TokenWalletInterface $wallet): int
    {
        return $this->walletOperator->getBalance($wallet);
    }

    /** @return array{credited: int, spent: int} */
    public function getStatistics(CustomerInterface $customer): array
    {
        /** @var array{credited: ?string, spent: ?string} $row */
        $row = $this->transactionRepository->createByCustomerQueryBuilder($customer)
            ->select(
                'SUM(CASE WHEN o.type = :credit THEN o.amount ELSE 0 END) AS credited',
                'SUM(CASE WHEN o.type = :debit THEN -o.amount ELSE 0 END) AS spent',
            )
            ->setParameter('credit', TokenTransactionType::Credit->value)
            ->setParameter('debit', TokenTransactionType::Debit->value)
            ->getQuery()
            ->getSingleResult()
        ;

        return [
            'credited' => (int) $row['credited'],
            'spent' => (int) $row['spent'],
        ];
    }
}
