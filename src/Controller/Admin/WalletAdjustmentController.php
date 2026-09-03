<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Controller\Admin;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Form\Type\Admin\WalletAdjustmentType;
use Guiziweb\SyliusTokenPlugin\Model\WalletAdjustment;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletAdjusterInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

final readonly class WalletAdjustmentController
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private RepositoryInterface $walletRepository,
        private WalletOperatorInterface $walletOperator,
        private WalletAdjusterInterface $walletAdjuster,
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $wallet = $this->walletRepository->find($id);

        if (!$wallet instanceof TokenWalletInterface) {
            throw new NotFoundHttpException();
        }

        if (null === $wallet->getCustomer()) {
            return $this->redirectToIndex($request, 'error', 'guiziweb_sylius_token.flash.wallet_without_customer');
        }

        $adjustment = new WalletAdjustment();
        $form = $this->formFactory->create(WalletAdjustmentType::class, $adjustment);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response($this->twig->render('@GuiziwebSyliusTokenPlugin/admin/wallet/adjust.html.twig', [
                'wallet' => $wallet,
                'balance' => $this->walletOperator->getBalance($wallet),
                'form' => $form->createView(),
            ]));
        }

        try {
            $this->walletAdjuster->adjust($wallet, $adjustment);
        } catch (InsufficientTokenBalanceException) {
            return $this->redirectToIndex($request, 'error', 'guiziweb_sylius_token.flash.insufficient_balance');
        }

        return $this->redirectToIndex(
            $request,
            'success',
            $adjustment->isCredit() ? 'guiziweb_sylius_token.flash.credited' : 'guiziweb_sylius_token.flash.debited',
        );
    }

    private function redirectToIndex(Request $request, string $type, string $message): RedirectResponse
    {
        $session = $request->getSession();

        if ($session instanceof FlashBagAwareSessionInterface) {
            $session->getFlashBag()->add($type, $message);
        }

        return new RedirectResponse($this->urlGenerator->generate('guiziweb_sylius_token_admin_wallet_index'));
    }
}
