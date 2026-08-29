<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Order;

enum OrderKind
{
    case Regular;
    case Consumables;
    case Mixed;
}
