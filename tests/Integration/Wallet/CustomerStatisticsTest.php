<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Twig\CustomerWalletRuntime;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Guiziweb\SyliusTokenPlugin\Integration\ContainerTrait;

final class CustomerStatisticsTest extends KernelTestCase
{
    use ContainerTrait;

    private EntityManagerInterface $entityManager;

    private CustomerWalletRuntime $runtime;

    private WalletOperatorInterface $operator;

    private WalletProviderInterface $provider;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::service(EntityManagerInterface::class, 'doctrine.orm.entity_manager');
        $this->runtime = self::service(CustomerWalletRuntime::class, 'guiziweb_sylius_token.twig.runtime.customer_wallet');
        $this->operator = self::service(WalletOperatorInterface::class);
        $this->provider = self::service(WalletProviderInterface::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testACustomerWithoutAnyMovementReadsZero(): void
    {
        self::assertSame(['credited' => 0, 'spent' => 0], $this->runtime->getStatistics($this->customer()));
    }

    public function testTheStatisticsSplitCreditsFromDebits(): void
    {
        $customer = $this->customer();
        $wallet = $this->provider->provideForCustomer($customer);
        $this->entityManager->flush();

        $this->operator->credit($wallet, new TokenCredit(500, uniqid('credit-', true)));
        $this->operator->debit($wallet, new TokenDebit(30, uniqid('debit-', true)));

        self::assertSame(['credited' => 500, 'spent' => 30], $this->runtime->getStatistics($customer));
    }

    private function customer(): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::service(FactoryInterface::class, 'sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('statistics-', true) . '@example.com');
        $customer->setFirstName('Statistics');
        $customer->setLastName('Test');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $customer;
    }
}
