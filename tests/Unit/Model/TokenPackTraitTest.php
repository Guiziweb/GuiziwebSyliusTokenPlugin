<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Model;

use Guiziweb\SyliusTokenPlugin\Model\TokenPackInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenPackTrait;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\CoreBundle\Validator\Constraints\MaxIntegerValidator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tests\Guiziweb\SyliusTokenPlugin\Entity\Product\ProductVariant;

final class TokenPackTraitTest extends TestCase
{
    private const NOT_SHIPPABLE = 'guiziweb_sylius_token.product_variant.token_pack_is_not_shippable';

    private const MAX_INT = 2147483647;

    public function testAPackAloneIsValid(): void
    {
        $variant = $this->createVariant();
        $variant->setTokenAmount(100);

        self::assertCount(0, $this->validator()->validate($variant, null, ['sylius']));
    }

    public function testAPackWithoutValidityNeverExpires(): void
    {
        $variant = $this->createVariant();
        $variant->setTokenAmount(100);

        self::assertNull($variant->resolveExpirationDate(new \DateTimeImmutable('2026-03-01 12:00:00')));
    }

    public function testAPackWithAValidityExpiresThatManyMonthsAfterTheAcquisition(): void
    {
        $variant = $this->createVariant();
        $variant->setTokenAmount(100);
        $variant->setTokenValidityMonths(6);

        $expiresAt = $variant->resolveExpirationDate(new \DateTimeImmutable('2026-03-01 12:00:00'));

        self::assertNotNull($expiresAt);
        self::assertSame('2026-09-01 12:00:00', $expiresAt->format('Y-m-d H:i:s'));
    }

    /** @dataProvider endOfMonthAcquisitions */
    public function testAValidityNeverOverflowsIntoTheFollowingMonth(string $acquiredAt, int $months, string $expected): void
    {
        $variant = $this->createVariant();
        $variant->setTokenAmount(100);
        $variant->setTokenValidityMonths($months);

        $expiresAt = $variant->resolveExpirationDate(new \DateTimeImmutable($acquiredAt));

        self::assertNotNull($expiresAt);
        self::assertSame($expected, $expiresAt->format('Y-m-d H:i:s'));
    }

    /** @return iterable<string, array{string, int, string}> */
    public static function endOfMonthAcquisitions(): iterable
    {
        yield 'january 31st, one month' => ['2026-01-31 12:00:00', 1, '2026-02-28 12:00:00'];
        yield 'august 31st, one month' => ['2026-08-31 12:00:00', 1, '2026-09-30 12:00:00'];
        yield 'august 31st, six months' => ['2026-08-31 12:00:00', 6, '2027-02-28 12:00:00'];
        yield 'a day that exists in every month' => ['2026-03-01 12:00:00', 6, '2026-09-01 12:00:00'];
    }

    public function testAValidityMustBePositive(): void
    {
        $variant = $this->createVariant();
        $variant->setTokenAmount(100);
        $variant->setTokenValidityMonths(-3);

        $violations = $this->validator()->validate($variant, null, ['sylius']);

        self::assertCount(1, $violations);
    }

    public function testAPackCannotRequireShipping(): void
    {
        $variant = new ProductVariant();
        $variant->setTokenAmount(500);
        $variant->setShippingRequired(true);

        self::assertSame([self::NOT_SHIPPABLE], $this->shippingViolations($variant));
    }

    public function testAPackThatNeverShipsIsValid(): void
    {
        $variant = new ProductVariant();
        $variant->setTokenAmount(500);
        $variant->setShippingRequired(false);

        self::assertSame([], $this->shippingViolations($variant));
    }

    public function testAnOrdinaryVariantCanStillRequireShipping(): void
    {
        $variant = new ProductVariant();
        $variant->setShippingRequired(true);

        self::assertSame([], $this->shippingViolations($variant));
    }

    private function validator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->setConstraintValidatorFactory(new ConstraintValidatorFactory([
                'sylius_max_integer' => new MaxIntegerValidator(self::MAX_INT),
            ]))
            ->getValidator()
        ;
    }

    /** @return array<int, string> */
    private function shippingViolations(ProductVariant $variant): array
    {
        $violations = $this->validator()->validate($variant, null, ['sylius']);

        $messages = [];

        foreach ($violations as $violation) {
            if (self::NOT_SHIPPABLE === $violation->getMessageTemplate()) {
                $messages[] = $violation->getMessageTemplate();
            }
        }

        return $messages;
    }

    private function createVariant(): TokenPackInterface
    {
        return new class() implements TokenPackInterface {
            use TokenPackTrait;
        };
    }
}