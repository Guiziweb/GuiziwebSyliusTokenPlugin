<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\EventListener;

use Guiziweb\SyliusTokenPlugin\EventListener\Workflow\Payment\CreditTokensListener;
use Guiziweb\SyliusTokenPlugin\Wallet\OrderTokenCreditorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Payment\Model\GatewayConfigInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

final class CreditTokensListenerTest extends TestCase
{
    public function testAPaymentInMoneyCreditsTheOrder(): void
    {
        $creditor = $this->createMock(OrderTokenCreditorInterface::class);
        $creditor->expects(self::once())->method('credit');

        (new CreditTokensListener($creditor))($this->createEvent('offline'));
    }

    public function testAPaymentInTokensNeverCreditsTokens(): void
    {
        $creditor = $this->createMock(OrderTokenCreditorInterface::class);
        $creditor->expects(self::never())->method('credit');

        (new CreditTokensListener($creditor))($this->createEvent('token_wallet'));
    }

    private function createEvent(string $gatewayFactory): CompletedEvent
    {
        $gatewayConfig = $this->createMock(GatewayConfigInterface::class);
        $gatewayConfig->method('getFactoryName')->willReturn($gatewayFactory);

        $method = $this->createMock(PaymentMethodInterface::class);
        $method->method('getGatewayConfig')->willReturn($gatewayConfig);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getMethod')->willReturn($method);
        $payment->method('getOrder')->willReturn($this->createMock(OrderInterface::class));

        return new CompletedEvent($payment, new Marking());
    }
}
