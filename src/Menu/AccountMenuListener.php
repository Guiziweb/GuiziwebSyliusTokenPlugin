<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AccountMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $event->getMenu()
            ->addChild('tokens', ['route' => 'guiziweb_sylius_token_shop_account_tokens_index'])
            ->setLabel('guiziweb_sylius_token.menu.shop.account.tokens')
            ->setLabelAttribute('icon', 'tabler:coins')
        ;
    }
}
