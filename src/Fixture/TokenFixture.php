<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Fixture;

use Doctrine\Persistence\ObjectManager;
use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;
use Guiziweb\SyliusTokenPlugin\Factory\TokenPackFactory;
use Guiziweb\SyliusTokenPlugin\Model\TokenPackInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Webmozart\Assert\Assert;

final class TokenFixture extends AbstractFixture
{
    /**
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param RepositoryInterface<ChannelInterface> $channelRepository
     * @param RepositoryInterface<ProductInterface> $productRepository
     * @param FactoryInterface<TokenPriceInterface> $priceFactory
     * @param RepositoryInterface<TokenPriceInterface> $priceRepository
     */
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly TokenPackFactory $productFactory,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly RepositoryInterface $channelRepository,
        private readonly RepositoryInterface $productRepository,
        private readonly FactoryInterface $priceFactory,
        private readonly RepositoryInterface $priceRepository,
    ) {
    }

    public function getName(): string
    {
        return 'guiziweb_token';
    }

    /**
     * @param array{
     *     packs: array<int, array{name: string, tokens: int, price: int}>,
     *     prices: array<int, array{code: string, name: string, cost: int}>
     * } $options
     */
    public function load(array $options): void
    {
        /** @var array<int, ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

        foreach ($options['packs'] as $pack) {
            $this->createProduct($pack['name'], $pack['price'], $channels, tokenAmount: $pack['tokens']);
        }

        foreach ($options['prices'] as $price) {
            $this->createPrice($price['code'], $price['name'], $price['cost']);
        }

        $this->objectManager->flush();
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->arrayNode('packs')
                    ->defaultValue([
                        ['name' => 'Starter pack', 'tokens' => 100, 'price' => 1000],
                        ['name' => 'Pro pack', 'tokens' => 500, 'price' => 4000],
                        ['name' => 'Business pack', 'tokens' => 2000, 'price' => 14000],
                    ])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->integerNode('tokens')->min(1)->end()
                            ->integerNode('price')->min(0)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('prices')
                    ->defaultValue([
                        ['code' => 'cv_generation', 'name' => 'CV generation', 'cost' => 5],
                        ['code' => 'hd_image', 'name' => 'HD image generation', 'cost' => 20],
                    ])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('code')->cannotBeEmpty()->end()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->integerNode('cost')->min(1)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    private function createPrice(string $code, string $name, int $cost): void
    {
        if (null !== $this->priceRepository->findOneBy(['code' => $code])) {
            return;
        }

        $price = $this->priceFactory->createNew();
        $price->setCode($code);
        $price->setName($name);
        $price->setCost($cost);
        $price->setEnabled(true);

        $this->objectManager->persist($price);
    }

    /** @param array<int, ChannelInterface> $channels */
    private function createProduct(
        string $name,
        int $price,
        array $channels,
        ?int $tokenAmount = null,
    ): void {
        $code = StringInflector::nameToCode(strtoupper($name));

        if (null !== $this->productRepository->findOneBy(['code' => $code])) {
            return;
        }

        $product = $this->productFactory->createTokenPack();
        $product->setCode($code);
        $product->setEnabled(true);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($name);
        $product->setSlug(StringInflector::nameToSlug($name));

        foreach ($channels as $channel) {
            $product->addChannel($channel);
        }

        $variant = $product->getVariants()->first();
        Assert::isInstanceOf($variant, TokenPackInterface::class, 'Apply TokenPackTrait to your ProductVariant entity.');
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        $variant->setCode($code . '_VARIANT');
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setName($name);
        $variant->setTokenAmount($tokenAmount);

        foreach ($channels as $channel) {
            $channelPricing = $this->channelPricingFactory->createNew();
            $channelPricing->setChannelCode($channel->getCode());
            $channelPricing->setPrice($price);
            $variant->addChannelPricing($channelPricing);
        }

        $this->objectManager->persist($product);
    }
}
