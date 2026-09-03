<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\ORM\EntityManagerInterface;
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

final class IdempotencyTest extends KernelTestCase
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

    public function testAReplayedCreditIsIgnored(): void
    {
        $wallet = $this->wallet();
        $key = uniqid('order-item-', true);

        $this->operator->credit($wallet, new TokenCredit(500, $key));
        $this->operator->credit($wallet, new TokenCredit(500, $key));

        self::assertSame(500, $this->operator->getBalance($wallet), 'A replayed payment must never credit twice.');
        self::assertSame(1, $this->countBatches($wallet));
        self::assertSame(1, $this->countTransactions($wallet, 'credit'));
    }

    public function testAReplayedDebitIsIgnored(): void
    {
        $wallet = $this->wallet();
        $this->operator->credit($wallet, new TokenCredit(500, uniqid('credit-', true)));

        $key = uniqid('consume-', true);
        $this->operator->debit($wallet, new TokenDebit(200, $key));
        $this->operator->debit($wallet, new TokenDebit(200, $key));

        self::assertSame(300, $this->operator->getBalance($wallet), 'A replayed consumption must never debit twice.');
        self::assertSame(1, $this->countTransactions($wallet, 'debit'));
    }

    public function testARefusedDebitDoesNotBurnItsKey(): void
    {
        $wallet = $this->wallet();
        $key = uniqid('consume-', true);

        try {
            $this->operator->debit($wallet, new TokenDebit(200, $key));
            self::fail('The debit must be refused on an empty wallet.');
        } catch (InsufficientTokenBalanceException) {
        }

        $this->operator->credit($wallet, new TokenCredit(500, uniqid('credit-', true)));
        $this->operator->debit($wallet, new TokenDebit(200, $key));

        self::assertSame(300, $this->operator->getBalance($wallet), 'A key spent on a refused debit must stay usable.');
        self::assertSame(1, $this->countTransactions($wallet, 'debit'));
    }

    public function testTheSameKeyOnAnotherTypeIsNotAReplay(): void
    {
        $wallet = $this->wallet();
        $key = uniqid('order-', true);

        $this->operator->credit($wallet, new TokenCredit(500, $key));
        $this->operator->debit($wallet, new TokenDebit(200, $key));

        self::assertSame(300, $this->operator->getBalance($wallet), 'A debit must not be mistaken for a replayed credit.');
        self::assertSame(1, $this->countTransactions($wallet, 'credit'));
        self::assertSame(1, $this->countTransactions($wallet, 'debit'));
    }

    public function testTwoWalletsDoNotShareIdempotencyKeys(): void
    {
        $first = $this->wallet();
        $second = $this->wallet();
        $key = 'order-item-shared';

        $this->operator->credit($first, new TokenCredit(500, $key));
        $this->operator->credit($second, new TokenCredit(500, $key));

        self::assertSame(500, $this->operator->getBalance($first));
        self::assertSame(500, $this->operator->getBalance($second), 'Another customer must not be denied by a key already used elsewhere.');
    }

    private function countBatches(TokenWalletInterface $wallet): int
    {
        /** @var int|string $scalar */
        /** @var int|string $scalar */
        $scalar = $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM guiziweb_sylius_token_batch WHERE wallet_id = :wallet',
            ['wallet' => $wallet->getId()],
        );

        return (int) $scalar;
    }

    private function countTransactions(TokenWalletInterface $wallet, string $type): int
    {
        /** @var int|string $scalar */
        /** @var int|string $scalar */
        $scalar = $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM guiziweb_sylius_token_transaction WHERE wallet_id = :wallet AND type = :type',
            ['wallet' => $wallet->getId(), 'type' => $type],
        );

        return (int) $scalar;
    }

    private function wallet(): TokenWalletInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::service(FactoryInterface::class, 'sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('idempotency-', true) . '@example.com');
        $customer->setFirstName('Idempotency');
        $customer->setLastName('Test');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $wallet = $this->provider->provideForCustomer($customer);
        $this->entityManager->flush();

        return $wallet;
    }
}
