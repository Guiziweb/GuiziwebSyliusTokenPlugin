<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Pack;

use Sylius\Behat\Page\Admin\Crud\CreatePageInterface as BaseCreatePageInterface;

interface CreatePageInterface extends BaseCreatePageInterface
{
    public function specifyCode(string $code): void;

    public function specifyName(string $name): void;

    public function specifyTokenAmount(int $amount): void;

    public function specifyPrice(string $channelCode, string $price): void;

    public function enableChannel(string $channelCode): void;

    public function hasShippingFields(): bool;
}
