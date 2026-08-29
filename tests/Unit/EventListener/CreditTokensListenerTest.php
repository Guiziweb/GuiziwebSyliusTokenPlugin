<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\EventListener;

use Guiziweb\SyliusTokenPlugin\EventListener\Workflow\Payment\CreditTokensListener;
use Guiziweb\SyliusTokenPlugin\Wallet\OrderTokenCreditorInterface;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Symfony\Component\Workflow\Marking;

final class CreditTokensListenerTest extends TestCase
{
    public function testAPaymentInMoneyCreditsTheOrder(): void
    {
        $creditor = $this->createMock(OrderTokenCreditorInterface::class);
        $creditor->expects(self::once())->method('credit');

        (new CreditTokensListener($creditor))($this->createEvent());
    }

    private function createEvent(): CompletedEvent
    {
        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getOrder')->willReturn($this->createMock(OrderInterface::class));

        return new CompletedEvent($payment, new Marking());
    }
}
