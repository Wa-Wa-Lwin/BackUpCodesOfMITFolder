<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MIT\Product\Model;

use Magento\Framework\Api\SearchResults;
use MIT\Product\Api\Data\ReviewManagementSearchResultsInterface;

/**
 * Service Data Object with Product search results.
 */
class ReviewManagementSearchResults extends SearchResults implements ReviewManagementSearchResultsInterface
{
}
