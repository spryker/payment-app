<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\PaymentApp\Communication\Plugin\Oms;

use Generated\Shared\Transfer\PaymentAppPaymentStatusRequestTransfer;
use Orm\Zed\Sales\Persistence\SpySalesOrderItem;
use Spryker\Shared\PaymentApp\Status\PaymentStatus;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Oms\Dependency\Plugin\Condition\ConditionInterface;

/**
 * @method \Spryker\Zed\PaymentApp\PaymentAppConfig getConfig()
 * @method \Spryker\Zed\PaymentApp\Business\PaymentAppFacadeInterface getFacade()
 * @method \Spryker\Zed\PaymentApp\Business\PaymentAppBusinessFactory getFactory()
 */
class IsPaymentAppPaymentStatusRefundedConditionPlugin extends AbstractPlugin implements ConditionInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     */
    public function check(SpySalesOrderItem $orderItem): bool
    {
        $paymentAppPaymentStatusRequestTransfer = (new PaymentAppPaymentStatusRequestTransfer())
            ->setOrderReference($orderItem->getOrder()->getOrderReference())
            ->setStatus(PaymentStatus::STATUS_REFUNDED);

        $paymentAppPaymentStatusResponseTransfer = $this->getFacade()->hasPaymentAppExpectedPaymentStatus($paymentAppPaymentStatusRequestTransfer);

        return $paymentAppPaymentStatusResponseTransfer->getIsInExpectedStateOrFail();
    }
}
