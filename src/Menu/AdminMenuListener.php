<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    public function __invoke(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        $tokens = $menu
            ->addChild('guiziweb_tokens')
            ->setLabel('guiziweb_sylius_token.menu.header')
            ->setLabelAttribute('icon', 'tabler:coins')
        ;

        $tokens
            ->addChild('guiziweb_token_packs', ['route' => 'guiziweb_sylius_token_admin_token_product_index', 'extras' => ['routes' => [
                ['route' => 'guiziweb_sylius_token_admin_token_product_create'],
                ['route' => 'guiziweb_sylius_token_admin_token_product_update'],
                ['route' => 'guiziweb_sylius_token_admin_token_product_show'],
            ]]])
            ->setLabel('guiziweb_sylius_token.menu.products')
            ->setLabelAttribute('icon', 'tabler:package')
        ;

        $tokens
            ->addChild('guiziweb_token_wallets', ['route' => 'guiziweb_sylius_token_admin_wallet_index', 'extras' => ['routes' => [
                ['route' => 'guiziweb_sylius_token_admin_wallet_adjust'],
                ['route' => 'guiziweb_sylius_token_admin_wallet_history'],
            ]]])
            ->setLabel('guiziweb_sylius_token.menu.wallets')
            ->setLabelAttribute('icon', 'tabler:wallet')
        ;

        $tokens
            ->addChild('guiziweb_token_prices', ['route' => 'guiziweb_sylius_token_admin_price_index', 'extras' => ['routes' => [
                ['route' => 'guiziweb_sylius_token_admin_price_create'],
                ['route' => 'guiziweb_sylius_token_admin_price_update'],
            ]]])
            ->setLabel('guiziweb_sylius_token.menu.pricing')
            ->setLabelAttribute('icon', 'tabler:receipt')
        ;

        $order = array_keys($menu->getChildren());
        $position = array_search('catalog', $order, true);

        if (false !== $position) {
            unset($order[array_search('guiziweb_tokens', $order, true)]);
            array_splice($order, $position + 1, 0, 'guiziweb_tokens');
            $menu->reorderChildren($order);
        }
    }
}
