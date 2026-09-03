<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Price;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function specifyCode(string $code): void
    {
        $this->getElement('code')->setValue($code);
    }

    public function specifyName(string $name): void
    {
        $this->getElement('name')->setValue($name);
    }

    public function specifyCost(int $cost): void
    {
        $this->getElement('cost')->setValue((string) $cost);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'code' => '#guiziweb_sylius_token_price_code',
            'name' => '#guiziweb_sylius_token_price_name',
            'cost' => '#guiziweb_sylius_token_price_cost',
        ]);
    }
}
