<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Form\Extension\ProductVariant;

use Sylius\Bundle\ProductBundle\Form\Type\ProductVariantType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductVariantTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tokenAmount', IntegerType::class, [
            'label' => 'guiziweb_sylius_token.form.product_variant.token_amount',
            'required' => false,
        ]);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ProductVariantType::class];
    }
}
