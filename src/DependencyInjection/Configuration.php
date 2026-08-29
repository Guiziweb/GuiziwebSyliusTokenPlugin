<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @psalm-suppress UnusedVariable
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('guiziweb_sylius_token');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('expiration')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('period')
                            ->defaultValue('P1Y')
                            ->info('ISO 8601 duration added to the acquisition date, e.g. P1Y or P6M.')
                            ->validate()
                                ->ifTrue(static function (mixed $period): bool {
                                    if (!is_string($period)) {
                                        return true;
                                    }

                                    try {
                                        new \DateInterval($period);

                                        return false;
                                    } catch (\Exception) {
                                        return true;
                                    }
                                })
                                ->thenInvalid('%s is not a valid ISO 8601 duration.')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
