<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\Menu\AccountMenuListener;
use Guiziweb\SyliusTokenPlugin\Twig\CustomerWalletRuntime;
use Guiziweb\SyliusTokenPlugin\Twig\TokenBalanceExtension;
use Guiziweb\SyliusTokenPlugin\Twig\TokenBalanceRuntime;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_token.listener.menu.shop_account', AccountMenuListener::class)
        ->tag('kernel.event_listener', ['event' => 'sylius.menu.shop.account'])
    ;

    $services->set('guiziweb_sylius_token.twig.runtime.token_balance', TokenBalanceRuntime::class)
        ->args([
            service('sylius.context.customer'),
            service('guiziweb_sylius_token.wallet.consumer'),
            service('guiziweb_sylius_token.repository.wallet'),
            service('guiziweb_sylius_token.repository.batch'),
            service('clock'),
        ])
        ->tag('twig.runtime')
    ;

    $services->set('guiziweb_sylius_token.twig.extension.token_balance', TokenBalanceExtension::class)
        ->tag('twig.extension')
    ;

    $services->set('guiziweb_sylius_token.twig.runtime.customer_wallet', CustomerWalletRuntime::class)
        ->args([
            service('guiziweb_sylius_token.repository.wallet'),
            service('guiziweb_sylius_token.repository.transaction'),
            service('guiziweb_sylius_token.wallet.operator'),
        ])
        ->tag('twig.runtime')
    ;
};
