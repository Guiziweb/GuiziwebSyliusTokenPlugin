<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Wallet;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface AdjustPageInterface extends SymfonyPageInterface
{
    public function specifyAmount(int $amount): void;

    public function specifyReason(string $reason): void;

    public function chooseCredit(): void;

    public function chooseDebit(): void;

    public function submit(): void;
}
