# Usage

## Selling token packs

A token pack is a Sylius product that grants tokens instead of being shipped.
Create one from **Tokens → Products → Create**: the screen only asks for what a
pack needs, and the plugin marks it as non-shippable and untracked for you.

Set **Tokens granted** to the amount the customer receives. The price in currency
stays an ordinary Sylius channel price, so promotions, taxes and multi-currency
keep working.

When the payment of an order completes, the plugin credits the wallet of the
customer, once per order line, quantity included. Nothing happens before the
payment is completed.

## Spending tokens

Define what each action costs in **Tokens → Pricing**, then inject
`TokenConsumerInterface` in your own code:

```php
use Guiziweb\SyliusTokenPlugin\Wallet\TokenConsumerInterface;

public function __construct(private TokenConsumerInterface $tokenConsumer) {}

public function generateImage(CustomerInterface $customer, TokenPriceInterface $price): void
{
    $this->tokenConsumer->consume($customer, $price, 'image-generation-4521');

    // the customer has been debited, do the work
}
```

| method | behaviour |
|---|---|
| `consume()` | debits `cost * quantity`, throws `InsufficientTokenBalanceException` or `TokenPriceNotAvailableException` |
| `canConsume()` | tells whether the balance covers it, without debiting |
| `getBalance()` | current balance |

**The `reference` is the idempotency key.** Calling `consume()` twice with the same
reference debits once. Pass an identifier of the operation you are charging for,
never a random value: a retried request would otherwise debit the customer twice.

## Adjusting a balance by hand

**Tokens → Wallets → Adjust balance** adds or removes tokens from a customer
wallet. A reason is mandatory and kept in the ledger, so the movement stays
explainable months later.

Use it for goodwill gestures and for refunds: when you refund an order that
granted tokens, the plugin does not take them back on its own, so that you decide
what to do when the customer already spent part of them.

Adjustments are idempotent: submitting the same form twice credits once.

## Reading the history

Customers see their balance in the header and their movements under
**My account → My tokens**. Administrators get the same ledger per wallet, plus a
token summary on the customer page.

Every line is a real ledger entry: a purchase, a consumption, an expiration or a
manual adjustment, with the related order when there is one.

