<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Form\Type\Admin;

use Sylius\Bundle\AdminBundle\Form\Type\ProductType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Valid;

final class TokenPackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event): void {
            $event->getForm()->add('variant', TokenPackVariantType::class, [
                'property_path' => 'variants[0]',
                'constraints' => [new Valid()],
                'label' => false,
            ]);
        });
    }

    public function getParent(): string
    {
        return ProductType::class;
    }
}
