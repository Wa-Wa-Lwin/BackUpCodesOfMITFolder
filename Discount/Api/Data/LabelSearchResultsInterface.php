<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Api\Data;

interface LabelSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get label list.
     * @return \MIT\Discount\Api\Data\LabelInterface[]
     */
    public function getItems();

    /**
     * Set id list.
     * @param \MIT\Discount\Api\Data\LabelInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

