<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\PlentySwissupCheckoutFields\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Swissup\CheckoutFields\Api\Data\FieldInterface;
use Swissup\CheckoutFields\Api\Data\FieldValueInterface;

/**
 * Class GetTemplateIdByOptionId
 * @package SoftCommerce\PlentySteelMan24\Model
 */
class GetCheckoutFieldsDataByOrder implements GetCheckoutFieldsDataByOrderInterface
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var string[]
     */
    private array $dataCache = [];

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    /**
     * @inheritDoc
     */
    public function execute(int $orderId): array
    {
        if (!isset($this->dataCache[$orderId])) {
            $this->dataCache[$orderId] = $this->getData($orderId);
        }
        return $this->dataCache[$orderId];
    }

    /**
     * @param int $orderId
     * @return array
     */
    private function getData(int $orderId): array
    {
        $select = $this->connection->select()
            ->from(
                ['scv' => $this->connection->getTableName('swissup_checkoutfields_values')],
                [FieldValueInterface::VALUE_ID, FieldValueInterface::VALUE]
            )
            ->joinLeft(
                ['scf' => $this->connection->getTableName('swissup_checkoutfields_field')],
                'scv.' . FieldInterface::FIELD_ID . ' = scf.' . FieldInterface::FIELD_ID,
                [FieldInterface::FRONTEND_LABEL]
            )
            ->where('scv.' . FieldValueInterface::ORDER_ID . ' = ?', $orderId);

        return $this->connection->fetchAll($select);
    }
}
