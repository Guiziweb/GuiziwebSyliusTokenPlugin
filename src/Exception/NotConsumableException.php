<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Exception;

final class NotConsumableException extends \InvalidArgumentException
{
    public function __construct()
    {
        parent::__construct('This product variant has no price in tokens.');
    }
}
