<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\PaymentApp\Business\Mapper;

use Generated\Shared\Transfer\PaymentAppStatusUpdatedTransfer;
use Generated\Shared\Transfer\PaymentAuthorizationFailedTransfer;
use Generated\Shared\Transfer\PaymentAuthorizedTransfer;
use Generated\Shared\Transfer\PaymentCanceledTransfer;
use Generated\Shared\Transfer\PaymentCancellationFailedTransfer;
use Generated\Shared\Transfer\PaymentCapturedTransfer;
use Generated\Shared\Transfer\PaymentCaptureFailedTransfer;
use Generated\Shared\Transfer\PaymentOverpaidTransfer;
use Generated\Shared\Transfer\PaymentUnderpaidTransfer;
use InvalidArgumentException;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;
use Spryker\Shared\PaymentApp\Status\PaymentStatus;

class PaymentMessageMapper implements PaymentMessageMapperInterface
{
    /**
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $paymentAppMessageTransfer
     *
     * @throws \InvalidArgumentException
     *
     * @return \Generated\Shared\Transfer\PaymentAppStatusUpdatedTransfer
     */
    public function mapPaymentMessageTransferToPaymentAppStatusUpdatedTransfer(AbstractTransfer $paymentAppMessageTransfer): PaymentAppStatusUpdatedTransfer
    {
        return match (get_class($paymentAppMessageTransfer)) {
            PaymentAuthorizedTransfer::class => $this->mapPaymentAuthorizedToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentAuthorizationFailedTransfer::class => $this->mapPaymentAuthorizationFailedToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentCapturedTransfer::class => $this->mapPaymentCapturedToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentCaptureFailedTransfer::class => $this->mapPaymentCaptureFailedToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentCanceledTransfer::class => $this->mapPaymentCanceledToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentCancellationFailedTransfer::class => $this->mapPaymentCancellationFailedToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentOverpaidTransfer::class => $this->mapPaymentOverpaidToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            PaymentUnderpaidTransfer::class => $this->mapPaymentUnderpaidToPaymentStatusUpdatedTransfer($paymentAppMessageTransfer),
            default => throw new InvalidArgumentException(sprintf('Message type %s is not supported.', get_class($paymentAppMessageTransfer))),
        };
    }

    protected function mapPaymentAuthorizedToPaymentStatusUpdatedTransfer(
        PaymentAuthorizedTransfer $paymentAuthorizedTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentAuthorizedTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_AUTHORIZED,
        );
    }

    protected function mapPaymentAuthorizationFailedToPaymentStatusUpdatedTransfer(
        PaymentAuthorizationFailedTransfer $paymentAuthorizationFailedTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentAuthorizationFailedTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_AUTHORIZATION_FAILED,
        );
    }

    protected function mapPaymentCapturedToPaymentStatusUpdatedTransfer(
        PaymentCapturedTransfer $paymentCapturedTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentCapturedTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_CAPTURED,
        );
    }

    protected function mapPaymentCaptureFailedToPaymentStatusUpdatedTransfer(
        PaymentCaptureFailedTransfer $paymentCaptureFailedTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentCaptureFailedTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_CAPTURE_FAILED,
        );
    }

    protected function mapPaymentCanceledToPaymentStatusUpdatedTransfer(
        PaymentCanceledTransfer $paymentCanceledTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentCanceledTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_CANCELED,
        );
    }

    protected function mapPaymentCancellationFailedToPaymentStatusUpdatedTransfer(
        PaymentCancellationFailedTransfer $paymentCancellationFailedTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentCancellationFailedTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_CANCELLATION_FAILED,
        );
    }

    protected function mapPaymentOverpaidToPaymentStatusUpdatedTransfer(
        PaymentOverpaidTransfer $paymentOverpaidTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentOverpaidTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_OVERPAID,
        );
    }

    protected function mapPaymentUnderpaidToPaymentStatusUpdatedTransfer(
        PaymentUnderpaidTransfer $paymentUnderpaidTransfer
    ): PaymentAppStatusUpdatedTransfer {
        return $this->createPaymentAppStatusUpdatedTransfer(
            $paymentUnderpaidTransfer->getOrderReferenceOrFail(),
            PaymentStatus::STATUS_UNDERPAID,
        );
    }

    protected function createPaymentAppStatusUpdatedTransfer(string $orderReference, string $paymentStatus): PaymentAppStatusUpdatedTransfer
    {
        $paymentAppStatusUpdatedTransfer = new PaymentAppStatusUpdatedTransfer();
        $paymentAppStatusUpdatedTransfer
            ->setOrderReference($orderReference)
            ->setStatus($paymentStatus);

        return $paymentAppStatusUpdatedTransfer;
    }
}
