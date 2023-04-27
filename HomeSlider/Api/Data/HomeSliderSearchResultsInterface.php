<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\HomeSlider\Api\Data;

interface HomeSliderSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get HomeSlider list.
     * @return \MIT\HomeSlider\Api\Data\HomeSliderInterface[]
     */
    public function getItems();

    /**
     * Set Slider list.
     * @param \MIT\HomeSlider\Api\Data\HomeSliderInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
