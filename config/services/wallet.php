<?php

declare(strict_types=1);

use Guiziweb\SyliusTokenPlugin\Factory\TokenBatchFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenBatchFactoryInterface;
use Guiziweb\SyliusTokenPlugin\Factory\TokenTransactionFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenTransactionFactoryInterface;
use Guiziweb\SyliusTokenPlugin\Factory\TokenWalletFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenWalletFactoryInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenOperationRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Repository\TokenTransactionRepositoryInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\BatchAllocator;
use Guiziweb\SyliusTokenPlugin\Wallet\BatchAllocatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumer;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumerInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletAdjuster;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletAdjusterInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperator;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProvider;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->alias(TokenBatchRepositoryInterface::class, 'guiziweb_sylius_token.repository.batch');
    $services->alias(TokenTransactionRepositoryInterface::class, 'guiziweb_sylius_token.repository.transaction');
    $services->alias(TokenOperationRepositoryInterface::class, 'guiziweb_sylius_token.repository.operation');

    $services->set('guiziweb_sylius_token.wallet.batch_allocator', BatchAllocator::class);
    $services->alias(BatchAllocatorInterface::class, 'guiziweb_sylius_token.wallet.batch_allocator');


    $services->set('guiziweb_sylius_token.wallet.factory', TokenWalletFactory::class)
        ->args([param('guiziweb_sylius_token.model.wallet.class')])
    ;
    $services->alias(TokenWalletFactoryInterface::class, 'guiziweb_sylius_token.wallet.factory');

    $services->set('guiziweb_sylius_token.batch.factory', TokenBatchFactory::class)
        ->args([param('guiziweb_sylius_token.model.batch.class')])
    ;
    $services->alias(TokenBatchFactoryInterface::class, 'guiziweb_sylius_token.batch.factory');

    $services->set('guiziweb_sylius_token.transaction.factory', TokenTransactionFactory::class)
        ->args([param('guiziweb_sylius_token.model.transaction.class')])
    ;
    $services->alias(TokenTransactionFactoryInterface::class, 'guiziweb_sylius_token.transaction.factory');

    $services->set('guiziweb_sylius_token.wallet.provider', WalletProvider::class)
        ->args([
            service('guiziweb_sylius_token.repository.wallet'),
            service('doctrine.orm.entity_manager'),
            service('clock'),
            service('guiziweb_sylius_token.wallet.factory'),
        ])
    ;
    $services->alias(WalletProviderInterface::class, 'guiziweb_sylius_token.wallet.provider');

    $services->set('guiziweb_sylius_token.wallet.operator', WalletOperator::class)
        ->args([
            service('doctrine.orm.entity_manager'),
            service('guiziweb_sylius_token.repository.batch'),
            service('guiziweb_sylius_token.repository.operation'),
            service('guiziweb_sylius_token.wallet.batch_allocator'),
            service('clock'),
            service('guiziweb_sylius_token.batch.factory'),
            service('guiziweb_sylius_token.transaction.factory'),
        ])
    ;
    $services->alias(WalletOperatorInterface::class, 'guiziweb_sylius_token.wallet.operator');

    $services->set('guiziweb_sylius_token.wallet.consumer', TokenConsumer::class)
        ->args([
            service('guiziweb_sylius_token.repository.wallet'),
            service('guiziweb_sylius_token.wallet.provider'),
            service('guiziweb_sylius_token.wallet.operator'),
        ])
    ;
    $services->alias(TokenConsumerInterface::class, 'guiziweb_sylius_token.wallet.consumer');

    $services->set('guiziweb_sylius_token.wallet.adjuster', WalletAdjuster::class)
        ->args([service('guiziweb_sylius_token.wallet.operator')])
    ;
    $services->alias(WalletAdjusterInterface::class, 'guiziweb_sylius_token.wallet.adjuster');
};
