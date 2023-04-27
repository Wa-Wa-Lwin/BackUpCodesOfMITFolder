<?php

namespace MIT\Product\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Api\Search\SearchCriteriaInterface;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\Search\Request\Builder;
use Magento\Framework\Search\SearchEngineInterface;
use Magento\Framework\Search\SearchResponseBuilder;

class CustomBuilder extends AbstractHelper
{

    /**
     * @var Builder
     */
    private $requestBuilder;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var SearchEngineInterface
     */
    private $searchEngine;

    /**
     * @var SearchResponseBuilder
     */
    private $searchResponseBuilder;

    /**
     * @param Builder $requestBuilder
     * @param ScopeResolverInterface $scopeResolver
     * @param SearchEngineInterface $searchEngine
     * @param SearchResponseBuilder $searchResponseBuilder
     */
    public function __construct(
        Builder $requestBuilder,
        ScopeResolverInterface $scopeResolver,
        SearchEngineInterface $searchEngine,
        SearchResponseBuilder $searchResponseBuilder
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->scopeResolver = $scopeResolver;
        $this->searchEngine = $searchEngine;
        $this->searchResponseBuilder = $searchResponseBuilder;
    }

    /**
     * @inheritdoc
     */
    public function search(SearchCriteriaInterface $searchCriteria)
    {
        $this->requestBuilder->setRequestName($searchCriteria->getRequestName());

        $scope = $this->scopeResolver->getScope()->getId();
        $this->requestBuilder->bindDimension('scope', $scope);

        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $this->addFieldToFilter($filter->getField(), $filter->getValue());
            }
        }

        if ($searchCriteria->getCurrentPage() > 1) {
            $this->requestBuilder->setFrom(($searchCriteria->getCurrentPage() - 1) * $searchCriteria->getPageSize());
        }
        $this->requestBuilder->setSize($searchCriteria->getPageSize());

        /**
         * This added in Backward compatibility purposes.
         * Temporary solution for an existing API of a fulltext search request builder.
         * It must be moved to different API.
         * Scope to split Search request builder API in MC-16461.
         */
        if (method_exists($this->requestBuilder, 'setSort')) {
            $this->requestBuilder->setSort($searchCriteria->getSortOrders());
        }
        $request = $this->requestBuilder->create();
        $searchResponse = $this->searchEngine->search($request);

        $data = $this->searchResponseBuilder->build($searchResponse)
            ->setSearchCriteria($searchCriteria);


        $idList = [];
        foreach ($data->getItems() as $document) {
            $idList[] = $document->getId();
        }

        return $data;
    }

    /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param string|array|null $condition
     * @return $this
     */
    private function addFieldToFilter($field, $condition = null)
    {
        if (!is_array($condition) || !in_array(key($condition), ['from', 'to'], true)) {
            $this->requestBuilder->bind($field, $condition);
        } else {
            if (!empty($condition['from'])) {
                $this->requestBuilder->bind("{$field}.from", $condition['from']);
            }
            if (!empty($condition['to'])) {
                $this->requestBuilder->bind("{$field}.to", $condition['to']);
            }
        }

        return $this;
    }
}
