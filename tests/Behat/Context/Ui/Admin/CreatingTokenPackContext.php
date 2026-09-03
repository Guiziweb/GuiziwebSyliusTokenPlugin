<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenPackInterface;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Pack\CreatePageInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Pack\UpdatePageInterface;
use Webmozart\Assert\Assert;

final readonly class CreatingTokenPackContext implements Context
{
    /** @param RepositoryInterface<ProductVariantInterface> $variantRepository */
    public function __construct(
        private CreatePageInterface $createPage,
        private UpdatePageInterface $updatePage,
        private IndexPageInterface $indexPage,
        private RepositoryInterface $variantRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @When I want to edit the token pack :code */
    public function iWantToEditTheTokenPack(string $code): void
    {
        $this->entityManager->clear();

        $variant = $this->variantRepository->findOneBy(['code' => $code]);
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        $product = $variant->getProduct();
        Assert::notNull($product);

        $this->updatePage->open(['id' => $product->getId()]);
    }

    /** @When I change the granted tokens to :amount */
    public function iChangeTheGrantedTokensTo(int $amount): void
    {
        $this->updatePage->specifyTokenAmount($amount);
    }

    /** @When I set its validity to :months months */
    public function iSetItsValidityTo(int $months): void
    {
        $this->updatePage->specifyValidityMonths($months);
    }

    /** @When I save my changes */
    public function iSaveMyChanges(): void
    {
        $this->updatePage->saveChanges();
    }

    /** @Then the token pack :code should grant :amount tokens valid for :months months */
    public function theTokenPackShouldGrantTokensValidFor(string $code, int $amount, int $months): void
    {
        $this->entityManager->clear();

        $variant = $this->variantRepository->findOneBy(['code' => $code]);
        Assert::isInstanceOf($variant, TokenPackInterface::class);

        Assert::same($variant->getTokenAmount(), $amount);
        Assert::same($variant->getTokenValidityMonths(), $months);
    }

    /** @When I want to create a new token pack */
    public function iWantToCreateANewTokenPack(): void
    {
        $this->createPage->open();
    }

    /** @When I set its code to :code */
    public function iSpecifyItsCodeAs(string $code): void
    {
        $this->createPage->specifyCode($code);
    }

    /** @When I call it :name */
    public function iNameIt(string $name): void
    {
        $this->createPage->specifyName($name);
    }

    /** @When it grants :amount tokens */
    public function itGrantsTokens(int $amount): void
    {
        $this->createPage->specifyTokenAmount($amount);
    }

    /** @When it costs :price in the :channel channel */
    public function itCostsInTheChannel(string $price, ChannelInterface $channel): void
    {
        $channelCode = $channel->getCode();
        Assert::string($channelCode);

        $this->createPage->enableChannel($channelCode);
        $this->createPage->specifyPrice($channelCode, trim($price, '$'));
    }

    /** @When I create it */
    public function iCreateIt(): void
    {
        $this->createPage->create();
    }

    /** @Then the shipping fields should not be available */
    public function theShippingFieldsShouldNotBeAvailable(): void
    {
        Assert::false($this->createPage->hasShippingFields(), 'The shipping fields should not be part of a token pack form.');
    }

    /** @Then the token pack :name should appear in the list */
    public function theTokenPackShouldAppearInTheList(string $name): void
    {
        $this->indexPage->open();

        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    /** @Then the token pack :code should never require shipping */
    public function theTokenPackShouldNeverRequireShipping(string $code): void
    {
        $this->entityManager->clear();

        $variant = $this->variantRepository->findOneBy(['code' => $code]);
        Assert::isInstanceOf($variant, TokenPackInterface::class);
        Assert::isInstanceOf($variant, ProductVariantInterface::class);

        Assert::false($variant->isShippingRequired(), 'A token pack must never require shipping.');
        Assert::false($variant->isTracked(), 'A token pack must not be tracked in inventory.');
    }
}
