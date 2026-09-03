<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Provider;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Provider\CurrentWalletProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CurrentWalletProviderTest extends TestCase
{
    /** @var RepositoryInterface<TokenWalletInterface>&MockObject */
    private RepositoryInterface&MockObject $walletRepository;

    protected function setUp(): void
    {
        $this->walletRepository = $this->createMock(RepositoryInterface::class);
    }

    public function testItProvidesTheWalletNamedByTheRoute(): void
    {
        $wallet = $this->createMock(TokenWalletInterface::class);
        $this->walletRepository->expects(self::once())->method('find')->with(7)->willReturn($wallet);

        self::assertSame($wallet, $this->createProvider(['id' => '7'])->getWallet());
    }

    public function testItFailsOutsideOfARequest(): void
    {
        $this->walletRepository->expects(self::never())->method('find');

        $this->expectException(NotFoundHttpException::class);

        (new CurrentWalletProvider(new RequestStack(), $this->walletRepository))->getWallet();
    }

    public function testItFailsWhenTheRouteCarriesNoIdentifier(): void
    {
        $this->walletRepository->expects(self::never())->method('find');

        $this->expectException(NotFoundHttpException::class);

        $this->createProvider([])->getWallet();
    }

    public function testItRefusesAnIdentifierThatIsNotANumber(): void
    {
        $this->walletRepository->expects(self::never())->method('find');

        $this->expectException(NotFoundHttpException::class);

        $this->createProvider(['id' => 'sept'])->getWallet();
    }

    public function testItFailsWhenTheWalletIsGone(): void
    {
        $this->walletRepository->method('find')->willReturn(null);

        $this->expectException(NotFoundHttpException::class);

        $this->createProvider(['id' => '404'])->getWallet();
    }

    /** @param array<string, string> $attributes */
    private function createProvider(array $attributes): CurrentWalletProvider
    {
        $request = new Request();

        foreach ($attributes as $key => $value) {
            $request->attributes->set($key, $value);
        }

        $stack = new RequestStack();
        $stack->push($request);

        return new CurrentWalletProvider($stack, $this->walletRepository);
    }
}
