<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Abstraction\StateMachine\StateMachineInterface;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\ExampleFactoryInterface;
use Sylius\Component\Core\Formatter\StringInflector;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Webmozart\Assert\Assert;

final readonly class TokenContext implements Context
{
    /**
     * @param ExampleFactoryInterface<PaymentMethodInterface> $paymentMethodExampleFactory
     * @param RepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     */
    public function __construct(
        private EntityManagerInterface $entityManager,
        private WalletProviderInterface $walletProvider,
        private WalletOperatorInterface $walletOperator,
        private SharedStorageInterface $sharedStorage,
        private ExampleFactoryInterface $paymentMethodExampleFactory,
        private RepositoryInterface $paymentMethodRepository,
        private StateMachineInterface $stateMachine,
    ) {
    }

    /**
     * @Given the store has a payment method :name with a code :code and token_wallet gateway
     */
    public function theStoreHasATokenWalletPaymentMethod(string $name, string $code): void
    {
        $channel = $this->sharedStorage->has('channel') ? $this->sharedStorage->get('channel') : null;

        $paymentMethod = $this->paymentMethodExampleFactory->create([
            'name' => ucfirst($name),
            'code' => $code,
            'gatewayName' => StringInflector::nameToLowercaseCode($name),
            'gatewayFactory' => 'token_wallet',
            'usePayum' => false,
            'enabled' => true,
            'channels' => $channel instanceof ChannelInterface ? [$channel] : [],
        ]);

        $this->paymentMethodRepository->add($paymentMethod);
        $this->sharedStorage->set('payment_method', $paymentMethod);
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
     * @Given /^(this product) costs "([^"]+)" tokens$/
     * @Given /^the (product "[^"]+") costs "([^"]+)" tokens$/
     */
    public function theProductCostsTokens(ProductInterface $product, string $amount): void
    {
        $variant = $product->getVariants()->first();
        Assert::isInstanceOf($variant, TokenPackInterface::class);

        $variant->setTokenPrice((int) $amount);
        $this->entityManager->flush();
    }

    /**
     * @Given /^(this customer) has "([^"]+)" tokens$/
     * @Given /^(the customer "[^"]+") has "([^"]+)" tokens$/
     * @Given /^I have "([^"]+)" tokens$/
     */
    public function theCustomerHasTokens(string $amount, ?CustomerInterface $customer = null): void
    {
        $customer ??= $this->getCurrentCustomer();

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
