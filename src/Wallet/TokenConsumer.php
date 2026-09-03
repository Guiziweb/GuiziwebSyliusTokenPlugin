<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\TokenPriceNotAvailableException;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
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

    public function consume(CustomerInterface $customer, TokenPriceInterface $price, string $reference, int $quantity = 1): void
    {
        $this->walletOperator->debit(
            $this->walletProvider->provideForCustomer($customer),
            new TokenDebit(
                amount: $this->cost($price, $quantity),
                idempotencyKey: $reference,
                reason: $price->getName() ?? $price->getCode(),
            ),
        );
    }

    public function canConsume(CustomerInterface $customer, TokenPriceInterface $price, int $quantity = 1): bool
    {
        try {
            $cost = $this->cost($price, $quantity);
        } catch (TokenPriceNotAvailableException|\InvalidArgumentException) {
            return false;
        }

        return $this->getBalance($customer) >= $cost;
    }

    public function getBalance(CustomerInterface $customer): int
    {
        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);

        return null === $wallet ? 0 : $this->walletOperator->getBalance($wallet);
    }

    private function cost(TokenPriceInterface $price, int $quantity): int
    {
        $cost = $price->getCost();

        if (!$price->isEnabled() || null === $cost || $cost <= 0) {
            throw new TokenPriceNotAvailableException($price);
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('The quantity must be positive.');
        }

        return $cost * $quantity;
    }
}
