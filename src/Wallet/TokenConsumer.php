<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\NotConsumableException;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
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

    public function consume(CustomerInterface $customer, TokenPackInterface $consumable, string $reference, int $quantity = 1): void
    {
        $this->walletOperator->debit(
            $this->walletProvider->provideForCustomer($customer),
            new TokenDebit(
                amount: $this->price($consumable, $quantity),
                idempotencyKey: $reference,
            ),
        );
    }

    public function canConsume(CustomerInterface $customer, TokenPackInterface $consumable, int $quantity = 1): bool
    {
        return $this->getBalance($customer) >= $this->price($consumable, $quantity);
    }

    public function getBalance(CustomerInterface $customer): int
    {
        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);

        return null === $wallet ? 0 : $this->walletOperator->getBalance($wallet);
    }

    private function price(TokenPackInterface $consumable, int $quantity): int
    {
        if (!$consumable->isConsumable()) {
            throw new NotConsumableException();
        }

        if ($quantity <= 0) {
            throw new \InvalidArgumentException('The quantity must be positive.');
        }

        /** @var int $tokenPrice */
        $tokenPrice = $consumable->getTokenPrice();

        return $tokenPrice * $quantity;
    }
}
