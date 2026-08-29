<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Twig;

use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumerInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class TokenBalanceRuntime implements RuntimeExtensionInterface
{
    private ?int $balance = null;

    private bool $resolved = false;

    public function __construct(
        private readonly CustomerContextInterface $customerContext,
        private readonly TokenConsumerInterface $tokenConsumer,
    ) {
    }

    public function getBalance(): ?int
    {
        if ($this->resolved) {
            return $this->balance;
        }

        $this->resolved = true;
        $customer = $this->customerContext->getCustomer();

        if ($customer instanceof CustomerInterface && $customer->hasUser()) {
            $this->balance = $this->tokenConsumer->getBalance($customer);
        }

        return $this->balance;
    }
}
