<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PaymentApp\Communication\Controller;

use Generated\Shared\Transfer\ExpressCheckoutPaymentRequestTransfer;
use Generated\Shared\Transfer\ExpressCheckoutPaymentResponseTransfer;
use Generated\Shared\Transfer\PaymentCustomerRequestTransfer;
use Generated\Shared\Transfer\PaymentCustomerResponseTransfer;
use Generated\Shared\Transfer\PreOrderPaymentRequestTransfer;
use Generated\Shared\Transfer\PreOrderPaymentResponseTransfer;
use Spryker\Zed\Kernel\Communication\Controller\AbstractGatewayController;

/**
 * @method \Spryker\Zed\PaymentApp\Business\PaymentAppFacadeInterface getFacade()
 * @method \Spryker\Zed\PaymentApp\Persistence\PaymentAppRepositoryInterface getRepository()
 */
class GatewayController extends AbstractGatewayController
{
    public function processExpressCheckoutPaymentRequestAction(
        ExpressCheckoutPaymentRequestTransfer $expressCheckoutPaymentRequestTransfer
    ): ExpressCheckoutPaymentResponseTransfer {
        return $this->getFacade()->processExpressCheckoutPaymentRequest($expressCheckoutPaymentRequestTransfer);
    }

    public function getCustomerAction(
        PaymentCustomerRequestTransfer $paymentCustomerRequestTransfer
    ): PaymentCustomerResponseTransfer {
        return $this->getFacade()->getCustomer($paymentCustomerRequestTransfer);
    }

    public function initializePreOrderPaymentAction(
        PreOrderPaymentRequestTransfer $preOrderPaymentRequestTransfer
    ): PreOrderPaymentResponseTransfer {
        return $this->getFacade()->initializePreOrderPayment($preOrderPaymentRequestTransfer);
    }

    public function cancelPreOrderPaymentAction(
        PreOrderPaymentRequestTransfer $preOrderPaymentRequestTransfer
    ): PreOrderPaymentResponseTransfer {
        return $this->getFacade()->cancelPreOrderPayment($preOrderPaymentRequestTransfer);
    }
}
