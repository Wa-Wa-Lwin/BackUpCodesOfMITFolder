<?php

namespace MIT\Product\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use MIT\Product\Model\Api\CustomLayerNavigationFactory;
use Magento\Framework\App\RequestInterface;

class ShoppingOptionsFilter extends AbstractHelper
{
    /**
     * @var CustomLayerNavigationFactory
     */
    private $customLayerNavigationFactory;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * @var CustomBuilder
     */
    private $customBuilder;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\FilterableAttributeListFactory
     */
    private $filterableAttributeListFactory;

    /**
     * @var \Magento\Catalog\Model\Layer\ResolverFactory
     */
    private $resolverFactory;

    /**
     * @var \MIT\Product\Model\Layer\FilterListFactory
     */
    private $filterListFactory;

    /**
     * @var RequestInterface
     */
    private $requestInterface;

    public function __construct(
        CustomLayerNavigationFactory $customLayerNavigationFactory,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomBuilder $customBuilder,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeListFactory $filterableAttributeListFactory,
        \Magento\Catalog\Model\Layer\ResolverFactory $resolverFactory,
        \MIT\Product\Model\Layer\FilterListFactory $filterListFactory,
        RequestInterface $requestInterface
    ) {
        $this->customLayerNavigationFactory = $customLayerNavigationFactory;
        $this->_filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customBuilder = $customBuilder;
        $this->filterableAttributeListFactory = $filterableAttributeListFactory;
        $this->resolverFactory = $resolverFactory;
        $this->filterListFactory = $filterListFactory;
        $this->requestInterface = $requestInterface;
    }

    /**
     * get modified search criteria for layer navigation filter api
     * @param SearchCriteriaInterface $searchCriteria
     * @return array
     */
    public function getModifiedSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        $navigationList = [];
        $removeList = [];
        $total = 0;
        $curPage = 0;
        $isCategoryExist = false;
        $categoryId = $this->getCategoryIdFromFilter($searchCriteria);
        if ($categoryId > 0) {
            $layerNavigationList = $this->customLayerNavigationFactory->create()->getLayerNavigationByCategoryId($categoryId);
            foreach ($layerNavigationList as $navigation) {
                $navigationList[] = $navigation['code'];
            }
            $navigationList[] = 'category_id';

            $filterData = [];

            foreach ($searchCriteria->getFilterGroups() as $key => $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    if (in_array($filter->getField(), $navigationList)) {
                        if ($filter->getField() == 'category_id') {
                            $filterData['cat'] = $filter->getValue();
                        } else {
                            $filterData[$filter->getField()] = $filter->getValue();
                        }
                    } else {
                        $this->_searchCriteriaBuilder->addFilter($filter);
                    }
                }
            }

            $filterableAttributes = $this->filterableAttributeListFactory->create();
            $filterList = $this->filterListFactory->create([
                'filterableAttributes' => $filterableAttributes
            ]);
            $layer = $this->resolverFactory->create()->get();
            $layer->setCurrentCategory($categoryId);


            var_dump($filterData);

            $filters = $filterList->getFilters($layer);
            foreach ($filters as $filter) {
                $filter->apply($this->requestInterface->setParams($filterData));
            }
            $layer->apply();

            $productIdArr = [];
            $collection = $layer->getProductCollection();
            $total = $collection->getSize();
            foreach($collection as $item) {
                $productIdArr[] = $item->getId();
            }

            if (count($productIdArr) > 0) {
                $filterIds = $this->_filterBuilder
                    ->setConditionType('in')
                    ->setField('entity_id')
                    ->setValue($productIdArr)
                    ->create();

                $this->_searchCriteriaBuilder->addFilter($filterIds);
            }

            $curPage = $searchCriteria->getCurrentPage();
            

            if ($searchCriteria->getSortOrders()) {
                foreach ($searchCriteria->getSortOrders() as $sort) {
                    $this->_searchCriteriaBuilder->addSortOrder($sort->getField(), $sort->getDirection());
                }
            }
            

            $this->_searchCriteriaBuilder->setPageSize($searchCriteria->getPageSize());
            $this->_searchCriteriaBuilder->setCurrentPage($searchCriteria->getCurrentPage());
            return ['search' => $this->_searchCriteriaBuilder->create(), 'total' => $total, 'curPage' => $curPage];
        } else {
            return ['search' => $searchCriteria, 'total' => $total, 'curPage' => $curPage];
        }
    }

    /**
     * get category id from filter
     * @param SearchCriteriaInterface $searchCriteria
     * @return int
     */
    private function getCategoryIdFromFilter(SearchCriteriaInterface $searchCriteria)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() == 'category_id') {
                    // echo $filter->getValue();
                    return $filter->getValue();
                }
            }
        }
        return 0;
    }
}
