<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Payment;

use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolverInterface;
use Guiziweb\SyliusTokenPlugin\Payment\Remover\OrderPaymentsRemover;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Payment\Remover\OrderPaymentsRemoverInterface;

final class OrderPaymentsRemoverTest extends TestCase
{
    public function testAFreeConsumablesOrderKeepsItsPayment(): void
    {
        $decorated = $this->createMock(OrderPaymentsRemoverInterface::class);
        $decorated->expects(self::never())->method('canRemovePayments');

        $remover = new OrderPaymentsRemover($decorated, $this->createKindResolver(OrderKind::Consumables));

        self::assertFalse($remover->canRemovePayments($this->createMock(OrderInterface::class)));
    }

    public function testARegularOrderDefersToSylius(): void
    {
        $decorated = $this->createMock(OrderPaymentsRemoverInterface::class);
        $decorated->method('canRemovePayments')->willReturn(true);

        $remover = new OrderPaymentsRemover($decorated, $this->createKindResolver(OrderKind::Regular));

        self::assertTrue($remover->canRemovePayments($this->createMock(OrderInterface::class)));
    }

    private function createKindResolver(OrderKind $kind): OrderKindResolverInterface
    {
        $resolver = $this->createMock(OrderKindResolverInterface::class);
        $resolver->method('resolve')->willReturn($kind);

        return $resolver;
    }
}
