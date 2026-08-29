<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $catalog = $event->getMenu()->getChild('catalog');

        if (null === $catalog) {
            return;
        }

        $catalog
            ->addChild('guiziweb_token_packs', ['route' => 'guiziweb_sylius_token_admin_token_product_index'])
            ->setLabel('guiziweb_sylius_token.ui.token_products')
            ->setLabelAttribute('icon', 'tabler:coins')
        ;

        $catalog
            ->addChild('guiziweb_token_wallets', ['route' => 'guiziweb_sylius_token_admin_wallet_index'])
            ->setLabel('guiziweb_sylius_token.ui.wallets')
            ->setLabelAttribute('icon', 'tabler:wallet')
        ;

        $catalog
            ->addChild('guiziweb_token_tariffs', ['route' => 'guiziweb_sylius_token_admin_tariff_index'])
            ->setLabel('guiziweb_sylius_token.ui.tariffs')
            ->setLabelAttribute('icon', 'tabler:receipt')
        ;
    }
}
