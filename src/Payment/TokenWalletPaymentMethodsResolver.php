<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Form\Type\TokenWalletGatewayConfigurationType;
use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolverInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\PaymentInterface as BasePaymentInterface;
use Sylius\Component\Payment\Model\PaymentMethodInterface as BasePaymentMethodInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final readonly class TokenWalletPaymentMethodsResolver implements PaymentMethodsResolverInterface
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private PaymentMethodsResolverInterface $decorated,
        private OrderKindResolverInterface $orderKindResolver,
        private OrderTokenPriceCalculatorInterface $tokenPriceCalculator,
        private RepositoryInterface $walletRepository,
        private WalletOperatorInterface $walletOperator,
    ) {
    }

    public function getSupportedMethods(BasePaymentInterface $subject): array
    {
        $methods = $this->decorated->getSupportedMethods($subject);

        if (!$subject instanceof PaymentInterface) {
            return $methods;
        }

        $order = $subject->getOrder();

        if (!$order instanceof OrderInterface) {
            return $methods;
        }

        return match ($this->orderKindResolver->resolve($order)) {
            OrderKind::Regular => $this->without($methods, tokenWallet: true),
            OrderKind::Consumables => $this->hasEnoughTokens($order) ? $this->without($methods, tokenWallet: false) : [],
            OrderKind::Mixed => [],
        };
    }

    public function supports(BasePaymentInterface $subject): bool
    {
        return $this->decorated->supports($subject);
    }

    /**
     * @param array<int, BasePaymentMethodInterface> $methods
     *
     * @return array<int, BasePaymentMethodInterface>
     */
    private function without(array $methods, bool $tokenWallet): array
    {
        return array_values(array_filter(
            $methods,
            fn (BasePaymentMethodInterface $method): bool => $this->isTokenWallet($method) !== $tokenWallet,
        ));
    }

    private function isTokenWallet(BasePaymentMethodInterface $method): bool
    {
        return $method instanceof PaymentMethodInterface &&
            TokenWalletGatewayConfigurationType::GATEWAY_FACTORY === $method->getGatewayConfig()?->getFactoryName();
    }

    private function hasEnoughTokens(OrderInterface $order): bool
    {
        $customer = $order->getCustomer();

        if (!$customer instanceof CustomerInterface || !$customer->hasUser()) {
            return false;
        }

        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);

        if (null === $wallet) {
            return false;
        }

        return $this->walletOperator->getBalance($wallet) >= $this->tokenPriceCalculator->calculate($order);
    }
}
