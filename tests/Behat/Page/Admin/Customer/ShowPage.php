<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Customer;

use Sylius\Behat\Page\SyliusPage;

class ShowPage extends SyliusPage implements ShowPageInterface
{
    public function getRouteName(): string
    {
        return 'sylius_admin_customer_show';
    }

    public function getTokenBalance(): int
    {
        return (int) trim($this->getElement('token_balance')->getText());
    }

    public function getTotalCredited(): int
    {
        return (int) trim($this->getElement('token_credited')->getText());
    }

    public function getTotalSpent(): int
    {
        return (int) trim($this->getElement('token_spent')->getText());
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'token_balance' => '[data-test-customer-tokens-balance]',
            'token_credited' => '[data-test-customer-tokens-credited]',
            'token_spent' => '[data-test-customer-tokens-spent]',
        ]);
    }
}
