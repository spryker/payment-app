<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types = 1);

namespace SprykerTest\Shared\PaymentApp\Helper;

use Codeception\Module;
use Codeception\Stub;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\AcpHttpResponseTransfer;
use Generated\Shared\Transfer\EndpointTransfer;
use Generated\Shared\Transfer\PaymentAppPaymentStatusTransfer;
use Generated\Shared\Transfer\PaymentCustomerRequestTransfer;
use Generated\Shared\Transfer\PaymentMethodAppConfigurationTransfer;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Generated\Shared\Transfer\PaymentProviderTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\PreOrderPaymentRequestTransfer;
use Orm\Zed\PaymentApp\Persistence\SpyPaymentAppPaymentStatus;
use Orm\Zed\PaymentApp\Persistence\SpyPaymentAppPaymentStatusHistoryQuery;
use Orm\Zed\PaymentApp\Persistence\SpyPaymentAppPaymentStatusQuery;
use Propel\Runtime\Collection\Collection;
use Ramsey\Uuid\Uuid;
use Spryker\Zed\KernelApp\Business\KernelAppFacadeInterface;
use Spryker\Zed\Payment\Business\PaymentFacade;
use Spryker\Zed\PaymentApp\Dependency\Facade\PaymentAppToKernelAppFacadeBridge;
use Spryker\Zed\PaymentApp\PaymentAppConfig;
use Spryker\Zed\PaymentApp\PaymentAppDependencyProvider;
use SprykerTest\Shared\Payment\Helper\PaymentDataHelperTrait;
use SprykerTest\Shared\Testify\Helper\DependencyHelperTrait;

class PaymentAppHelper extends Module
{
    use PaymentDataHelperTrait;
    use DependencyHelperTrait;

    public function havePaymentMethodWithoutPaymentMethodAppConfigurationPersisted(): PaymentMethodTransfer
    {
        $seedData = $this->getDefaultPaymentMethodSeedData();

        return $this->getPaymentDataHelper()->havePaymentMethodWithPaymentProviderPersisted($seedData);
    }

    public function havePaymentMethodWithPaymentMethodAppConfigurationPersisted(array $paymentMethodAppConfigurationSeed = []): PaymentMethodTransfer
    {
        $seedData = $this->getDefaultPaymentMethodSeedData();
        $seedData[PaymentMethodTransfer::PAYMENT_METHOD_APP_CONFIGURATION] = $paymentMethodAppConfigurationSeed;

        return $this->getPaymentDataHelper()->havePaymentMethodWithPaymentProviderPersisted($seedData);
    }

    public function havePaymentMethodWithPaymentMethodAppConfigurationForCustomerEndpointPersisted(): PaymentMethodTransfer
    {
        $seedData = $this->getDefaultPaymentMethodSeedData();
        $seedData += [
            PaymentMethodTransfer::PAYMENT_METHOD_APP_CONFIGURATION => [
                PaymentMethodAppConfigurationTransfer::BASE_URL => 'http://foo.bar',
                PaymentMethodAppConfigurationTransfer::ENDPOINTS => [
                    [
                        EndpointTransfer::NAME => PaymentAppConfig::PAYMENT_SERVICE_PROVIDER_ENDPOINT_NAME_CUSTOMER,
                        EndpointTransfer::PATH => '/customer',
                    ],
                ],
            ],
        ];

        return $this->getPaymentDataHelper()->havePaymentMethodWithPaymentProviderPersisted($seedData);
    }

    protected function getDefaultPaymentMethodSeedData(): array
    {
        $paymentMethodName = 'method-' . Uuid::uuid4()->toString();
        $paymentProviderKey = 'provider-' . Uuid::uuid4()->toString();

        return [
            PaymentProviderTransfer::PAYMENT_PROVIDER_KEY => $paymentProviderKey,
            PaymentMethodTransfer::IS_ACTIVE => true,
            PaymentMethodTransfer::PAYMENT_METHOD_KEY => (new PaymentFacade())->generatePaymentMethodKey($paymentProviderKey, $paymentMethodName),
            PaymentMethodTransfer::NAME => $paymentMethodName,
            PaymentMethodTransfer::PAYMENT_PROVIDER => [
                PaymentProviderTransfer::NAME => $paymentProviderKey,
                PaymentProviderTransfer::PAYMENT_PROVIDER_KEY => $paymentProviderKey,
            ],
        ];
    }

    public function havePaymentCustomerRequestTransfer(?PaymentMethodTransfer $paymentMethodTransfer = null): PaymentCustomerRequestTransfer
    {
        $paymentMethodName = $this->getPaymentMethodName($paymentMethodTransfer);
        $paymentProviderName = $this->getPaymentProviderName($paymentMethodTransfer);

        $paymentTransfer = new PaymentTransfer();
        $paymentTransfer
            ->setPaymentMethodName($paymentMethodName)
            ->setPaymentProviderName($paymentProviderName);

        $paymentCustomerRequestTransfer = new PaymentCustomerRequestTransfer();
        $paymentCustomerRequestTransfer
            ->setPayment($paymentTransfer)
            ->setCustomerPaymentServiceProviderData([
                'foo' => 'bar',
            ]);

        return $paymentCustomerRequestTransfer;
    }

    protected function getPaymentMethodName(?PaymentMethodTransfer $paymentMethodTransfer = null): string
    {
        if ($paymentMethodTransfer && $paymentMethodTransfer->getName()) {
            return $paymentMethodTransfer->getName();
        }

        return 'method-' . Uuid::uuid4()->toString();
    }

    protected function getPaymentProviderName(?PaymentMethodTransfer $paymentMethodTransfer = null): string
    {
        if ($paymentMethodTransfer && $paymentMethodTransfer->getPaymentProvider() && $paymentMethodTransfer->getPaymentProvider()->getName()) {
            return $paymentMethodTransfer->getPaymentProvider()->getName();
        }

        return 'provider-' . Uuid::uuid4()->toString();
    }

    public function havePreOrderPaymentRequestTransferWithoutQuote(): PreOrderPaymentRequestTransfer
    {
        return new PreOrderPaymentRequestTransfer();
    }

    public function havePreOrderPaymentRequestTransferWithQuote(): PreOrderPaymentRequestTransfer
    {
        $quoteBuilder = new QuoteBuilder();
        $quoteTransfer = $quoteBuilder->withItem()->build();

        $preOrderPaymentRequestTransfer = new PreOrderPaymentRequestTransfer();
        $preOrderPaymentRequestTransfer->setQuote($quoteTransfer);

        return $preOrderPaymentRequestTransfer;
    }

    public function mockKernelAppFacadeResponse(int $expectedResponseCode, array|string $expectedResponseData): void
    {
        // Mock the KernelApp response
        $kernelAppFacadeMock = Stub::makeEmpty(KernelAppFacadeInterface::class, [
            'makeRequest' => function () use ($expectedResponseCode, $expectedResponseData) {
                $acpHttpResponseTransfer = new AcpHttpResponseTransfer();
                $acpHttpResponseTransfer
                    ->setHttpStatusCode($expectedResponseCode)
                    ->setContent(is_string($expectedResponseData) ? $expectedResponseData : json_encode($expectedResponseData));

                return $acpHttpResponseTransfer;
            },
        ]);

        $this->getDependencyHelper()->setDependency(PaymentAppDependencyProvider::FACADE_KERNEL_APP, new PaymentAppToKernelAppFacadeBridge($kernelAppFacadeMock));
    }

    public function havePaymentAppPaymentStatusEntity(string $orderReference, string $status): PaymentAppPaymentStatusTransfer
    {
        $paymentAppPaymentStatusEntity = new SpyPaymentAppPaymentStatus();
        $paymentAppPaymentStatusEntity
            ->setOrderReference($orderReference)
            ->setStatus($status)
            ->save();

        $paymentAppPaymentStatusTransfer = new PaymentAppPaymentStatusTransfer();
        $paymentAppPaymentStatusTransfer->fromArray($paymentAppPaymentStatusEntity->toArray(), true);

        return $paymentAppPaymentStatusTransfer;
    }

    public function assertPaymentAppPaymentStatusEntityExists(string $orderReference, string $expectedStatus): void
    {
        $paymentAppPaymentStatusQuery = SpyPaymentAppPaymentStatusQuery::create();
        $paymentAppPaymentStatusEntity = $paymentAppPaymentStatusQuery
            ->filterByOrderReference($orderReference)
            ->findOne();

        $this->assertInstanceOf(SpyPaymentAppPaymentStatus::class, $paymentAppPaymentStatusEntity);
        $this->assertSame($expectedStatus, $paymentAppPaymentStatusEntity->getStatus());
    }

    public function assertPaymentAppPaymentStatusHistoryEntityExists(string $orderReference, string $expectedStatus): void
    {
        $paymentAppPaymentStatusHistoryQuery = SpyPaymentAppPaymentStatusHistoryQuery::create();
        $paymentAppPaymentStatusHistoryEntityCollection = $paymentAppPaymentStatusHistoryQuery
            ->filterByOrderReference($orderReference)
            ->find();

        $this->assertInstanceOf(Collection::class, $paymentAppPaymentStatusHistoryEntityCollection);

        $found = false;

        foreach ($paymentAppPaymentStatusHistoryEntityCollection as $paymentAppPaymentStatusHistoryEntity) {
            if ($paymentAppPaymentStatusHistoryEntity->getStatus() !== $expectedStatus) {
                continue;
            }

            $found = true;
        }

        $this->assertTrue($found, sprintf('Expected to find a history entity with status "%s" but it was not found.', $expectedStatus));
    }

    public function assertPaymentAppPaymentStatusHistoryEntityDoesNotExist(string $orderReference): void
    {
        $paymentAppPaymentStatusHistoryQuery = SpyPaymentAppPaymentStatusHistoryQuery::create();
        $paymentAppPaymentStatusHistoryEntityCollection = $paymentAppPaymentStatusHistoryQuery
            ->filterByOrderReference($orderReference)
            ->findOne();

        $this->assertNull($paymentAppPaymentStatusHistoryEntityCollection, 'Did not expected to find a history entity but it was found.');
    }

    public function assertPaymentAppPaymentStatusEntityDoesNotExists(string $orderReference, string $status): void
    {
        $paymentAppPaymentStatusQuery = SpyPaymentAppPaymentStatusQuery::create();
        $paymentAppPaymentStatusEntity = $paymentAppPaymentStatusQuery
            ->filterByOrderReference($orderReference)
            ->filterByStatus($status)
            ->findOne();

        $this->assertNull($paymentAppPaymentStatusEntity);
    }
}
