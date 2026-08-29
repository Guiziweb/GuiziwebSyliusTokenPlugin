<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Fixture;

use Doctrine\Persistence\ObjectManager;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTariff\TokenTariffInterface;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
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
     * @param FactoryInterface<ProductInterface> $productFactory
     * @param FactoryInterface<ProductVariantInterface> $productVariantFactory
     * @param FactoryInterface<ChannelPricingInterface> $channelPricingFactory
     * @param RepositoryInterface<ChannelInterface> $channelRepository
     * @param RepositoryInterface<ProductInterface> $productRepository
     * @param FactoryInterface<TokenTariffInterface> $tariffFactory
     * @param RepositoryInterface<TokenTariffInterface> $tariffRepository
     */
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly FactoryInterface $productFactory,
        private readonly FactoryInterface $productVariantFactory,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly RepositoryInterface $channelRepository,
        private readonly RepositoryInterface $productRepository,
        private readonly FactoryInterface $tariffFactory,
        private readonly RepositoryInterface $tariffRepository,
    ) {
    }

    public function getName(): string
    {
        return 'guiziweb_token';
    }

    /**
     * @param array{
     *     packs: array<int, array{name: string, tokens: int, price: int}>,
     *     tariffs: array<int, array{code: string, name: string, cost: int}>
     * } $options
     */
    public function load(array $options): void
    {
        /** @var array<int, ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

        foreach ($options['packs'] as $pack) {
            $this->createProduct($pack['name'], $pack['price'], $channels, tokenAmount: $pack['tokens']);
        }

        foreach ($options['tariffs'] as $tariff) {
            $this->createTariff($tariff['code'], $tariff['name'], $tariff['cost']);
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
                ->arrayNode('tariffs')
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

    private function createTariff(string $code, string $name, int $cost): void
    {
        if (null !== $this->tariffRepository->findOneBy(['code' => $code])) {
            return;
        }

        $tariff = $this->tariffFactory->createNew();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setCost($cost);
        $tariff->setEnabled(true);

        $this->objectManager->persist($tariff);
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

        $product = $this->productFactory->createNew();
        $product->setCode($code);
        $product->setEnabled(true);
        $product->setCurrentLocale('en_US');
        $product->setFallbackLocale('en_US');
        $product->setName($name);
        $product->setSlug(StringInflector::nameToSlug($name));

        foreach ($channels as $channel) {
            $product->addChannel($channel);
        }

        $variant = $this->productVariantFactory->createNew();
        Assert::isInstanceOf($variant, TokenPackInterface::class, 'Apply TokenPackTrait to your ProductVariant entity.');

        $variant->setCode($code . '_VARIANT');
        $variant->setCurrentLocale('en_US');
        $variant->setFallbackLocale('en_US');
        $variant->setName($name);
        $variant->setShippingRequired(false);
        $variant->setTracked(false);
        $variant->setTokenAmount($tokenAmount);

        foreach ($channels as $channel) {
            $channelPricing = $this->channelPricingFactory->createNew();
            $channelPricing->setChannelCode($channel->getCode());
            $channelPricing->setPrice($price);
            $variant->addChannelPricing($channelPricing);
        }

        $product->addVariant($variant);
        $this->objectManager->persist($product);
    }
}
