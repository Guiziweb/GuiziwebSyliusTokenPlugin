<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Wallet;

use Doctrine\Common\Collections\ArrayCollection;
use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Model\PurchasePrice;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\OrderTokenCreditor;
use Guiziweb\SyliusTokenPlugin\Wallet\TokenCredit;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;

final class OrderTokenCreditorTest extends TestCase
{
    private WalletProviderInterface&MockObject $walletProvider;

    private WalletOperatorInterface&MockObject $walletOperator;

    private TokenWalletInterface&MockObject $wallet;

    protected function setUp(): void
    {
        $this->wallet = $this->createMock(TokenWalletInterface::class);
        $this->walletOperator = $this->createMock(WalletOperatorInterface::class);
        $this->walletProvider = $this->createMock(WalletProviderInterface::class);
        $this->walletProvider->method('provideForCustomer')->willReturn($this->wallet);
    }

    public function testItCreditsTokensTimesTheBoughtQuantity(): void
    {
        $order = $this->createOrder([$this->createTokenPackItem(id: 7, tokenAmount: 500, quantity: 3, total: 12000)]);

        $this->walletOperator
            ->expects(self::once())
            ->method('credit')
            ->with($this->wallet, self::callback(function (TokenCredit $credit) use ($order): bool {
                self::assertSame(1500, $credit->amount);
                self::assertSame('order-item-7', $credit->idempotencyKey);
                self::assertSame($order, $credit->order);
                self::assertEquals(new PurchasePrice(12000, 'EUR'), $credit->price);

                return true;
            }))
        ;

        $this->createCreditor()->credit($order);
    }

    public function testItCreditsOneBatchPerTokenPackLine(): void
    {
        $order = $this->createOrder([
            $this->createTokenPackItem(id: 1, tokenAmount: 100, quantity: 1, total: 1000),
            $this->createPlainItem(),
            $this->createTokenPackItem(id: 2, tokenAmount: 500, quantity: 1, total: 4000),
        ]);

        $this->walletOperator->expects(self::exactly(2))->method('credit');

        $this->createCreditor()->credit($order);
    }

    public function testItIgnoresOrdersWithoutTokenPacks(): void
    {
        $order = $this->createOrder([$this->createPlainItem()]);

        $this->walletProvider->expects(self::never())->method('provideForCustomer');
        $this->walletOperator->expects(self::never())->method('credit');

        $this->createCreditor()->credit($order);
    }

    public function testItIgnoresOrdersWithoutACustomer(): void
    {
        $order = $this->createOrder(
            [$this->createTokenPackItem(id: 1, tokenAmount: 100, quantity: 1, total: 1000)],
            withCustomer: false,
        );

        $this->walletOperator->expects(self::never())->method('credit');

        $this->createCreditor()->credit($order);
    }

    private function createCreditor(): OrderTokenCreditor
    {
        return new OrderTokenCreditor($this->walletProvider, $this->walletOperator);
    }

    /** @param array<int, OrderItemInterface> $items */
    private function createOrder(array $items, bool $withCustomer = true): OrderInterface&MockObject
    {
        $order = $this->createMock(OrderInterface::class);
        $order->method('getItems')->willReturn(new ArrayCollection($items));
        $order->method('getCurrencyCode')->willReturn('EUR');
        $order->method('getCustomer')->willReturn(
            $withCustomer ? $this->createMock(CustomerInterface::class) : null,
        );

        return $order;
    }

    private function createTokenPackItem(int $id, int $tokenAmount, int $quantity, int $total): OrderItemInterface
    {
        $variant = $this->createMockForIntersectionOfInterfaces([ProductVariantInterface::class, TokenPackInterface::class]);
        $variant->method('isTokenPack')->willReturn(true);
        $variant->method('getTokenAmount')->willReturn($tokenAmount);

        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getId')->willReturn($id);
        $item->method('getVariant')->willReturn($variant);
        $item->method('getQuantity')->willReturn($quantity);
        $item->method('getTotal')->willReturn($total);

        return $item;
    }

    private function createPlainItem(): OrderItemInterface
    {
        $item = $this->createMock(OrderItemInterface::class);
        $item->method('getVariant')->willReturn($this->createMock(ProductVariantInterface::class));

        return $item;
    }
}
