# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

### Docker Environment (Recommended)
```bash
# Initialize Docker environment and install dependencies
make init

# Initialize database and run migrations
make database-init

# Load fixtures (optional)
make load-fixtures

# Start/stop containers
make up
make down

# Access containers
make php-shell
make node-shell
```

### Traditional Development
```bash
# Frontend setup
(cd vendor/sylius/test-application && yarn install)
(cd vendor/sylius/test-application && yarn build)
vendor/bin/console assets:install

# Database setup
vendor/bin/console doctrine:database:create
vendor/bin/console doctrine:migrations:migrate -n
vendor/bin/console sylius:fixtures:load -n

# Start server
symfony server:start -d
```

### Testing
```bash
# PHPUnit tests
vendor/bin/phpunit
make phpunit  # Docker

# Behat tests (non-JS)
vendor/bin/behat --strict --tags="~@javascript&&~@mink:chromedriver"
make behat  # Docker

# Behat tests (JS scenarios)
# Requires Chrome headless and symfony server
APP_ENV=test symfony server:start --port=8080 --daemon
vendor/bin/behat --strict --tags="@javascript,@mink:chromedriver"
```

### Code Quality
```bash
# PHPStan analysis
vendor/bin/phpstan analyse -c phpstan.neon -l max src/
make phpstan  # Docker

# Coding standards
vendor/bin/ecs check
make ecs  # Docker
```

### Composer Scripts
```bash
# Database reset with fixtures
composer run database-reset

# Frontend rebuild
composer run frontend-clear

# Complete test app initialization
composer run test-app-init
```

## Architecture

Prepaid token wallet for Sylius: customers buy token packs, tokens are credited on
payment, and spent later against a price list.

### Core concepts

- **TokenWallet** - one per customer, owns the batches
- **TokenBatch** - tokens arrive in batches, each with its own acquisition date,
  purchase price and optional expiry
- **TokenTransaction** - append-only ledger, the source of truth
- **TokenPrice** - what one action costs in tokens

A pack carries a validity in months; the expiry date is frozen on the batch when
the tokens are acquired. A balance is always derived from the batches, never
stored, so it can never go stale and expiry needs no scheduled task.

### Structure

- `src/Entity/` - persisted entities, one directory per entity with its interface
- `src/Model/` - non persisted: contracts, traits and value objects
- `src/Wallet/` - domain services (operator, allocator, consumer, provider)
- `src/Factory/` - `TokenPackFactory` decorates `sylius.factory.product`
- `config/services/` - one file per domain
- `config/twig_hooks/` - hookables, mirroring the `templates/` tree

### Invariants to preserve

- Every write goes through `WalletOperator::record()`, which takes a pessimistic
  lock on the wallet and checks an idempotency key before mutating anything
- Debits allocate batches oldest-expiring-first, and always re-read them **under**
  the lock: an entity loaded before the lock may be stale
- `record()` settles expired batches under the lock before running the operation,
  which is what keeps the ledger honest without a cron
- A token pack is never shippable nor tracked; the factory sets it, a constraint
  on `TokenPackTrait` enforces it
- The ledger outlives its customer: `wallet.customer_id` is `ON DELETE SET NULL`

### Known limitations

- Refunds do not take tokens back, this is left to a manual adjustment
- `hasIdempotencyKey` is deliberately a plain read. Making it `FOR UPDATE` would
  guard against a stale read view when `record()` runs inside an already-open
  transaction (the Sylius payment bus does that), but MySQL then takes a gap lock
  on `guiziweb_token_transaction_replay_idx` — a key space shared by every wallet.
  Two customers paying at the same time deadlock on each other's insert intention,
  which was measured and is far more frequent than the double webhook it would
  protect against. The wallet lock already serialises writes on one wallet
- `WalletProvider` reads then creates without a lock: two concurrent first
  purchases for the same customer both insert, and the loser hits the unique
  index on `customer_id`, which closes the EntityManager and loses that credit.
  The window is a few milliseconds on a customer's very first purchase, and the
  PSP retrying its webhook recovers it. Locking is not an option here: `lock()`
  needs an open transaction, and there is no row to lock yet.
