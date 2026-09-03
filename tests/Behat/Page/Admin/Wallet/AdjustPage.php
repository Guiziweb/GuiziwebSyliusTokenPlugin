<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Wallet;

use Sylius\Behat\Page\SyliusPage;

class AdjustPage extends SyliusPage implements AdjustPageInterface
{
    public function getRouteName(): string
    {
        return 'guiziweb_sylius_token_admin_wallet_adjust';
    }

    public function specifyAmount(int $amount): void
    {
        $this->getElement('amount')->setValue((string) $amount);
    }

    public function specifyReason(string $reason): void
    {
        $this->getElement('reason')->setValue($reason);
    }

    public function chooseCredit(): void
    {
        $this->getElement('direction_credit')->selectOption('credit');
    }

    public function chooseDebit(): void
    {
        $this->getElement('direction_debit')->selectOption('debit');
    }

    public function submit(): void
    {
        $this->getElement('submit')->press();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'amount' => '#guiziweb_sylius_token_wallet_adjustment_amount',
            'reason' => '#guiziweb_sylius_token_wallet_adjustment_reason',
            'direction_credit' => '#guiziweb_sylius_token_wallet_adjustment_direction_0',
            'direction_debit' => '#guiziweb_sylius_token_wallet_adjustment_direction_1',
            'submit' => 'form[name="guiziweb_sylius_token_wallet_adjustment"] button[type="submit"]',
        ]);
    }
}
