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
            ->addChild('guiziweb_token_products', ['route' => 'guiziweb_sylius_token_admin_token_product_index'])
            ->setLabel('guiziweb_sylius_token.ui.token_products')
            ->setLabelAttribute('icon', 'tabler:coins')
        ;
    }
}
