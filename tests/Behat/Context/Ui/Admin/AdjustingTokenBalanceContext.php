<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumerInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Wallet\AdjustPageInterface;
use Webmozart\Assert\Assert;

final readonly class AdjustingTokenBalanceContext implements Context
{
    public function __construct(
        private AdjustPageInterface $adjustPage,
        private EntityManagerInterface $entityManager,
        private TokenConsumerInterface $tokenConsumer,
        private WalletProviderInterface $walletProvider,
        private SharedStorageInterface $sharedStorage,
        private NotificationCheckerInterface $notificationChecker,
    ) {
    }

    /**
     * @When I want to adjust the token balance of :customer
     * @When I want to adjust my customer's token balance
     */
    public function iWantToAdjustTheTokenBalanceOf(?CustomerInterface $customer = null): void
    {
        $customer ??= $this->sharedStorage->get('customer');
        Assert::isInstanceOf($customer, CustomerInterface::class);

        $wallet = $this->walletProvider->provideForCustomer($customer);

        $this->adjustPage->open(['id' => $wallet->getId()]);
    }

    /** @When I add :amount tokens with the reason :reason */
    public function iAddTokensWithTheReason(int $amount, string $reason): void
    {
        $this->adjustPage->chooseCredit();
        $this->adjustPage->specifyAmount($amount);
        $this->adjustPage->specifyReason($reason);
        $this->adjustPage->submit();
    }

    /** @When I remove :amount tokens with the reason :reason */
    public function iRemoveTokensWithTheReason(int $amount, string $reason): void
    {
        $this->adjustPage->chooseDebit();
        $this->adjustPage->specifyAmount($amount);
        $this->adjustPage->specifyReason($reason);
        $this->adjustPage->submit();
    }

    /** @Then I should be notified that the tokens have been added */
    public function iShouldBeNotifiedThatTheTokensHaveBeenAdded(): void
    {
        $this->notificationChecker->checkNotification(
            'Tokens have been added to the wallet.',
            NotificationType::success(),
        );
    }

    /** @Then I should be notified that the tokens have been removed */
    public function iShouldBeNotifiedThatTheTokensHaveBeenRemoved(): void
    {
        $this->notificationChecker->checkNotification(
            'Tokens have been removed from the wallet.',
            NotificationType::success(),
        );
    }

    /** @Then I should be notified that the wallet does not hold enough tokens */
    public function iShouldBeNotifiedThatTheWalletDoesNotHoldEnoughTokens(): void
    {
        $this->notificationChecker->checkNotification(
            'This wallet does not hold enough tokens.',
            NotificationType::error(),
        );
    }

    /** @Then /^the (customer "[^"]+") should have "([^"]+)" tokens$/ */
    public function theCustomerShouldHaveTokens(CustomerInterface $customer, string $amount): void
    {
        $this->entityManager->clear();

        Assert::same($this->tokenConsumer->getBalance($customer), (int) $amount);
    }
}
