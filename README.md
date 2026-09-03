<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
          <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
          <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
          <img alt="Sylius Logo." src="https://media.sylius.com/sylius-logo-800.png">
        </picture>
    </a>
</p>

<h1 align="center">Sylius Token Plugin</h1>

<p align="center">
    <a href="https://github.com/Guiziweb/GuiziwebSyliusTokenPlugin/actions"><img src="https://img.shields.io/github/actions/workflow/status/Guiziweb/GuiziwebSyliusTokenPlugin/build.yaml?branch=main" alt="Build status"></a>
    <a href="https://packagist.org/packages/guiziweb/sylius-token-plugin"><img src="https://img.shields.io/packagist/v/guiziweb/sylius-token-plugin" alt="Latest version"></a>
    <a href="https://packagist.org/packages/guiziweb/sylius-token-plugin"><img src="https://img.shields.io/packagist/php-v/guiziweb/sylius-token-plugin" alt="PHP version"></a>
    <img src="https://img.shields.io/badge/phpunit-passing-success" alt="PHPUnit">
    <img src="https://img.shields.io/badge/behat-passing-success" alt="Behat">
    <img src="https://img.shields.io/badge/phpstan-level%20max-blue" alt="PHPStan max">
    <img src="https://img.shields.io/badge/ecs-passing-success" alt="ECS">
    <a href="https://packagist.org/packages/guiziweb/sylius-token-plugin"><img src="https://img.shields.io/packagist/l/guiziweb/sylius-token-plugin" alt="License"></a>
</p>

> **Buy tokens, spend them on anything.** Prepaid wallet for Sylius, the way AI SaaS products bill their users.

## How it works

Customers buy **token packs**: ordinary Sylius products that grant tokens instead of being shipped. Tokens land in a **wallet** when the payment completes, and your own code spends them against a **price list** that says what each action costs.

```php
$this->tokenConsumer->consume($customer, $price, 'image-generation-4521');
```

Every movement is written to an append-only ledger. Tokens are held in batches, each with its own acquisition date, purchase price and optional expiry, and spent oldest-expiring-first. A balance is always derived from those batches, so an expired batch stops counting the moment it expires.

## Requirements

- PHP ^8.2
- Sylius ^2.0
- MySQL, MariaDB or PostgreSQL

## Install

```bash
composer require guiziweb/sylius-token-plugin
```

Then register the bundle, import the configuration and the routes, apply the
plugin trait to your `ProductVariant`, and run the migrations. The five steps are
in [installation](docs/installation.md) — none of them can be skipped: without the
trait a product cannot grant tokens, and without the imports the application will
not boot.

## Documentation

- [Installation](docs/installation.md) - full setup steps
- [Usage](docs/usage.md) - selling packs, spending tokens, adjusting balances
- [Extending](docs/extending.md) - expiration policy, overriding models

## License

MIT - see [LICENSE](LICENSE).
