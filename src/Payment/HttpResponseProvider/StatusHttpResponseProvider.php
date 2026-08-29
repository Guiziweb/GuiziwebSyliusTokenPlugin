<?php

declare(strict_types=1);

namespace Guiziweb\SyliusTokenPlugin\Payment\HttpResponseProvider;

use Sylius\Bundle\CoreBundle\OrderPay\Provider\FinalUrlProviderInterface;
use Sylius\Bundle\PaymentBundle\Provider\HttpResponseProviderInterface;
use Sylius\Bundle\ResourceBundle\Controller\RequestConfiguration;
use Sylius\Component\Payment\Model\PaymentRequestInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class StatusHttpResponseProvider implements HttpResponseProviderInterface
{
    public function __construct(private FinalUrlProviderInterface $finalUrlProvider)
    {
    }

    public function supports(RequestConfiguration $requestConfiguration, PaymentRequestInterface $paymentRequest): bool
    {
        return PaymentRequestInterface::ACTION_STATUS === $paymentRequest->getAction();
    }

    public function getResponse(RequestConfiguration $requestConfiguration, PaymentRequestInterface $paymentRequest): Response
    {
        return new RedirectResponse($this->finalUrlProvider->getUrl(null));
    }
}
