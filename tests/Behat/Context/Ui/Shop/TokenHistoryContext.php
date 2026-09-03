<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Shop\Account\TokenHistoryPageInterface;
use Webmozart\Assert\Assert;

final readonly class TokenHistoryContext implements Context
{
    public function __construct(
        private TokenHistoryPageInterface $tokenHistoryPage,
    ) {
    }

    /** @When I browse my token history */
    public function iBrowseMyTokenHistory(): void
    {
        $this->tokenHistoryPage->open();
    }

    /** @Then my displayed token balance should be :balance */
    public function myDisplayedTokenBalanceShouldBe(int $balance): void
    {
        Assert::same($this->tokenHistoryPage->getBalance(), $balance);
    }

    /** @Then I should see :count token movements */
    public function iShouldSeeTokenMovements(int $count): void
    {
        Assert::same($this->tokenHistoryPage->countMovements(), $count);
    }

    /** @Then I should see a token movement labelled :type */
    public function iShouldSeeATokenMovementLabelled(string $type): void
    {
        Assert::true(
            $this->tokenHistoryPage->hasMovementOfType($type),
            sprintf('No "%s" movement found in the token history.', $type),
        );
    }
}
