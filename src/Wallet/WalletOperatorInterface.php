<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;

interface WalletOperatorInterface
{
    public function credit(TokenWalletInterface $wallet, TokenCredit $credit): ?TokenBatchInterface;

    /**
     * @throws InsufficientTokenBalanceException
     */
    public function debit(TokenWalletInterface $wallet, TokenDebit $debit): void;

    public function getBalance(TokenWalletInterface $wallet): int;
}
