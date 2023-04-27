<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace MIT\Discount\Api\Data;

interface LabelImageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get label_image list.
     * @return \MIT\Discount\Api\Data\LabelImageInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \MIT\Discount\Api\Data\LabelImageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}