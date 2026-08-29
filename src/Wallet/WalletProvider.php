<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWallet;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Psr\Clock\ClockInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final readonly class WalletProvider implements WalletProviderInterface
{
    /**
     * @param RepositoryInterface<TokenWalletInterface> $walletRepository
     */
    public function __construct(
        private RepositoryInterface $walletRepository,
        private EntityManagerInterface $entityManager,
        private ClockInterface $clock,
    ) {
    }

    public function provideForCustomer(CustomerInterface $customer): TokenWalletInterface
    {
        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);

        if (null !== $wallet) {
            return $wallet;
        }

        $wallet = new TokenWallet($this->clock->now());
        $wallet->setCustomer($customer);
        $this->entityManager->persist($wallet);

        return $wallet;
    }
}
