<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Customer\ShowPageInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Wallet\HistoryPageInterface;
use Webmozart\Assert\Assert;

final readonly class BrowsingTokenHistoryContext implements Context
{
    public function __construct(
        private HistoryPageInterface $historyPage,
        private ShowPageInterface $customerShowPage,
        private WalletProviderInterface $walletProvider,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /** @When I browse the token history of :customer */
    public function iBrowseTheTokenHistoryOf(CustomerInterface $customer): void
    {
        $this->historyPage->open(['id' => $this->walletProvider->provideForCustomer($customer)->getId()]);
    }

    /** @When I view the customer :customer */
    public function iViewTheCustomer(CustomerInterface $customer): void
    {
        $this->customerShowPage->open(['id' => $customer->getId()]);
    }

    /** @Then the wallet history should list :count movements */
    public function iShouldSeeTokenMovements(int $count): void
    {
        Assert::same($this->historyPage->countMovements(), $count);
    }

    /** @Then the history should mention a :type movement */
    public function theHistoryShouldMentionAMovement(string $type): void
    {
        Assert::true($this->historyPage->hasMovementOfType($type), sprintf('No "%s" movement in the history.', $type));
    }

    /** @Then his token summary should read :balance available, :credited credited and :spent spent */
    public function hisTokenSummaryShouldRead(int $balance, int $credited, int $spent): void
    {
        Assert::same($this->customerShowPage->getTokenBalance(), $balance);
        Assert::same($this->customerShowPage->getTotalCredited(), $credited);
        Assert::same($this->customerShowPage->getTotalSpent(), $spent);
    }

    /** @Then the token history of :customer should be reachable */
    public function theTokenHistoryShouldBeReachable(CustomerInterface $customer): void
    {
        $this->entityManager->clear();

        $this->historyPage->open(['id' => $this->walletProvider->provideForCustomer($customer)->getId()]);

        Assert::true($this->historyPage->isOpen());
    }
}
