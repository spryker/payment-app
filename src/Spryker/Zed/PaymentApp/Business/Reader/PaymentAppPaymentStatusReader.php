<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\PaymentApp\Business\Reader;

use Generated\Shared\Transfer\PaymentAppPaymentStatusCollectionTransfer;
use Generated\Shared\Transfer\PaymentAppPaymentStatusCriteriaTransfer;
use Spryker\Zed\PaymentApp\Persistence\PaymentAppRepositoryInterface;

class PaymentAppPaymentStatusReader implements PaymentAppPaymentStatusReaderInterface
{
    public function __construct(protected PaymentAppRepositoryInterface $paymentAppRepository)
    {
    }

    public function getPaymentAppPaymentStatusCollection(
        PaymentAppPaymentStatusCriteriaTransfer $paymentAppPaymentStatusCriteriaTransfer
    ): PaymentAppPaymentStatusCollectionTransfer {
        return $this->paymentAppRepository->getPaymentAppPaymentStatusCollection($paymentAppPaymentStatusCriteriaTransfer);
    }
}
