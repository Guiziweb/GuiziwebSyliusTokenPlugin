<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatch;
use Guiziweb\SyliusTokenPlugin\Entity\TokenOperation\TokenOperation;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransaction;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Factory\TokenBatchFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenOperationFactory;
use Guiziweb\SyliusTokenPlugin\Factory\TokenTransactionFactory;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Repository\TokenBatchRepository;
use Guiziweb\SyliusTokenPlugin\Repository\TokenOperationRepository;
use Guiziweb\SyliusTokenPlugin\Wallet\BatchAllocator;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperator;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Clock\MockClock;
use Tests\Guiziweb\SyliusTokenPlugin\Integration\ContainerTrait;

final class ConcurrentCreditTest extends KernelTestCase
{
    use ContainerTrait;

    private EntityManagerInterface $entityManager;

    private EntityManagerInterface $otherEntityManager;

    private WalletOperatorInterface $operator;

    private WalletOperatorInterface $otherOperator;

    private ?int $walletId = null;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::service(EntityManagerInterface::class, 'doctrine.orm.entity_manager');
        $this->operator = self::service(WalletOperatorInterface::class);

        /** @var array<string, mixed> $params */
        $params = array_diff_key(
            $this->entityManager->getConnection()->getParams(),
            ['pdo' => null, 'driverOptions' => null],
        );

        /** @phpstan-ignore argument.type */
        $connection = DriverManager::getConnection($params);
        $connection->executeStatement('SET SESSION innodb_lock_wait_timeout = 5');

        $this->otherEntityManager = new EntityManager($connection, $this->entityManager->getConfiguration());

        $this->otherOperator = new WalletOperator(
            $this->otherEntityManager,
            new TokenBatchRepository($this->otherEntityManager, $this->otherEntityManager->getClassMetadata(TokenBatch::class)),
            new TokenOperationRepository($this->otherEntityManager, $this->otherEntityManager->getClassMetadata(TokenOperation::class)),
            new BatchAllocator(),
            new MockClock(new \DateTimeImmutable('2026-09-03 12:00:00')),
            new TokenBatchFactory(TokenBatch::class),
            new TokenTransactionFactory(TokenTransaction::class),
            new TokenOperationFactory(TokenOperation::class),
        );
    }

    protected function tearDown(): void
    {
        if (null !== $this->walletId) {
            $connection = $this->entityManager->getConnection();
            $connection->executeStatement('DELETE FROM guiziweb_sylius_token_transaction WHERE wallet_id = ?', [$this->walletId]);
            $connection->executeStatement('DELETE FROM guiziweb_sylius_token_operation WHERE wallet_id = ?', [$this->walletId]);
            $connection->executeStatement('DELETE FROM guiziweb_sylius_token_batch WHERE wallet_id = ?', [$this->walletId]);
            $connection->executeStatement('DELETE FROM guiziweb_sylius_token_wallet WHERE id = ?', [$this->walletId]);
        }

        $this->otherEntityManager->getConnection()->close();

        parent::tearDown();
    }

    public function testACreditReplayedFromAStaleSnapshotIsIgnored(): void
    {
        $wallet = $this->wallet();
        $key = uniqid('order-item-', true);

        $this->otherEntityManager->beginTransaction();
        $this->otherEntityManager->getConnection()->fetchOne('SELECT COUNT(*) FROM guiziweb_sylius_token_operation');

        $this->operator->credit($wallet, new TokenCredit(500, $key));

        $otherWallet = $this->otherEntityManager->find($wallet::class, $this->walletId);
        self::assertInstanceOf(TokenWalletInterface::class, $otherWallet);

        $this->otherOperator->credit($otherWallet, new TokenCredit(500, $key));
        $this->otherEntityManager->commit();

        self::assertSame(1, $this->countBatches(), 'The second webhook must not create a second batch.');
        self::assertSame(500, $this->operator->getBalance($wallet), 'A concurrent replay must never credit twice.');
    }

    private function countBatches(): int
    {
        /** @var int|string $count */
        $count = $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM guiziweb_sylius_token_batch WHERE wallet_id = ?',
            [$this->walletId],
        );

        return (int) $count;
    }

    private function wallet(): TokenWalletInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::service(FactoryInterface::class, 'sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('concurrent-', true) . '@example.com');
        $customer->setFirstName('Concurrent');
        $customer->setLastName('Credit');
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $wallet = self::service(WalletProviderInterface::class)->provideForCustomer($customer);
        $this->entityManager->flush();
        $this->walletId = $wallet->getId();

        return $wallet;
    }
}
