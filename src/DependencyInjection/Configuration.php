<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /** @psalm-suppress UnusedVariable */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        return new TreeBuilder('guiziweb_sylius_token');
    }
}
