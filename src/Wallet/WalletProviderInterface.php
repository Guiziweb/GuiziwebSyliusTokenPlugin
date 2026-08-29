<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Sylius\Component\Core\Model\CustomerInterface;

interface WalletProviderInterface
{
    public function provideForCustomer(CustomerInterface $customer): TokenWalletInterface;
}
