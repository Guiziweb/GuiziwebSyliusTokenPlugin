# Installation

## 1. Require the plugin

```bash
composer require guiziweb/sylius-token-plugin
```

## 2. Register the plugin

```php
# config/bundles.php

return [
    // ...
    Guiziweb\SyliusTokenPlugin\GuiziwebSyliusTokenPlugin::class => ['all' => true],
];
```

## 3. Import the configuration

```yaml
# config/packages/guiziweb_sylius_token.yaml

imports:
    - { resource: "@GuiziwebSyliusTokenPlugin/config/config.yaml" }
```

Import this file **before** clearing the cache. The bundle declares services that
depend on resources configured here, so an application that registers the bundle
without importing its configuration fails to boot.

## 4. Import the routes

The shop routes must be mounted under `{_locale}`, like the rest of the Sylius storefront.

```yaml
# config/routes/guiziweb_sylius_token.yaml

guiziweb_sylius_token_admin:
    resource: "@GuiziwebSyliusTokenPlugin/config/routes/admin.yaml"
    prefix: /admin

guiziweb_sylius_token_shop:
    resource: "@GuiziwebSyliusTokenPlugin/config/routes/shop.yaml"
    prefix: /{_locale}
    requirements:
        _locale: ^[A-Za-z]{2,4}(_([A-Za-z]{4}|[0-9]{3}))?(_([A-Za-z]{2}|[0-9]{3}))?$
```

## Make your product variants able to grant tokens

**This step is required and cannot be automated.** A pack grants tokens through a
field carried by your own `ProductVariant` entity, so the plugin needs you to apply
its trait:

```php
# src/Entity/Product/ProductVariant.php

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
```

Without it, creating a token pack fails with
`Apply TokenPackTrait to your ProductVariant entity.`

## 5. Run the migrations

```bash
bin/console doctrine:migrations:migrate
```

## Database support

The migrations target **MySQL, MariaDB and PostgreSQL**. They ship as a pair: one
migration runs on MySQL and MariaDB, its counterpart on PostgreSQL, and each skips
itself on the platform it does not target.
