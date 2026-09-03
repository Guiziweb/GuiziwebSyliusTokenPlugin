<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Customer;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface ShowPageInterface extends SymfonyPageInterface
{
    public function getTokenBalance(): int;

    public function getTotalCredited(): int;

    public function getTotalSpent(): int;
}
