<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Twig;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumerInterface;
use Psr\Clock\ClockInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class TokenBalanceRuntime implements RuntimeExtensionInterface
{
    private ?int $balance = null;

    private bool $resolved = false;

    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private readonly CustomerContextInterface $customerContext,
        private readonly TokenConsumerInterface $tokenConsumer,
        private readonly RepositoryInterface $walletRepository,
        private readonly TokenBatchRepositoryInterface $batchRepository,
        private readonly ClockInterface $clock,
    ) {
    }

    public function getNextExpiringBatch(): ?TokenBatchInterface
    {
        $customer = $this->customerContext->getCustomer();

        if (!$customer instanceof CustomerInterface || !$customer->hasUser()) {
            return null;
        }

        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);

        if (!$wallet instanceof TokenWalletInterface) {
            return null;
        }

        return $this->batchRepository->findNextExpiring($wallet, $this->clock->now());
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
