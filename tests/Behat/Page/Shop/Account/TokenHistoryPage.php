<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Shop\Account;

use Sylius\Behat\Page\SyliusPage;

class TokenHistoryPage extends SyliusPage implements TokenHistoryPageInterface
{
    public function getRouteName(): string
    {
        return 'guiziweb_sylius_token_shop_account_tokens_index';
    }

    public function getBalance(): int
    {
        return (int) trim($this->getElement('balance')->getText());
    }

    public function countMovements(): int
    {
        return count($this->getDocument()->findAll('css', 'table tbody tr'));
    }

    public function hasMovementOfType(string $type): bool
    {
        return $this->getDocument()->has('css', sprintf('table tbody .badge:contains("%s")', $type));
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'balance' => '[data-test-token-balance]',
        ]);
    }
}
