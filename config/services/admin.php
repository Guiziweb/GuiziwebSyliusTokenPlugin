<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\Controller\Admin\WalletAdjustmentController;
use Guiziweb\SyliusTokenPlugin\Form\Type\Admin\WalletAdjustmentType;
use Guiziweb\SyliusTokenPlugin\Menu\AdminMenuListener;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_token.listener.menu.admin', AdminMenuListener::class)
        ->tag('kernel.event_listener', ['event' => 'sylius.menu.admin.main'])
    ;

    $services->set('guiziweb_sylius_token.form.type.admin.wallet_adjustment', WalletAdjustmentType::class)
        ->tag('form.type')
    ;

    $services->set(WalletAdjustmentController::class)
        ->public()
        ->args([
            service('guiziweb_sylius_token.repository.wallet'),
            service('guiziweb_sylius_token.wallet.operator'),
            service('guiziweb_sylius_token.wallet.adjuster'),
            service('form.factory'),
            service('router'),
            service('twig'),
        ])
    ;
};
