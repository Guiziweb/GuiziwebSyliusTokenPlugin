<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Behat\Page\Admin\Wallet;

use FriendsOfBehat\PageObjectExtension\Page\SymfonyPageInterface;

interface HistoryPageInterface extends SymfonyPageInterface
{
    public function countMovements(): int;

    public function hasMovementOfType(string $type): bool;
}
