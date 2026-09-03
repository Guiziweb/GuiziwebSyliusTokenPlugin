<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Pack;

use Sylius\Behat\Page\Admin\Crud\CreatePage as BaseCreatePage;
use Webmozart\Assert\Assert;

class CreatePage extends BaseCreatePage implements CreatePageInterface
{
    public function specifyCode(string $code): void
    {
        $this->getElement('code')->setValue($code);
    }

    public function specifyName(string $name): void
    {
        $this->getDocument()->fillField('token_pack[translations][en_US][name]', $name);
        $this->getDocument()->fillField('token_pack[translations][en_US][slug]', strtolower(str_replace(' ', '-', $name)));
    }

    public function specifyTokenAmount(int $amount): void
    {
        $this->getElement('token_amount')->setValue((string) $amount);
    }

    public function specifyPrice(string $channelCode, string $price): void
    {
        $this->getDocument()
            ->findField(sprintf('token_pack[variant][channelPricings][%s][price]', $channelCode))
            ?->setValue($price)
        ;
    }

    public function enableChannel(string $channelCode): void
    {
        $checkbox = $this->getDocument()->find(
            'css',
            sprintf('input[name="token_pack[channels][]"][value="%s"]', $channelCode),
        );

        Assert::notNull($checkbox, sprintf('No channel checkbox found for "%s".', $channelCode));

        $checkbox->check();
    }

    public function hasShippingFields(): bool
    {
        return null !== $this->getDocument()->findField('token_pack[variant][shippingRequired]') ||
            null !== $this->getDocument()->findField('token_pack[variant][onHand]');
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'code' => '#token_pack_code',
            'token_amount' => '#token_pack_variant_tokenAmount',
        ]);
    }
}
