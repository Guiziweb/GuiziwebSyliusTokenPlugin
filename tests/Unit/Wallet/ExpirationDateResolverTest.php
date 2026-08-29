<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Wallet;

use Guiziweb\SyliusTokenPlugin\Wallet\ExpirationDateResolver;
use PHPUnit\Framework\TestCase;

final class ExpirationDateResolverTest extends TestCase
{
    public function testItReturnsNoDateWhenExpirationIsDisabled(): void
    {
        $resolver = new ExpirationDateResolver(false, 'P1Y');

        self::assertNull($resolver->resolve(new \DateTimeImmutable('2026-01-01')));
    }

    public function testItAddsTheConfiguredPeriodToTheAcquisitionDate(): void
    {
        $resolver = new ExpirationDateResolver(true, 'P6M');

        $expiresAt = $resolver->resolve(new \DateTimeImmutable('2026-01-01 10:00:00'));

        self::assertNotNull($expiresAt);
        self::assertSame('2026-07-01 10:00:00', $expiresAt->format('Y-m-d H:i:s'));
    }
}
