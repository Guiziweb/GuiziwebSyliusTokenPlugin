<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\Fixture\TokenFixture;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('guiziweb_sylius_token.fixture.token', TokenFixture::class)
        ->args([
            service('doctrine.orm.entity_manager'),
            service('sylius.factory.product'),
            service('sylius.factory.channel_pricing'),
            service('sylius.repository.channel'),
            service('sylius.repository.product'),
            service('guiziweb_sylius_token.factory.price'),
            service('guiziweb_sylius_token.repository.price'),
        ])
        ->tag('sylius_fixtures.fixture')
    ;
};
