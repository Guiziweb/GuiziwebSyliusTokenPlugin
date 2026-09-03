<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Pack;

use Sylius\Behat\Page\Admin\Crud\UpdatePage as BaseUpdatePage;

class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    public function specifyTokenAmount(int $amount): void
    {
        $this->getElement('token_amount')->setValue((string) $amount);
    }

    public function specifyValidityMonths(int $months): void
    {
        $this->getElement('token_validity_months')->setValue((string) $months);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'token_amount' => '#token_pack_variant_tokenAmount',
            'token_validity_months' => '#token_pack_variant_tokenValidityMonths',
        ]);
    }
}
