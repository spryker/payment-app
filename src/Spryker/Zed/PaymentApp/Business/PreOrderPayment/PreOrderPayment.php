<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PaymentApp\Business\PreOrderPayment;

use ArrayObject;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ExpressCheckoutPaymentRequestTransfer;
use Generated\Shared\Transfer\ExpressCheckoutPaymentResponseTransfer;
use Generated\Shared\Transfer\PreOrderPaymentRequestTransfer;
use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\PaymentApp\Business\RequestExecutor\ExpressCheckoutPaymentRequestExecutorInterface;
use Spryker\Zed\PaymentApp\Dependency\Facade\PaymentAppToPaymentFacadeInterface;

class PreOrderPayment implements PreOrderPaymentInterface
{
    /**
     * @var \Spryker\Zed\PaymentApp\Dependency\Facade\PaymentAppToPaymentFacadeInterface
     */
    protected PaymentAppToPaymentFacadeInterface $paymentFacade;

    /**
     * @var \Spryker\Zed\PaymentApp\Business\RequestExecutor\ExpressCheckoutPaymentRequestExecutorInterface
     */
    protected ExpressCheckoutPaymentRequestExecutorInterface $expressCheckoutPaymentRequestExecutor;

    public function __construct(
        PaymentAppToPaymentFacadeInterface $paymentFacade,
        ExpressCheckoutPaymentRequestExecutorInterface $expressCheckoutPaymentRequestExecutor
    ) {
        $this->paymentFacade = $paymentFacade;
        $this->expressCheckoutPaymentRequestExecutor = $expressCheckoutPaymentRequestExecutor;
    }

    public function initializePreOrderPayment(
        PreOrderPaymentRequestTransfer $preOrderPaymentRequestTransfer
    ): PreOrderPaymentResponseTransfer {
        $quoteTransfer = $preOrderPaymentRequestTransfer->getQuoteOrFail();
        $customerTransfer = $quoteTransfer->getCustomer();

        // In case, we have a guest checkout we need to set an "empty" customer
        if (!$customerTransfer) {
            $customerTransfer = new CustomerTransfer();
        }

        // If no shipping address is present (guest) we need to set an empty shipping address for getting the shipping method calculation
        if ($customerTransfer->getShippingAddress()->count() === 0) {
            $customerTransfer->setShippingAddress(new ArrayObject([new AddressTransfer()]));
            $quoteTransfer->setCustomer($customerTransfer);
        }

        $expressCheckoutPaymentRequestTransfer = new ExpressCheckoutPaymentRequestTransfer();
        $expressCheckoutPaymentRequestTransfer->setQuote($quoteTransfer);

        $expressCheckoutPaymentResponseTransfer = $this->expressCheckoutPaymentRequestExecutor
            ->processExpressCheckoutPaymentRequest($expressCheckoutPaymentRequestTransfer);

        if ($expressCheckoutPaymentResponseTransfer->getErrors()->count() > 0) {
            return (new PreOrderPaymentResponseTransfer())
                ->setIsSuccessful(false)
                ->setError($this->getErrorMessageFromCheckoutPaymentRequestTransfer($expressCheckoutPaymentResponseTransfer));
        }

        $quoteTransfer = $expressCheckoutPaymentRequestTransfer->getQuoteOrFail();

        // We need to remove the items from the quote as they are not complete yet and may fail when trying to be persisted on the App side
        $quoteTransfer->setItems(new ArrayObject());

        $preOrderPaymentRequestTransfer->setQuote($quoteTransfer);

        return $this->paymentFacade->initializePreOrderPayment($preOrderPaymentRequestTransfer);
    }

    protected function getErrorMessageFromCheckoutPaymentRequestTransfer(ExpressCheckoutPaymentResponseTransfer $expressCheckoutPaymentResponseTransfer): string
    {
        $errorMessages = [];
        $errorTransfers = $expressCheckoutPaymentResponseTransfer->getErrors();

        foreach ($errorTransfers as $errorTransfer) {
            $errorMessages[] = $errorTransfer->getMessage();
        }

        return implode(', ', $errorMessages);
    }

    public function confirmPreOrderPayment(
        QuoteTransfer $quoteTransfer,
        CheckoutResponseTransfer $checkoutResponseTransfer
    ): void {
        $this->paymentFacade->confirmPreOrderPayment($quoteTransfer, $checkoutResponseTransfer);
    }

    public function cancelPreOrderPayment(
        PreOrderPaymentRequestTransfer $preOrderPaymentRequestTransfer
    ): PreOrderPaymentResponseTransfer {
        return $this->paymentFacade->cancelPreOrderPayment($preOrderPaymentRequestTransfer);
    }
}
