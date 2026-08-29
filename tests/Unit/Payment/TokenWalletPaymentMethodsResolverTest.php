<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Payment;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolverInterface;
use Guiziweb\SyliusTokenPlugin\Payment\OrderTokenPriceCalculatorInterface;
use Guiziweb\SyliusTokenPlugin\Payment\TokenWalletPaymentMethodsResolver;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Sylius\Component\Payment\Resolver\PaymentMethodsResolverInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final class TokenWalletPaymentMethodsResolverTest extends TestCase
{
    private const TOKEN_PRICE = 30;

    private PaymentMethodsResolverInterface&MockObject $decorated;

    private OrderKindResolverInterface&MockObject $orderKindResolver;

    private RepositoryInterface&MockObject $walletRepository;

    private WalletOperatorInterface&MockObject $walletOperator;

    private PaymentMethodInterface $tokenWallet;

    private PaymentMethodInterface $card;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(PaymentMethodsResolverInterface::class);
        $this->orderKindResolver = $this->createMock(OrderKindResolverInterface::class);
        $this->walletRepository = $this->createMock(RepositoryInterface::class);
        $this->walletOperator = $this->createMock(WalletOperatorInterface::class);
        $this->tokenWallet = $this->createPaymentMethod('token_wallet');
        $this->card = $this->createPaymentMethod('stripe');

        $this->decorated->method('getSupportedMethods')->willReturn([$this->card, $this->tokenWallet]);
        $this->walletRepository->method('findOneBy')->willReturn($this->createMock(TokenWalletInterface::class));
    }

    public function testARegularOrderCannotBePaidWithTokens(): void
    {
        $this->orderKindResolver->method('resolve')->willReturn(OrderKind::Regular);
        $this->walletOperator->method('getBalance')->willReturn(10000);

        self::assertSame([$this->card], $this->createResolver()->getSupportedMethods($this->createPayment()));
    }

    public function testConsumablesCanOnlyBePaidWithTokens(): void
    {
        $this->orderKindResolver->method('resolve')->willReturn(OrderKind::Consumables);
        $this->walletOperator->method('getBalance')->willReturn(self::TOKEN_PRICE);

        self::assertSame([$this->tokenWallet], $this->createResolver()->getSupportedMethods($this->createPayment()));
    }

    public function testConsumablesCannotBePaidAtAllWhenTheBalanceIsTooLow(): void
    {
        $this->orderKindResolver->method('resolve')->willReturn(OrderKind::Consumables);
        $this->walletOperator->method('getBalance')->willReturn(self::TOKEN_PRICE - 1);

        self::assertSame([], $this->createResolver()->getSupportedMethods($this->createPayment()));
    }

    public function testGuestsCannotBuyConsumables(): void
    {
        $this->orderKindResolver->method('resolve')->willReturn(OrderKind::Consumables);
        $this->walletOperator->method('getBalance')->willReturn(10000);

        self::assertSame([], $this->createResolver()->getSupportedMethods($this->createPayment(hasUser: false)));
    }

    public function testAMixedOrderCannotBePaidAtAll(): void
    {
        $this->orderKindResolver->method('resolve')->willReturn(OrderKind::Mixed);
        $this->walletOperator->method('getBalance')->willReturn(10000);

        self::assertSame([], $this->createResolver()->getSupportedMethods($this->createPayment()));
    }

    private function createResolver(): TokenWalletPaymentMethodsResolver
    {
        $calculator = $this->createMock(OrderTokenPriceCalculatorInterface::class);
        $calculator->method('calculate')->willReturn(self::TOKEN_PRICE);

        return new TokenWalletPaymentMethodsResolver(
            $this->decorated,
            $this->orderKindResolver,
            $calculator,
            $this->walletRepository,
            $this->walletOperator,
        );
    }

    private function createPaymentMethod(string $factoryName): PaymentMethodInterface
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn($factoryName);

        $method = $this->createMock(PaymentMethodInterface::class);
        $method->method('getGatewayConfig')->willReturn($gatewayConfig);

        return $method;
    }

    private function createPayment(bool $hasUser = true): PaymentInterface
    {
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('hasUser')->willReturn($hasUser);

        $order = $this->createMock(OrderInterface::class);
        $order->method('getCustomer')->willReturn($customer);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getOrder')->willReturn($order);

        return $payment;
    }
}
