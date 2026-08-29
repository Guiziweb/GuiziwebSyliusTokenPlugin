<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class OrderPayableWithTokens extends Constraint
{
    public string $mixedMessage = 'guiziweb_sylius_token.order.mixed';

    public string $guestMessage = 'guiziweb_sylius_token.order.guest';

    public string $insufficientBalanceMessage = 'guiziweb_sylius_token.order.insufficient_balance';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return 'guiziweb_sylius_token_order_payable_with_tokens_validator';
    }
}
