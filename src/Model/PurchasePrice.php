<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Model;

final readonly class PurchasePrice
{
    public function __construct(
        public int $amount,
        public string $currencyCode,
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('A purchase price cannot be negative.');
        }

        if (3 !== strlen($currencyCode)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not an ISO 4217 currency code.', $currencyCode));
        }
    }
}
