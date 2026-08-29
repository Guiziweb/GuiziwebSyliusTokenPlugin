<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Exception;

final class InsufficientTokenBalanceException extends \RuntimeException
{
    public function __construct(
        private readonly int $requestedAmount,
        private readonly int $availableAmount,
    ) {
        parent::__construct(sprintf(
            'Not enough tokens: %d requested, %d available.',
            $requestedAmount,
            $availableAmount,
        ));
    }

    public function getRequestedAmount(): int
    {
        return $this->requestedAmount;
    }

    public function getAvailableAmount(): int
    {
        return $this->availableAmount;
    }
}
