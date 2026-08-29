<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Entity\TokenBatch;

enum TokenBatchOrigin: string
{
    case Purchase = 'purchase';
    case Adjustment = 'adjustment';
}
