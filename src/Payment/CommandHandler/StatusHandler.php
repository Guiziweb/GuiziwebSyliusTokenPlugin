<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment\CommandHandler;

use Guiziweb\SyliusTokenPlugin\Payment\Command\StatusTokenWalletPayment;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Bundle\PaymentBundle\Provider\PaymentRequestProviderInterface;
use Sylius\Component\Payment\PaymentRequestTransitions;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'sylius.payment_request.command_bus')]
final readonly class StatusHandler
{
    public function __construct(
        private PaymentRequestProviderInterface $paymentRequestProvider,
        private StateMachineInterface $stateMachine,
    ) {
    }

    public function __invoke(StatusTokenWalletPayment $command): void
    {
        $paymentRequest = $this->paymentRequestProvider->provide($command);

        $this->stateMachine->apply(
            $paymentRequest,
            PaymentRequestTransitions::GRAPH,
            PaymentRequestTransitions::TRANSITION_COMPLETE,
        );
    }
}
