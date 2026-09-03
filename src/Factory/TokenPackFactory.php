<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Factory;

use Guiziweb\SyliusTokenPlugin\Model\TokenPackInterface;
use Sylius\Component\Core\Model\ProductInterface as CoreProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Sylius\Component\Product\Model\ProductInterface;
use Webmozart\Assert\Assert;

/**
 * @implements ProductFactoryInterface<ProductInterface>
 */
final class TokenPackFactory implements ProductFactoryInterface
{
    /** @param ProductFactoryInterface<ProductInterface> $decorated */
    public function __construct(
        private readonly ProductFactoryInterface $decorated,
    ) {
    }

    public function createNew(): ProductInterface
    {
        return $this->decorated->createNew();
    }

    public function createWithVariant(): ProductInterface
    {
        return $this->decorated->createWithVariant();
    }

    public function createTokenPack(): CoreProductInterface
    {
        $product = $this->decorated->createWithVariant();
        Assert::isInstanceOf($product, CoreProductInterface::class);

        $variant = $product->getVariants()->first();
        Assert::isInstanceOf($variant, TokenPackInterface::class, 'Apply TokenPackTrait to your ProductVariant entity.');
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        $variant->setShippingRequired(false);
        $variant->setTracked(false);

        return $product;
    }
}
