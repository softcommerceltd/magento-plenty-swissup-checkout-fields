<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace SoftCommerce\PlentySwissupCheckoutFields\Model;

/**
 * Interface GetCheckoutFieldsDataByOrderInterface
 * used to retrieve checkout fields in array format.
 */
interface GetCheckoutFieldsDataByOrderInterface
{
    /**
     * @param int $orderId
     * @return array
     */
    public function execute(int $orderId): array;
}
