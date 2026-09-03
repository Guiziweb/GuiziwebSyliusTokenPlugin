<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatch;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\InsufficientTokenBalanceException;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Guiziweb\SyliusTokenPlugin\Integration\ContainerTrait;

final class WalletOperatorTest extends KernelTestCase
{
    use ContainerTrait;

    private EntityManagerInterface $entityManager;

    private WalletOperatorInterface $operator;

    private WalletProviderInterface $provider;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::service(EntityManagerInterface::class, 'doctrine.orm.entity_manager');
        $this->operator = self::service(WalletOperatorInterface::class);
        $this->provider = self::service(WalletProviderInterface::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testAnInsufficientBalanceLeavesTheEntityManagerUsable(): void
    {
        $wallet = $this->emptyWallet();
        $this->operator->credit($wallet, new TokenCredit(10, uniqid('credit-', true)));

        try {
            $this->operator->debit($wallet, new TokenDebit(11, uniqid('debit-', true)));
            self::fail('Expected an InsufficientTokenBalanceException.');
        } catch (InsufficientTokenBalanceException) {
        }

        self::assertTrue($this->entityManager->isOpen());
        self::assertSame(10, $this->operator->getBalance($wallet));

        $this->operator->debit($wallet, new TokenDebit(4, uniqid('debit-', true)));

        self::assertSame(6, $this->operator->getBalance($wallet));
    }

    public function testItCreditsAWalletThatWasNeverFlushed(): void
    {
        $customer = $this->customerWithoutWallet();
        $wallet = $this->provider->provideForCustomer($customer);

        self::assertNull($wallet->getId());

        $this->operator->credit($wallet, new TokenCredit(50, uniqid('credit-', true)));

        self::assertNotNull($wallet->getId());
        self::assertSame(50, $this->operator->getBalance($wallet));
    }

    public function testItSpendsTheBatchesExpiringFirst(): void
    {
        $wallet = $this->emptyWallet();
        $soonest = new \DateTimeImmutable('+1 year');
        $latest = new \DateTimeImmutable('+2 years');

        $this->operator->credit($wallet, new TokenCredit(10, uniqid('c-', true), expiresAt: $latest));
        $this->operator->credit($wallet, new TokenCredit(10, uniqid('c-', true), expiresAt: $soonest));
        $this->operator->credit($wallet, new TokenCredit(10, uniqid('c-', true)));

        $this->operator->debit($wallet, new TokenDebit(15, uniqid('d-', true)));

        $remaining = [];
        foreach ($this->entityManager->getRepository(TokenBatch::class)->findBy(['wallet' => $wallet]) as $batch) {
            $remaining[$batch->getExpiresAt()?->format('Y-m-d') ?? 'never'] = $batch->getRemainingAmount();
        }

        self::assertSame(0, $remaining[$soonest->format('Y-m-d')]);
        self::assertSame(5, $remaining[$latest->format('Y-m-d')]);
        self::assertSame(10, $remaining['never']);
    }

    private function emptyWallet(): TokenWalletInterface
    {
        $wallet = $this->provider->provideForCustomer($this->customerWithoutWallet());
        $this->entityManager->flush();

        return $wallet;
    }

    private function customerWithoutWallet(): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::service(FactoryInterface::class, 'sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('wallet-test-', true) . '@example.com');
        $customer->setFirstName('Wallet');
        $customer->setLastName('Test');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $customer;
    }
}
