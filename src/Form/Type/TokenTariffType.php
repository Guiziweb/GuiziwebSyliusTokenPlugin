<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Form\Type;

use Sylius\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class TokenTariffType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'label' => 'sylius.ui.code',
            ])
            ->add('name', TextType::class, [
                'label' => 'sylius.ui.name',
            ])
            ->add('cost', IntegerType::class, [
                'label' => 'guiziweb_sylius_token.form.tariff.cost',
                'help' => 'guiziweb_sylius_token.form.tariff.cost_help',
            ])
            ->add('enabled', CheckboxType::class, [
                'label' => 'sylius.ui.enabled',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_token_tariff';
    }
}
