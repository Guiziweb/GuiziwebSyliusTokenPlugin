<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\EventListener\Workflow\Payment;

use Guiziweb\SyliusTokenPlugin\Form\Type\TokenWalletGatewayConfigurationType;
use Guiziweb\SyliusTokenPlugin\Wallet\OrderTokenCreditorInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Webmozart\Assert\Assert;

final readonly class CreditTokensListener
{
    public function __construct(private OrderTokenCreditorInterface $orderTokenCreditor)
    {
    }

    public function __invoke(CompletedEvent $event): void
    {
        $payment = $event->getSubject();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        if (TokenWalletGatewayConfigurationType::GATEWAY_FACTORY === $payment->getMethod()?->getGatewayConfig()?->getFactoryName()) {
            return;
        }

        $order = $payment->getOrder();
        Assert::isInstanceOf($order, OrderInterface::class);

        $this->orderTokenCreditor->credit($order);
    }
}
