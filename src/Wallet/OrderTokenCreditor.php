<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Webmozart\Assert\Assert;

final readonly class OrderTokenCreditor implements OrderTokenCreditorInterface
{
    public function __construct(
        private WalletProviderInterface $walletProvider,
        private WalletOperatorInterface $walletOperator,
    ) {
    }

    public function credit(OrderInterface $order): void
    {
        $customer = $order->getCustomer();

        if (null === $customer) {
            return;
        }

        Assert::isInstanceOf($customer, CustomerInterface::class);

        $wallet = null;

        foreach ($order->getItems() as $item) {
            $amount = $this->tokensBought($item);

            if (null === $amount) {
                continue;
            }

            $wallet ??= $this->walletProvider->provideForCustomer($customer);

            $this->walletOperator->credit($wallet, new TokenCredit(
                amount: $amount,
                idempotencyKey: sprintf('order-item-%s', (string) $item->getId()),
                order: $order,
                price: $this->pricePaid($item, $order),
            ));
        }
    }

    private function tokensBought(OrderItemInterface $item): ?int
    {
        $variant = $item->getVariant();

        if (!$variant instanceof TokenPackInterface || !$variant->isTokenPack()) {
            return null;
        }

        /** @var int $tokenAmount */
        $tokenAmount = $variant->getTokenAmount();

        return $tokenAmount * $item->getQuantity();
    }

    private function pricePaid(OrderItemInterface $item, OrderInterface $order): ?PurchasePrice
    {
        $currencyCode = $order->getCurrencyCode();

        if (null === $currencyCode) {
            return null;
        }

        return new PurchasePrice($item->getTotal(), $currencyCode);
    }
}
