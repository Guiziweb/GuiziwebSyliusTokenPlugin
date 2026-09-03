<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Shop\Account;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface TokenHistoryPageInterface extends SymfonyPageInterface
{
    public function getBalance(): int;

    public function countMovements(): int;

    public function hasMovementOfType(string $type): bool;
}
