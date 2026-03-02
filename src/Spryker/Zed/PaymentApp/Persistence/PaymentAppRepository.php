<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\PaymentApp\Persistence;

use Generated\Shared\Transfer\PaymentAppPaymentStatusCollectionTransfer;
use Generated\Shared\Transfer\PaymentAppPaymentStatusCriteriaTransfer;
use Orm\Zed\PaymentApp\Persistence\SpyPaymentAppPaymentStatusQuery;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \Spryker\Zed\PaymentApp\Persistence\PaymentAppPersistenceFactory getFactory()
 */
class PaymentAppRepository extends AbstractRepository implements PaymentAppRepositoryInterface
{
    public function getPaymentAppPaymentStatusCollection(
        PaymentAppPaymentStatusCriteriaTransfer $paymentAppPaymentStatusCriteriaTransfer
    ): PaymentAppPaymentStatusCollectionTransfer {
        $paymentAppPaymentStatusQuery = $this->getFactory()->createPaymentAppPaymentStatusQuery();
        $paymentAppPaymentStatusQuery = $this->applyPaymentAppPaymentStatusCriteria($paymentAppPaymentStatusQuery, $paymentAppPaymentStatusCriteriaTransfer);

        $paymentAppPaymentStatusEntityCollection = $paymentAppPaymentStatusQuery->find();

        return $this->getFactory()->createPaymentAppPaymentStatusMapper()->mapPaymentAppPaymentStatusEntityCollectionToTransferCollection($paymentAppPaymentStatusEntityCollection, new PaymentAppPaymentStatusCollectionTransfer());
    }

    protected function applyPaymentAppPaymentStatusCriteria(
        SpyPaymentAppPaymentStatusQuery $paymentAppPaymentStatusQuery,
        PaymentAppPaymentStatusCriteriaTransfer $paymentAppPaymentStatusCriteriaTransfer
    ): SpyPaymentAppPaymentStatusQuery {
        return $paymentAppPaymentStatusQuery->filterByOrderReference_In($paymentAppPaymentStatusCriteriaTransfer->getOrderReferences());
    }
}
