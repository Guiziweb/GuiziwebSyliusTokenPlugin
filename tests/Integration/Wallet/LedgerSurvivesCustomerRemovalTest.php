<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Integration\Wallet;

use Doctrine\ORM\EntityManagerInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatchInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransactionInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Tests\Guiziweb\SyliusTokenPlugin\Integration\ContainerTrait;

final class LedgerSurvivesCustomerRemovalTest extends KernelTestCase
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

    public function testDeletingACustomerKeepsTheWalletAndItsLedger(): void
    {
        $customer = $this->customer();
        $wallet = $this->provider->provideForCustomer($customer);
        $this->entityManager->flush();

        $this->operator->credit($wallet, new TokenCredit(300, uniqid('credit-', true)));

        $walletId = $wallet->getId();
        self::assertNotNull($walletId);

        $this->entityManager->remove($customer);
        $this->entityManager->flush();
        $this->entityManager->clear();

        $survivor = $this->entityManager->find($wallet::class, $walletId);

        self::assertInstanceOf(TokenWalletInterface::class, $survivor, 'The wallet must outlive its customer.');
        self::assertNull($survivor->getCustomer(), 'The wallet must be detached from the deleted customer.');
        self::assertCount(1, $this->batchesOf($walletId), 'The batches must be preserved.');
        self::assertCount(1, $this->transactionsOf($walletId), 'The ledger entries must be preserved.');
    }

    /** @return array<int, TokenBatchInterface> */
    private function batchesOf(int $walletId): array
    {
        /** @var array<int, TokenBatchInterface> $batches */
        $batches = $this->entityManager
            ->createQuery('SELECT b FROM Guiziweb\SyliusTokenPlugin\Entity\TokenBatch\TokenBatch b WHERE b.wallet = :wallet')
            ->setParameter('wallet', $walletId)
            ->getResult()
        ;

        return $batches;
    }

    /** @return array<int, TokenTransactionInterface> */
    private function transactionsOf(int $walletId): array
    {
        /** @var array<int, TokenTransactionInterface> $transactions */
        $transactions = $this->entityManager
            ->createQuery('SELECT t FROM Guiziweb\SyliusTokenPlugin\Entity\TokenTransaction\TokenTransaction t WHERE t.wallet = :wallet')
            ->setParameter('wallet', $walletId)
            ->getResult()
        ;

        return $transactions;
    }

    private function customer(): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = self::service(FactoryInterface::class, 'sylius.factory.customer')->createNew();
        $customer->setEmail(uniqid('ledger-test-', true) . '@example.com');
        $customer->setFirstName('Ledger');
        $customer->setLastName('Test');

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $customer;
    }
}
