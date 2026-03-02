<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace Spryker\Zed\PaymentApp\Business\Mapper;

use Generated\Shared\Transfer\PaymentAppStatusUpdatedTransfer;
use Spryker\Shared\Kernel\Transfer\AbstractTransfer;

interface PaymentMessageMapperInterface
{
    public function mapPaymentMessageTransferToPaymentAppStatusUpdatedTransfer(AbstractTransfer $paymentAppMessageTransfer): PaymentAppStatusUpdatedTransfer;
}
