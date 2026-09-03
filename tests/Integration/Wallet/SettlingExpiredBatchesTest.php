<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class SettlingExpiredBatchesTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;

    private WalletOperatorInterface $operator;

    private WalletProviderInterface $provider;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $this->operator = self::getContainer()->get(WalletOperatorInterface::class);
        $this->provider = self::getContainer()->get(WalletProviderInterface::class);
        $this->entityManager->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->entityManager->rollback();
        parent::tearDown();
    }

    public function testAnExpiredBatchIsSettledOnTheNextOperation(): void
    {
        $wallet = $this->provider->provideForCustomer($this->customer());
        $this->entityManager->flush();

        $expiring = $this->operator->credit($wallet, new TokenCredit(
            50,
            uniqid('credit-', true),
            expiresAt: new \DateTimeImmutable('-1 day'),
        ));
        self::assertInstanceOf(TokenBatchInterface::class, $expiring);

        self::assertSame(0, $this->operator->getBalance($wallet), 'An expired batch is never spendable.');

        $this->operator->credit($wallet, new TokenCredit(20, uniqid('credit-', true)));

        self::assertSame(0, $expiring->getRemainingAmount(), 'The expired batch must be emptied.');
        self::assertSame(20, $this->operator->getBalance($wallet));
    }

    public function testTheSettlementIsRecordedInTheLedger(): void
    {
        $wallet = $this->provider->provideForCustomer($this->customer());
        $this->entityManager->flush();

        $this->operator->credit($wallet, new TokenCredit(
            50,
            uniqid('credit-', true),
            expiresAt: new \DateTimeImmutable('-1 day'),
        ));

        $this->operator->credit($wallet, new TokenCredit(20, uniqid('credit-', true)));

        $expirations = $this->entityManager->getConnection()->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) FROM guiziweb_sylius_token_transaction WHERE wallet_id = :wallet AND type = :type',
            ['wallet' => $wallet->getId(), 'type' => 'expiration'],
        );

        self::assertSame(-50, (int) $expirations, 'The customer must see where the tokens went.');
    }

    public function testASettledBatchIsNotSettledTwice(): void
    {
        $wallet = $this->provider->provideForCustomer($this->customer());
        $this->entityManager->flush();

        $this->operator->credit($wallet, new TokenCredit(
            50,
            uniqid('credit-', true),
            expiresAt: new \DateTimeImmutable('-1 day'),
        ));

        $this->operator->credit($wallet, new TokenCredit(20, uniqid('credit-', true)));
        $this->operator->credit($wallet, new TokenCredit(30, uniqid('credit-', true)));

        $expirationCount = $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM guiziweb_sylius_token_transaction WHERE wallet_id = :wallet AND type = :type',
            ['wallet' => $wallet->getId(), 'type' => 'expiration'],
        );

        self::assertSame(1, (int) $expirationCount);
        self::assertSame(50, $this->operator->getBalance($wallet));
    }

    private function customer(): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::getContainer()->get('sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('expired-batch-', true) . '@example.com');
        $customer->setFirstName('Expired');
        $customer->setLastName('Batch');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $customer;
    }
}