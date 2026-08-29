<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;

final class TokenWalletGatewayConfigurationType extends AbstractType
{
    public const GATEWAY_FACTORY = 'token_wallet';

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_token_gateway_configuration';
    }
}
