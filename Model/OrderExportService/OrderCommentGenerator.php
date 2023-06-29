<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\PlentySwissupCheckoutFields\Model\OrderExportService;

use Magento\Framework\Api\SearchCriteriaBuilder;
use SoftCommerce\Core\Framework\DataStorageInterfaceFactory;
use SoftCommerce\Core\Framework\MessageStorageInterfaceFactory;
use SoftCommerce\PlentyClient\Model\ClientConfigInterface;
use SoftCommerce\PlentyOrderProfile\Model\OrderExportService\AbstractService;
use SoftCommerce\PlentyOrderProfile\Model\OrderExportService\Processor\Order as OrderProcessor;
use SoftCommerce\PlentySwissupCheckoutFields\Model\GetCheckoutFieldsDataByOrderInterface;
use SoftCommerce\Profile\Model\ServiceAbstract\ProcessorInterface;
use Swissup\CheckoutFields\Api\Data\FieldInterface;
use Swissup\CheckoutFields\Api\Data\FieldValueInterface;

/**
 * @inheritdoc
 * Class OrderCommentGenerator used to generate
 * Swissup Checkout Fields for order comments
 */
class OrderCommentGenerator extends AbstractService implements ProcessorInterface
{
    /**
     * @var ClientConfigInterface
     */
    private ClientConfigInterface $clientConfig;

    /**
     * @var GetCheckoutFieldsDataByOrderInterface
     */
    private GetCheckoutFieldsDataByOrderInterface $getCheckoutFieldsDataByOrder;

    /**
     * @param ClientConfigInterface $clientConfig
     * @param GetCheckoutFieldsDataByOrderInterface $getCheckoutFieldsDataByOrder
     * @param DataStorageInterfaceFactory $dataStorageFactory
     * @param MessageStorageInterfaceFactory $messageStorageFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $data
     */
    public function __construct(
        ClientConfigInterface $clientConfig,
        GetCheckoutFieldsDataByOrderInterface $getCheckoutFieldsDataByOrder,
        DataStorageInterfaceFactory $dataStorageFactory,
        MessageStorageInterfaceFactory $messageStorageFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = []
    ) {
        $this->clientConfig = $clientConfig;
        $this->getCheckoutFieldsDataByOrder = $getCheckoutFieldsDataByOrder;
        parent::__construct($dataStorageFactory, $messageStorageFactory, $searchCriteriaBuilder, $data);
    }

    /**
     * @inheritDoc
     */
    public function execute(): void
    {
        $this->initialize();

        $context = $this->getContext();
        $checkoutFields = $this->getCheckoutFieldsDataByOrder->execute((int) $context->getSalesOrder()->getEntityId());
        $userId = $this->clientConfig->getUserId();

        $request = [];
        foreach ($checkoutFields as $checkoutField) {
            if (!$value = $checkoutField[FieldValueInterface::VALUE] ?? null) {
                continue;
            }

            if (isset($checkoutField[FieldInterface::FRONTEND_LABEL])) {
                $value = "<i>{$checkoutField[FieldInterface::FRONTEND_LABEL]}:</i><br>$value";
            }

            $request[] = [
                'userId' => $userId,
                "text" => $value,
                'referenceType' => 'order',
                'isVisibleForContact' => false
            ];
        }

        if ($request) {
            $context->getRequestStorage()->setData(
                array_values($request),
                [OrderProcessor::TYPE_ID, 'comments']
            );
        }

        $this->finalize();
    }
}
