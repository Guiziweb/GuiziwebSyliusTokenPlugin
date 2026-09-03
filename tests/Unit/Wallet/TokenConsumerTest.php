<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Wallet;

use Guiziweb\SyliusTokenPlugin\Entity\TokenPrice\TokenPriceInterface;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Exception\TokenPriceNotAvailableException;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumer;
use Guiziweb\SyliusTokenPlugin\Model\TokenDebit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;

final class TokenConsumerTest extends TestCase
{
    private RepositoryInterface&MockObject $walletRepository;

    private WalletOperatorInterface&MockObject $walletOperator;

    private TokenWalletInterface $wallet;

    private CustomerInterface $customer;

    protected function setUp(): void
    {
        $this->wallet = $this->createMock(TokenWalletInterface::class);
        $this->customer = $this->createMock(CustomerInterface::class);
        $this->walletRepository = $this->createMock(RepositoryInterface::class);
        $this->walletOperator = $this->createMock(WalletOperatorInterface::class);
    }

    public function testItDebitsTheCostTimesTheQuantityUnderTheGivenReference(): void
    {
        $this->walletOperator
            ->expects(self::once())
            ->method('debit')
            ->with($this->wallet, self::callback(static function (TokenDebit $debit): bool {
                self::assertSame(15, $debit->amount);
                self::assertSame('cv-4521', $debit->idempotencyKey);

                return true;
            }))
        ;

        $this->createConsumer()->consume($this->customer, $this->createPrice(5), 'cv-4521', 3);
    }

    public function testItRefusesADisabledPrice(): void
    {
        $this->walletOperator->expects(self::never())->method('debit');

        $this->expectException(TokenPriceNotAvailableException::class);

        $this->createConsumer()->consume($this->customer, $this->createPrice(5, enabled: false), 'cv-4521');
    }

    public function testItTellsWhetherTheBalanceCoversTheConsumption(): void
    {
        $this->walletRepository->method('findOneBy')->willReturn($this->wallet);
        $this->walletOperator->method('getBalance')->willReturn(9);

        $consumer = $this->createConsumer();

        self::assertTrue($consumer->canConsume($this->customer, $this->createPrice(5), 1));
        self::assertFalse($consumer->canConsume($this->customer, $this->createPrice(5), 2));
    }

    public function testACustomerWithoutAWalletHasNothingToSpend(): void
    {
        $this->walletRepository->method('findOneBy')->willReturn(null);

        self::assertSame(0, $this->createConsumer()->getBalance($this->customer));
        self::assertFalse($this->createConsumer()->canConsume($this->customer, $this->createPrice(1)));
    }

    private function createConsumer(): TokenConsumer
    {
        $provider = $this->createMock(WalletProviderInterface::class);
        $provider->method('provideForCustomer')->willReturn($this->wallet);

        return new TokenConsumer($this->walletRepository, $provider, $this->walletOperator);
    }

    private function createPrice(int $cost, bool $enabled = true): TokenPriceInterface
    {
        $price = $this->createMock(TokenPriceInterface::class);
        $price->method('getCost')->willReturn($cost);
        $price->method('isEnabled')->willReturn($enabled);

        return $price;
    }
}
