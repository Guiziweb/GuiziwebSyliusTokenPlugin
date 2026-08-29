<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Wallet;

interface TokenExpirerInterface
{
    /** @return int the number of tokens that were expired */
    public function expire(): int;
}
