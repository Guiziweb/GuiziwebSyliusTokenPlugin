<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Wallet;

use Sylius\Behat\Page\SyliusPage;

class HistoryPage extends SyliusPage implements HistoryPageInterface
{
    public function getRouteName(): string
    {
        return 'guiziweb_sylius_token_admin_wallet_history';
    }

    public function countMovements(): int
    {
        return count($this->getDocument()->findAll('css', 'table tbody tr'));
    }

    public function hasMovementOfType(string $type): bool
    {
        return str_contains(strtolower($this->getDocument()->getText()), strtolower($type));
    }
}
