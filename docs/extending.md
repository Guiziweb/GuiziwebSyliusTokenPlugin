# Extending

## Expiration

Tokens never expire unless the pack says so. Each token pack carries a validity in
months, set on the product variant next to the granted amount; leaving it empty
sells tokens that never expire.

The expiry date is computed **when tokens are acquired** and frozen on the batch.
Editing a pack never affects batches that already exist: tokens keep the terms
they were sold under.

The balance a customer sees is always derived from the batches, so an expired
batch stops counting the moment it expires — there is no cron to schedule and no
window during which the displayed balance is wrong.

Expiring is also a real ledger movement, not just a filter, so customers see where
their tokens went. That movement is written on the wallet's next operation, under
the same lock that guards every other write.

## Consuming tokens from your own code

`TokenConsumerInterface` is the public entry point, and the only one you should
need. It resolves the wallet, checks the price, applies the debit under a
pessimistic lock and writes the ledger entry.

If you need something lower level, `WalletOperatorInterface` credits and debits a
wallet directly, but you then own the idempotency key and the batch semantics.

## Overriding the models

Entities follow the Sylius resource pattern, so you can substitute your own:

```yaml
sylius_resource:
    resources:
        guiziweb_sylius_token.wallet:
            classes:
                model: App\Entity\Token\TokenWallet
```

The same applies to `guiziweb_sylius_token.batch`, `guiziweb_sylius_token.transaction`,
`guiziweb_sylius_token.operation` and `guiziweb_sylius_token.price`.

The plugin builds these entities through dedicated factories, so a substituted
class is really the one being persisted. Their constructors keep their arguments
and guard their invariants: a batch always holds a positive amount, a ledger entry
never holds zero. Your subclass must therefore keep the parent constructor
signature.

## Adding your own screens

Grids, routes and Twig hooks follow the Sylius conventions, so the usual extension
points apply: override a grid in `config/packages/`, add hookables under the
`guiziweb_sylius_token.admin.*` prefixes, or decorate any service of the plugin.
