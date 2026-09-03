<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Model;

use Symfony\Component\Validator\Constraints as Assert;

final class WalletAdjustment
{
    public const DIRECTION_CREDIT = 'credit';

    public const DIRECTION_DEBIT = 'debit';

    #[Assert\Choice(choices: [self::DIRECTION_CREDIT, self::DIRECTION_DEBIT])]
    public string $direction = self::DIRECTION_CREDIT;

    #[Assert\NotBlank]
    #[Assert\Positive]
    public ?int $amount = null;

    #[Assert\NotBlank]
    public ?string $reason = null;

    #[Assert\NotBlank]
    public string $operationId;

    public function __construct()
    {
        $this->operationId = bin2hex(random_bytes(16));
    }

    public function isCredit(): bool
    {
        return self::DIRECTION_CREDIT === $this->direction;
    }
}
