<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Guiziweb\SyliusTokenPlugin\Factory\TokenPackFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Component\Product\Factory\ProductFactoryInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Entity\Product\ProductVariant;
use Webmozart\Assert\InvalidArgumentException;

final class TokenPackFactoryTest extends TestCase
{
    private ProductFactoryInterface&MockObject $decorated;

    protected function setUp(): void
    {
        $this->decorated = $this->createMock(ProductFactoryInterface::class);
    }

    public function testItCreatesAPackThatNeverShipsAndIsNotTracked(): void
    {
        $variant = new ProductVariant();
        $variant->setShippingRequired(true);
        $variant->setTracked(true);

        $this->decorated->method('createWithVariant')->willReturn($this->createProductWith($variant));

        (new TokenPackFactory($this->decorated))->createTokenPack();

        self::assertFalse($variant->isShippingRequired());
        self::assertFalse($variant->isTracked());
    }

    public function testItRejectsAVariantThatDoesNotCarryTheTokenPackTrait(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getVariants')->willReturn(new ArrayCollection([
            $this->createMock(ProductVariantInterface::class),
        ]));

        $this->decorated->method('createWithVariant')->willReturn($product);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Apply TokenPackTrait to your ProductVariant entity.');

        (new TokenPackFactory($this->decorated))->createTokenPack();
    }

    public function testItDelegatesPlainCreationToTheDecoratedFactory(): void
    {
        $product = $this->createMock(ProductInterface::class);
        $this->decorated->expects(self::once())->method('createNew')->willReturn($product);

        self::assertSame($product, (new TokenPackFactory($this->decorated))->createNew());
    }

    private function createProductWith(ProductVariant $variant): ProductInterface
    {
        $product = $this->createMock(ProductInterface::class);
        $product->method('getVariants')->willReturn(new ArrayCollection([$variant]));

        return $product;
    }
}
