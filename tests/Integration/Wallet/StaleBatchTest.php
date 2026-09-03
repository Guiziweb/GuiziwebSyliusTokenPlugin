<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class StaleBatchTest extends KernelTestCase
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

    public function testADebitReadsTheBatchAsItStandsInTheDatabase(): void
    {
        $wallet = $this->wallet();

        $batch = $this->operator->credit($wallet, new TokenCredit(100, uniqid('credit-', true)));
        self::assertInstanceOf(TokenBatchInterface::class, $batch);

        $this->entityManager->getConnection()->executeStatement(
            'UPDATE guiziweb_sylius_token_batch SET remaining_amount = 30 WHERE id = :id',
            ['id' => $batch->getId()],
        );

        self::assertSame(100, $batch->getRemainingAmount(), 'The in-memory batch is deliberately stale.');

        $this->operator->debit($wallet, new TokenDebit(10, uniqid('debit-', true)));

        self::assertSame(
            20,
            $this->currentRemaining($batch),
            'The debit must apply to what the batch really holds, not to the stale value held in memory.',
        );
    }

    public function testExpirySettlesWhatTheBatchReallyHolds(): void
    {
        $wallet = $this->wallet();

        $batch = $this->operator->credit($wallet, new TokenCredit(
            100,
            uniqid('credit-', true),
            expiresAt: new \DateTimeImmutable('-1 day'),
        ));
        self::assertInstanceOf(TokenBatchInterface::class, $batch);

        $this->entityManager->getConnection()->executeStatement(
            'UPDATE guiziweb_sylius_token_batch SET remaining_amount = 30 WHERE id = :id',
            ['id' => $batch->getId()],
        );

        $this->operator->credit($wallet, new TokenCredit(50, uniqid('credit-', true)));

        $expired = $this->entityManager->getConnection()->fetchOne(
            'SELECT COALESCE(SUM(amount), 0) FROM guiziweb_sylius_token_transaction WHERE wallet_id = :wallet AND type = :type',
            ['wallet' => $wallet->getId(), 'type' => 'expiration'],
        );

        self::assertSame(-30, (int) $expired, 'Expiration must record what the batch really holds.');
        self::assertSame(0, $this->currentRemaining($batch));
    }

    private function currentRemaining(TokenBatchInterface $batch): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT remaining_amount FROM guiziweb_sylius_token_batch WHERE id = :id',
            ['id' => $batch->getId()],
        );
    }

    private function wallet(): \Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::getContainer()->get('sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('stale-', true) . '@example.com');
        $customer->setFirstName('Stale');
        $customer->setLastName('Batch');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $wallet = $this->provider->provideForCustomer($customer);
        $this->entityManager->flush();

        return $wallet;
    }
}
