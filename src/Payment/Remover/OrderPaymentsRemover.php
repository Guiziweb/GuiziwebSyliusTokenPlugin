<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment\Remover;

use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolverInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Payment\Remover\OrderPaymentsRemoverInterface;

final readonly class OrderPaymentsRemover implements OrderPaymentsRemoverInterface
{
    public function __construct(
        private OrderPaymentsRemoverInterface $decorated,
        private OrderKindResolverInterface $orderKindResolver,
    ) {
    }

    public function canRemovePayments(OrderInterface $order): bool
    {
        if (OrderKind::Regular !== $this->orderKindResolver->resolve($order)) {
            return false;
        }

        return $this->decorated->canRemovePayments($order);
    }

    public function removePayments(OrderInterface $order): void
    {
        $this->decorated->removePayments($order);
    }
}
