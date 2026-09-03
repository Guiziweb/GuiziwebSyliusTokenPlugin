<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Exception;

use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;

final class TokenPriceNotAvailableException extends \RuntimeException
{
    public function __construct(TokenPriceInterface $price)
    {
        parent::__construct(sprintf('The price "%s" is disabled or has no cost.', (string) $price->getCode()));
    }
}
