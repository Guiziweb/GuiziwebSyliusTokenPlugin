<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\Form\Type\TokenPriceType;
use Guiziweb\SyliusTokenPlugin\Provider\CurrentWalletProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_token.form.type.price', TokenPriceType::class)
        ->args([param('guiziweb_sylius_token.model.price.class'), ['sylius']])
        ->tag('form.type')
    ;

    $services->set('guiziweb_sylius_token.provider.current_wallet', CurrentWalletProvider::class)
        ->public()
        ->args([
            service('request_stack'),
            service('guiziweb_sylius_token.repository.wallet'),
        ])
    ;
};
