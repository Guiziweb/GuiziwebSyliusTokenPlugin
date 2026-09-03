<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\EventListener\Workflow\Payment\CreditTokensListener;
use Guiziweb\SyliusTokenPlugin\Wallet\OrderTokenCreditor;
use Guiziweb\SyliusTokenPlugin\Wallet\OrderTokenCreditorInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('guiziweb_sylius_token.wallet.order_token_creditor', OrderTokenCreditor::class)
        ->args([
            service('guiziweb_sylius_token.wallet.provider'),
            service('guiziweb_sylius_token.wallet.operator'),
            service('clock'),
        ])
    ;
    $services->alias(OrderTokenCreditorInterface::class, 'guiziweb_sylius_token.wallet.order_token_creditor');

    $services->set('guiziweb_sylius_token.listener.workflow.payment.credit_tokens', CreditTokensListener::class)
        ->args([service('guiziweb_sylius_token.wallet.order_token_creditor')])
        ->tag('kernel.event_listener', ['event' => 'workflow.sylius_payment.completed.complete'])
    ;
};
