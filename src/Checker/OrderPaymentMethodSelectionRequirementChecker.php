<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Checker;

use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolverInterface;
use Sylius\Component\Core\Checker\OrderPaymentMethodSelectionRequirementCheckerInterface;
use Sylius\Component\Core\Model\OrderInterface;

final readonly class OrderPaymentMethodSelectionRequirementChecker implements OrderPaymentMethodSelectionRequirementCheckerInterface
{
    public function __construct(
        private OrderPaymentMethodSelectionRequirementCheckerInterface $decorated,
        private OrderKindResolverInterface $orderKindResolver,
    ) {
    }

    public function isPaymentMethodSelectionRequired(OrderInterface $order): bool
    {
        if (OrderKind::Regular !== $this->orderKindResolver->resolve($order)) {
            return true;
        }

        return $this->decorated->isPaymentMethodSelectionRequired($order);
    }
}
