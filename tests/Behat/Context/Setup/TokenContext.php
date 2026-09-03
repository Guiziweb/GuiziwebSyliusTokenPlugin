<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenPackInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Resource\Factory\FactoryInterface;
use Webmozart\Assert\Assert;

final readonly class TokenContext implements Context
{
    /** @param FactoryInterface<TokenPriceInterface> $priceFactory */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WalletProviderInterface $walletProvider,
        private WalletOperatorInterface $walletOperator,
        private SharedStorageInterface $sharedStorage,
        private StateMachineInterface $stateMachine,
        private FactoryInterface $priceFactory,
    ) {
    }

    /**
     * @Given the order has been paid
     * @When the order is paid
     */
    public function theOrderIsPaid(): void
    {
        $order = $this->sharedStorage->get('order');
        Assert::isInstanceOf($order, OrderInterface::class);

        $payment = $order->getLastPayment(PaymentInterface::STATE_NEW)
            ?? $order->getLastPayment(PaymentInterface::STATE_PROCESSING)
            ?? $order->getLastPayment();
        Assert::isInstanceOf($payment, PaymentInterface::class);

        if (PaymentInterface::STATE_COMPLETED !== $payment->getState()) {
            $this->stateMachine->apply($payment, PaymentTransitions::GRAPH, PaymentTransitions::TRANSITION_COMPLETE);
        }

        $this->entityManager->flush();
    }

    /**
     * @Given /^(this product) grants "([^"]+)" tokens$/
     * @Given /^the (product "[^"]+") grants "([^"]+)" tokens$/
     */
    public function theProductGrantsTokens(ProductInterface $product, string $amount): void
    {
        $variant = $product->getVariants()->first();
        Assert::isInstanceOf($variant, TokenPackInterface::class);

        $variant->setTokenAmount((int) $amount);
        $this->entityManager->flush();
    }

    /**
     * @Given /^(this customer) has "([^"]+)" tokens$/
     * @Given /^the (customer "[^"]+") has "([^"]+)" tokens$/
     */
    public function theCustomerHasTokens(CustomerInterface $customer, string $amount): void
    {
        $this->creditCustomer($customer, $amount);
    }

    /** @Given the store has a token price :name with code :code costing :cost tokens */
    public function theStoreHasATokenPrice(string $name, string $code, int $cost): void
    {
        $price = $this->priceFactory->createNew();
        Assert::isInstanceOf($price, TokenPriceInterface::class);

        $price->setCode($code);
        $price->setName($name);
        $price->setCost($cost);

        $this->entityManager->persist($price);
        $this->entityManager->flush();
    }

    /** @Given /^I have "([^"]+)" tokens$/ */
    public function iHaveTokens(string $amount): void
    {
        $this->creditCustomer($this->getCurrentCustomer(), $amount);
    }

    private function creditCustomer(CustomerInterface $customer, string $amount): void
    {
        $this->walletOperator->credit(
            $this->walletProvider->provideForCustomer($customer),
            new TokenCredit((int) $amount, 'behat-' . uniqid('', true)),
        );

        $this->entityManager->flush();
    }

    private function getCurrentCustomer(): CustomerInterface
    {
        $customer = $this->entityManager->getRepository(CustomerInterface::class)->findOneBy([], ['id' => 'desc']);
        Assert::isInstanceOf($customer, CustomerInterface::class);

        return $customer;
    }
}
