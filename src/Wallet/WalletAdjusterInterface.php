<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Model\WalletAdjustment;

interface WalletAdjusterInterface
{
    /** @throws InsufficientTokenBalanceException */
    public function adjust(TokenWalletInterface $wallet, WalletAdjustment $adjustment): void;
}
