<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Provider;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CurrentWalletProvider
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private RequestStack $requestStack,
        private RepositoryInterface $walletRepository,
    ) {
    }

    /** @throws NotFoundHttpException */
    public function getWallet(): TokenWalletInterface
    {
        $request = $this->requestStack->getCurrentRequest();

        if (null === $request) {
            throw new NotFoundHttpException('No current request to read a token wallet identifier from.');
        }

        $id = $request->attributes->get('id');

        if (!is_numeric($id)) {
            throw new NotFoundHttpException('The token wallet identifier is missing or not a number.');
        }

        $wallet = $this->walletRepository->find((int) $id);

        if (null === $wallet) {
            throw new NotFoundHttpException(sprintf('No token wallet with identifier "%s".', (string) $id));
        }

        return $wallet;
    }
}
