<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Validator\Constraints;

use Guiziweb\SyliusTokenPlugin\Entity\TokenWallet\TokenWalletInterface;
use Guiziweb\SyliusTokenPlugin\Order\OrderKind;
use Guiziweb\SyliusTokenPlugin\Order\OrderKindResolverInterface;
use Guiziweb\SyliusTokenPlugin\Payment\OrderTokenPriceCalculatorInterface;
use Guiziweb\SyliusTokenPlugin\Wallet\WalletOperatorInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class OrderPayableWithTokensValidator extends ConstraintValidator
{
    /** @param RepositoryInterface<TokenWalletInterface> $walletRepository */
    public function __construct(
        private readonly OrderKindResolverInterface $orderKindResolver,
        private readonly OrderTokenPriceCalculatorInterface $tokenPriceCalculator,
        private readonly RepositoryInterface $walletRepository,
        private readonly WalletOperatorInterface $walletOperator,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        Assert::isInstanceOf($value, OrderInterface::class);
        Assert::isInstanceOf($constraint, OrderPayableWithTokens::class);

        $kind = $this->orderKindResolver->resolve($value);

        if (OrderKind::Regular === $kind) {
            return;
        }

        if (OrderKind::Mixed === $kind) {
            $this->context->addViolation($constraint->mixedMessage);

            return;
        }

        $customer = $value->getCustomer();

        if (!$customer instanceof CustomerInterface || !$customer->hasUser()) {
            $this->context->addViolation($constraint->guestMessage);

            return;
        }

        $wallet = $this->walletRepository->findOneBy(['customer' => $customer]);
        $balance = null === $wallet ? 0 : $this->walletOperator->getBalance($wallet);
        $price = $this->tokenPriceCalculator->calculate($value);

        if ($balance < $price) {
            $this->context->addViolation($constraint->insufficientBalanceMessage, [
                '%price%' => $price,
                '%balance%' => $balance,
            ]);
        }
    }
}
