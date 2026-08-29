<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Ui\Shop;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumerInterface;
use Sylius\Behat\Page\Shop\Checkout\SelectPaymentPageInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Customer\Context\CustomerContextInterface;
use Webmozart\Assert\Assert;

final readonly class TokenBalanceContext implements Context
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CustomerContextInterface $customerContext,
        private TokenConsumerInterface $tokenConsumer,
        private SelectPaymentPageInterface $selectPaymentPage,
    ) {
    }

    /**
     * @When I try to proceed through checkout process
     */
    public function iTryToProceedThroughCheckoutProcess(): void
    {
        $this->selectPaymentPage->tryToOpen();
    }

    /**
     * @Then I should not be able to complete the order
     */
    public function iShouldNotBeAbleToCompleteTheOrder(): void
    {
        Assert::false(
            $this->selectPaymentPage->isOpen() && $this->selectPaymentPage->hasPaymentMethod('Tokens'),
            'The token payment method should not be offered.',
        );
    }

    /**
     * @Then /^I should have "([^"]+)" tokens$/
     * @Then /^my token balance should be "([^"]+)"$/
     */
    public function myTokenBalanceShouldBe(string $amount): void
    {
        $this->entityManager->clear();

        Assert::same($this->tokenConsumer->getBalance($this->getCustomer()), (int) $amount);
    }

    private function getCustomer(): CustomerInterface
    {
        $customer = $this->customerContext->getCustomer();

        if (!$customer instanceof CustomerInterface) {
            $customer = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy([], ['id' => 'desc']);
        }

        Assert::isInstanceOf($customer, CustomerInterface::class);

        return $customer;
    }
}
