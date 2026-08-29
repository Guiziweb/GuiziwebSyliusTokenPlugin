<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Controller\Admin;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchOrigin;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Form\Type\Admin\WalletAdjustmentType;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class WalletAdjustmentController extends AbstractController
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private readonly RepositoryInterface $walletRepository,
        private readonly WalletOperatorInterface $walletOperator,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function __invoke(Request $request, int $id): Response
    {
        $wallet = $this->walletRepository->find($id);

        if (!$wallet instanceof TokenWalletInterface) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(WalletAdjustmentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var array{direction: string, amount: int, reason: string, operationId: string} $data */
            $data = $form->getData();
            $this->adjust($wallet, $data);

            return $this->redirect($this->urlGenerator->generate('guiziweb_sylius_token_admin_wallet_index'));
        }

        return $this->render('@GuiziwebSyliusTokenPlugin/admin/wallet/adjust.html.twig', [
            'wallet' => $wallet,
            'balance' => $this->walletOperator->getBalance($wallet),
            'form' => $form->createView(),
        ]);
    }

    /** @param array{direction: string, amount: int, reason: string, operationId: string} $data */
    private function adjust(TokenWalletInterface $wallet, array $data): void
    {
        $key = sprintf('admin-%s-%s', (string) $wallet->getId(), $data['operationId']);

        if ('credit' === $data['direction']) {
            $this->walletOperator->credit($wallet, new TokenCredit(
                amount: $data['amount'],
                idempotencyKey: $key,
                origin: TokenBatchOrigin::Adjustment,
                reason: $data['reason'],
            ));

            $this->addFlash('success', 'guiziweb_sylius_token.flash.credited');

            return;
        }

        try {
            $this->walletOperator->debit($wallet, new TokenDebit(
                amount: $data['amount'],
                idempotencyKey: $key,
                reason: $data['reason'],
            ));

            $this->addFlash('success', 'guiziweb_sylius_token.flash.debited');
        } catch (InsufficientTokenBalanceException) {
            $this->addFlash('error', 'guiziweb_sylius_token.flash.insufficient_balance');
        }
    }
}
