<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;

final class WalletAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('direction', ChoiceType::class, [
                'label' => 'guiziweb_sylius_token.form.adjustment.direction',
                'choices' => [
                    'guiziweb_sylius_token.ui.adjustment.credit' => 'credit',
                    'guiziweb_sylius_token.ui.adjustment.debit' => 'debit',
                ],
                'expanded' => true,
                'data' => 'credit',
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'guiziweb_sylius_token.form.adjustment.amount',
                'constraints' => [new NotBlank(), new Positive()],
            ])
            ->add('reason', TextType::class, [
                'label' => 'guiziweb_sylius_token.form.adjustment.reason',
                'help' => 'guiziweb_sylius_token.form.adjustment.reason_help',
                'constraints' => [new NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_token_wallet_adjustment';
    }
}
