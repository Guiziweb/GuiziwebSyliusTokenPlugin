<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Exception;

use Guiziweb\SyliusTokenPlugin\Entity\TokenTariff\TokenTariffInterface;

final class TariffNotAvailableException extends \RuntimeException
{
    public function __construct(TokenTariffInterface $tariff)
    {
        parent::__construct(sprintf('The tariff "%s" is disabled or has no cost.', (string) $tariff->getCode()));
    }
}
