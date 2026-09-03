<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Form\Type\Admin;

use Guiziweb\SyliusTokenPlugin\Model\WalletAdjustment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WalletAdjustmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('direction', ChoiceType::class, [
                'label' => 'guiziweb_sylius_token.form.adjustment.direction',
                'choices' => [
                    'guiziweb_sylius_token.ui.adjustment.credit' => WalletAdjustment::DIRECTION_CREDIT,
                    'guiziweb_sylius_token.ui.adjustment.debit' => WalletAdjustment::DIRECTION_DEBIT,
                ],
                'expanded' => true,
            ])
            ->add('amount', IntegerType::class, [
                'label' => 'guiziweb_sylius_token.form.adjustment.amount',
            ])
            ->add('reason', TextType::class, [
                'label' => 'guiziweb_sylius_token.form.adjustment.reason',
                'help' => 'guiziweb_sylius_token.form.adjustment.reason_help',
            ])
            ->add('operationId', HiddenType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => WalletAdjustment::class]);
    }

    public function getBlockPrefix(): string
    {
        return 'guiziweb_sylius_token_wallet_adjustment';
    }
}
