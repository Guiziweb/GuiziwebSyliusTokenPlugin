<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction;

enum TokenTransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
}
