<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TokenBalanceExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('guiziweb_token_balance', [TokenBalanceRuntime::class, 'getBalance']),
            new TwigFunction('guiziweb_customer_wallet', [CustomerWalletRuntime::class, 'getWallet']),
            new TwigFunction('guiziweb_customer_token_statistics', [CustomerWalletRuntime::class, 'getStatistics']),
        ];
    }
}
