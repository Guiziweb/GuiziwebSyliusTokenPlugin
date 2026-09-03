<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\Factory\TokenPackFactory;
use Guiziweb\SyliusTokenPlugin\Form\Extension\ProductVariant\ProductVariantTypeExtension;
use Guiziweb\SyliusTokenPlugin\Form\Type\Admin\TokenPackVariantType;
use Guiziweb\SyliusTokenPlugin\Repository\TokenProductRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_token.factory.token_pack', TokenPackFactory::class)
        ->decorate('sylius.factory.product')
        ->args([service('guiziweb_sylius_token.factory.token_pack.inner')])
    ;

    $services->set('guiziweb_sylius_token.form.extension.product_variant_type', ProductVariantTypeExtension::class)
        ->tag('form.type_extension')
    ;

    $services->set('guiziweb_sylius_token.form.type.admin.token_pack_variant', TokenPackVariantType::class)
        ->args([param('sylius.model.product_variant.class'), ['sylius']])
        ->tag('form.type')
    ;

    $services->set('guiziweb_sylius_token.repository.token_product', TokenProductRepository::class)
        ->public()
        ->args([service('doctrine'), param('sylius.model.product_variant.class')])
    ;
};
