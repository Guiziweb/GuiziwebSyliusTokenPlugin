<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Page\Admin\Crud\IndexPageInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Price\CreatePageInterface;
use Webmozart\Assert\Assert;

final readonly class ManagingTokenPricesContext implements Context
{
    /** @param RepositoryInterface<TokenPriceInterface> $priceRepository */
    public function __construct(
        private IndexPageInterface $indexPage,
        private CreatePageInterface $createPage,
        private NotificationCheckerInterface $notificationChecker,
        private RepositoryInterface $priceRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @When I want to create a new token price */
    public function iWantToCreateANewTokenPrice(): void
    {
        $this->createPage->open();
    }

    /** @When I browse token prices */
    public function iBrowseTokenPrices(): void
    {
        $this->indexPage->open();
    }

    /** @When I delete the token price :name */
    public function iDeleteTheTokenPrice(string $name): void
    {
        $this->indexPage->open();
        $this->indexPage->deleteResourceOnPage(['name' => $name]);
    }

    /** @Then I should be notified that it has been successfully deleted */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyDeleted(): void
    {
        $this->notificationChecker->checkNotification('has been successfully deleted.', NotificationType::success());
    }

    /** @Then the token price :code should no longer exist */
    public function theTokenPriceShouldNoLongerExist(string $code): void
    {
        $this->entityManager->clear();

        Assert::null(
            $this->priceRepository->findOneBy(['code' => $code]),
            sprintf('The token price "%s" is still there.', $code),
        );
    }

    /** @When I specify its code as :code */
    public function iSpecifyItsCodeAs(string $code): void
    {
        $this->createPage->specifyCode($code);
    }

    /** @When I name it :name */
    public function iNameIt(string $name): void
    {
        $this->createPage->specifyName($name);
    }

    /** @When it costs :cost tokens */
    public function itCostsTokens(int $cost): void
    {
        $this->createPage->specifyCost($cost);
    }

    /** @When I add it */
    public function iAddIt(): void
    {
        $this->createPage->create();
    }

    /** @Then I should see :count token prices in the list */
    public function iShouldSeeTokenPricesInTheList(int $count): void
    {
        Assert::same($this->indexPage->countItems(), $count);
    }

    /** @Then the token price :name should appear in the list */
    public function theTokenPriceShouldAppearInTheList(string $name): void
    {
        $this->indexPage->open();

        Assert::true($this->indexPage->isSingleResourceOnPage(['name' => $name]));
    }

    /** @Then I should be notified that the code must be unique */
    public function iShouldBeNotifiedThatTheCodeMustBeUnique(): void
    {
        Assert::same($this->createPage->getValidationMessage('code'), 'Price code must be unique.');
    }

    /** @Then I should be notified that it has been successfully created */
    public function iShouldBeNotifiedThatItHasBeenSuccessfullyCreated(): void
    {
        $this->notificationChecker->checkNotification(
            'Price has been successfully created.',
            NotificationType::success(),
        );
    }
}
