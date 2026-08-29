<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenTariff\TokenTariffInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\TariffNotAvailableException;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final readonly class TokenConsumer implements TokenConsumerInterface
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private RepositoryInterface $walletRepository,
        private WalletProviderInterface $walletProvider,
        private WalletOperatorInterface $walletOperator,
    ) {
    }

    public function consume(CustomerInterface $customer, TokenTariffInterface $tariff, string $reference, int $quantity = 1): void
    {
        $this->walletOperator->debit(
            $this->walletProvider->provideForCustomer($customer),
            new TokenDebit(
                amount: $this->cost($tariff, $quantity),
                idempotencyKey: $reference,
            ),
        );
    }

    public function canConsume(CustomerInterface $customer, TokenTariffInterface $tariff, int $quantity = 1): bool
    {
        return $this->getBalance($customer) >= $this->cost($tariff, $quantity);
    }

    public function getBalance(CustomerInterface $customer): int
    {
        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);

        return null === $wallet ? 0 : $this->walletOperator->getBalance($wallet);
    }

    private function cost(TokenTariffInterface $tariff, int $quantity): int
    {
        $cost = $tariff->getCost();

        if (!$tariff->isEnabled() || null === $cost || $cost <= 0) {
            throw new TariffNotAvailableException($tariff);
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('The quantity must be positive.');
        }

        return $cost * $quantity;
    }
}
