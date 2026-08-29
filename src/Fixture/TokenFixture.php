<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Fixture;

use Doctrine\Persistence\ObjectManager;
use Guiziweb\SyliusTokenPlugin\Form\Type\TokenWalletGatewayConfigurationType;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Sylius\Bundle\FixturesBundle\Fixture\AbstractFixture;
use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
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
     * @param FactoryInterface<PaymentMethodInterface> $paymentMethodFactory
     * @param FactoryInterface<GatewayConfigInterface> $gatewayConfigFactory
     * @param RepositoryInterface<ChannelInterface> $channelRepository
     * @param RepositoryInterface<ProductInterface> $productRepository
     * @param RepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     */
    public function __construct(
        private readonly ObjectManager $objectManager,
        private readonly FactoryInterface $productFactory,
        private readonly FactoryInterface $productVariantFactory,
        private readonly FactoryInterface $channelPricingFactory,
        private readonly FactoryInterface $paymentMethodFactory,
        private readonly FactoryInterface $gatewayConfigFactory,
        private readonly RepositoryInterface $channelRepository,
        private readonly RepositoryInterface $productRepository,
        private readonly RepositoryInterface $paymentMethodRepository,
    ) {
    }

    public function getName(): string
    {
        return 'guiziweb_token';
    }

    /**
     * @param array{
     *     payment_method_name: string,
     *     packs: array<int, array{name: string, tokens: int, price: int}>,
     *     consumables: array<int, array{name: string, tokens: int}>
     * } $options
     */
    public function load(array $options): void
    {
        /** @var array<int, ChannelInterface> $channels */
        $channels = $this->channelRepository->findAll();

        $this->createPaymentMethod($options['payment_method_name'], $channels);

        foreach ($options['packs'] as $pack) {
            $this->createProduct($pack['name'], $pack['price'], $channels, tokenAmount: $pack['tokens']);
        }

        foreach ($options['consumables'] as $consumable) {
            $this->createProduct($consumable['name'], 0, $channels, tokenPrice: $consumable['tokens']);
        }

        $this->objectManager->flush();
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->scalarNode('payment_method_name')->defaultValue('Tokens')->end()
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
                ->arrayNode('consumables')
                    ->defaultValue([
                        ['name' => 'CV generation', 'tokens' => 5],
                        ['name' => 'HD image generation', 'tokens' => 20],
                    ])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('name')->cannotBeEmpty()->end()
                            ->integerNode('tokens')->min(1)->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /** @param array<int, ChannelInterface> $channels */
    private function createProduct(
        string $name,
        int $price,
        array $channels,
        ?int $tokenAmount = null,
        ?int $tokenPrice = null,
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
        $variant->setTokenPrice($tokenPrice);

        foreach ($channels as $channel) {
            $channelPricing = $this->channelPricingFactory->createNew();
            $channelPricing->setChannelCode($channel->getCode());
            $channelPricing->setPrice($price);
            $variant->addChannelPricing($channelPricing);
        }

        $product->addVariant($variant);
        $this->objectManager->persist($product);
    }

    /** @param array<int, ChannelInterface> $channels */
    private function createPaymentMethod(string $name, array $channels): void
    {
        $code = TokenWalletGatewayConfigurationType::GATEWAY_FACTORY;

        if (null !== $this->paymentMethodRepository->findOneBy(['code' => $code])) {
            return;
        }

        $gatewayConfig = $this->gatewayConfigFactory->createNew();
        $gatewayConfig->setFactoryName(TokenWalletGatewayConfigurationType::GATEWAY_FACTORY);
        $gatewayConfig->setGatewayName(TokenWalletGatewayConfigurationType::GATEWAY_FACTORY);
        $gatewayConfig->setUsePayum(false);

        $paymentMethod = $this->paymentMethodFactory->createNew();
        $paymentMethod->setCode($code);
        $paymentMethod->setGatewayConfig($gatewayConfig);
        $paymentMethod->setEnabled(true);
        $paymentMethod->setCurrentLocale('en_US');
        $paymentMethod->setFallbackLocale('en_US');
        $paymentMethod->setName($name);

        foreach ($channels as $channel) {
            $paymentMethod->addChannel($channel);
        }

        $this->objectManager->persist($paymentMethod);
    }
}
