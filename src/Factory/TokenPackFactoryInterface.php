<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Sylius\Component\Core\Model\ProductInterface as CoreProductInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Model\ProductInterface;

/**
 * @template T of ProductInterface
 * @extends ProductFactoryInterface<T>
 */
interface TokenPackFactoryInterface extends ProductFactoryInterface
{
    public function createTokenPack(): CoreProductInterface;
}
