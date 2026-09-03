<?php

declare(strict_types=1);

namespace Tests\Guiziweb\SyliusTokenPlugin\Entity\Product;

use Doctrine\ORM\Mapping as ORM;
use Guiziweb\SyliusTokenPlugin\Model\TokenPackInterface;
use Guiziweb\SyliusTokenPlugin\Model\TokenPackTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements TokenPackInterface
{
    use TokenPackTrait;
}
