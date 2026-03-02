<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PaymentApp\Dependency\Facade;

use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\PaymentMethodCollectionTransfer;
use Generated\Shared\Transfer\PaymentMethodCriteriaTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PreOrderPaymentRequestTransfer;
use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\StoreTransfer;

class PaymentAppToPaymentFacadeBridge implements PaymentAppToPaymentFacadeInterface
{
    /**
     * @var \Spryker\Zed\Payment\Business\PaymentFacadeInterface
     */
    protected $paymentFacade;

    /**
     * @param \Spryker\Zed\Payment\Business\PaymentFacadeInterface $paymentFacade
     */
    public function __construct($paymentFacade)
    {
        $this->paymentFacade = $paymentFacade;
    }

    public function initializePreOrderPayment(PreOrderPaymentRequestTransfer $preOrderPaymentRequestTransfer): PreOrderPaymentResponseTransfer
    {
        return $this->paymentFacade->initializePreOrderPayment($preOrderPaymentRequestTransfer);
    }

    public function confirmPreOrderPayment(
        QuoteTransfer $quoteTransfer,
        CheckoutResponseTransfer $checkoutResponseTransfer
    ): void {
        $this->paymentFacade->confirmPreOrderPayment($quoteTransfer, $checkoutResponseTransfer);
    }

    public function cancelPreOrderPayment(PreOrderPaymentRequestTransfer $preOrderPaymentRequestTransfer): PreOrderPaymentResponseTransfer
    {
        return $this->paymentFacade->cancelPreOrderPayment($preOrderPaymentRequestTransfer);
    }

    public function expandPaymentWithPaymentSelection(
        PaymentTransfer $paymentTransfer,
        StoreTransfer $storeTransfer
    ): PaymentTransfer {
        return $this->paymentFacade->expandPaymentWithPaymentSelection($paymentTransfer, $storeTransfer);
    }

    public function getPaymentMethodCollection(PaymentMethodCriteriaTransfer $paymentMethodCriteriaTransfer): PaymentMethodCollectionTransfer
    {
        return $this->paymentFacade->getPaymentMethodCollection($paymentMethodCriteriaTransfer);
    }
}
