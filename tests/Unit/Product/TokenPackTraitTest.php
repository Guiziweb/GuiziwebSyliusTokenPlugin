<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Unit\Product;

use Guiziweb\SyliusTokenPlugin\Product\TokenPackInterface;
use Guiziweb\SyliusTokenPlugin\Product\TokenPackTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class TokenPackTraitTest extends TestCase
{
    public function testAPackAloneIsValid(): void
    {
        $variant = $this->createVariant();
        $variant->setTokenAmount(100);

        self::assertCount(0, Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($variant, null, ['sylius']));
    }

    private function createVariant(): TokenPackInterface
    {
        return new class() implements TokenPackInterface {
            use TokenPackTrait;
        };
    }
}
