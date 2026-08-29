<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment\CommandHandler;

use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Payment\Command\CaptureTokenWalletPayment;
use Guiziweb\SyliusTokenPlugin\Payment\OrderTokenPriceCalculatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Sylius\Component\Payment\PaymentTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler(bus: 'sylius.payment_request.command_bus')]
final readonly class CaptureHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private OrderTokenPriceCalculatorInterface $tokenPriceCalculator,
        private WalletProviderInterface $walletProvider,
        private WalletOperatorInterface $walletOperator,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function __invoke(CaptureTokenWalletPayment $command): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($command);

        $payment = $paymentRequest->getPayment();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        $customer = $order->getCustomer();
        Assert::isInstanceOf($customer, CustomerInterface::class);

        try {
            $this->walletOperator->debit(
                $this->walletProvider->provideForCustomer($customer),
                new TokenDebit(
                    amount: $this->tokenPriceCalculator->calculate($order),
                    idempotencyKey: sprintf('payment-%s', (string) $payment->getId()),
                    order: $order,
                ),
            );
        } catch (InsufficientTokenBalanceException) {
            $this->stateMachine->apply(
                $paymentRequest,
                PaymentRequestTransitions::GRAPH,
                PaymentRequestTransitions::TRANSITION_FAIL,
            );
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_FAIL);

            return;
        }

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE,
        );

        $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE);
    }
}
