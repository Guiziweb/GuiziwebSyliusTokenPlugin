<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Provider;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class CurrentWalletProvider
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private RequestStack $requestStack,
        private RepositoryInterface $walletRepository,
    ) {
    }

    public function getWallet(): ?TokenWalletInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            return null;
        }

        $id = $request->attributes->get('id');

        if (!is_numeric($id)) {
            return null;
        }

        return $this->walletRepository->find((int) $id);
    }
}
