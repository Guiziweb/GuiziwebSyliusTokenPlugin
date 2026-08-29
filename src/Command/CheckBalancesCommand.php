<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Command;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'guiziweb:tokens:check-balances',
    description: 'Compares each stored balance with the one derived from the ledger.',
)]
final class CheckBalancesCommand extends Command
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private readonly RepositoryInterface $walletRepository,
        private readonly WalletOperatorInterface $walletOperator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $drifted = [];

        foreach ($this->walletRepository->findAll() as $wallet) {
            $stored = $wallet->getBalance();
            $derived = $this->walletOperator->recalculateBalance($wallet);

            if ($stored !== $derived) {
                $drifted[] = [(string) $wallet->getId(), $stored, $derived];
            }
        }

        if ([] === $drifted) {
            $io->success('Every stored balance matches the ledger.');

            return Command::SUCCESS;
        }

        $io->table(['Wallet', 'Stored', 'Ledger'], $drifted);
        $io->error(sprintf('%d wallets drifted from the ledger.', count($drifted)));

        return Command::FAILURE;
    }
}
