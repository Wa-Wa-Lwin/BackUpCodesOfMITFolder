<?php

namespace MIT\Product\Model\Api;

use Magento\Framework\App\RequestInterface;
use MIT\Product\Api\CustomLayerNavigationInterface;

class CustomLayerNavigation implements CustomLayerNavigationInterface
{
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
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeListFactory $filterableAttributeListFactory,
        \Magento\Catalog\Model\Layer\ResolverFactory $resolverFactory,
        \MIT\Product\Model\Layer\FilterListFactory $filterListFactory,
        RequestInterface $requestInterface
    ) {
        $this->filterableAttributeListFactory = $filterableAttributeListFactory;
        $this->resolverFactory = $resolverFactory;
        $this->filterListFactory = $filterListFactory;
        $this->requestInterface = $requestInterface;
    }

    /**
     * @inheritDoc
     */
    public function getLayerNavigationByCategoryId($categoryId)
    {
        $filterableAttributes = $this->filterableAttributeListFactory->create();
        $filterList = $this->filterListFactory->create([
            'filterableAttributes' => $filterableAttributes
        ]);

        $layer = $this->resolverFactory->create()->get();
        $layer->setCurrentCategory($categoryId);
        $filters = $filterList->getFilters($layer);
        return $this->generateLayerNavigationFilter($filters);
    }

    /**
     * @inheritDoc
     */
    public function getLayerNavigationByCategoryIdAndActivFilters($categoryId, array $items)
    {
        $filterableAttributes = $this->filterableAttributeListFactory->create();
        $filterList = $this->filterListFactory->create([
            'filterableAttributes' => $filterableAttributes
        ]);
        $layer = $this->resolverFactory->create()->get();
        $layer->setCurrentCategory($categoryId);

        $activeFilterArr = [];
        if (count($items) > 0) {
            /** @var \MIT\Product\Model\CustomKeyVal $item */
            foreach ($items as $item) {
                $activeFilterArr[$item->getKey()] = $item->getValue();
            }

            $filters = $filterList->getFilters($layer);
            foreach ($filters as $filter) {
                $filter->apply($this->requestInterface->setParams($activeFilterArr));
            }
            $layer->apply();
        }

        $filters = $filterList->getFilters($layer);
        return $this->generateLayerNavigationFilter($filters);
    }

    /**
     * generate filter list
     * @param array|Filter\AbstractFilter[] $filters
     * @return array
     */
    private function generateLayerNavigationFilter($filters)
    {
        $i = 0;
        $filterArray = [];
        foreach ($filters as $filter) {
            $availablefilter = $filter->getRequestVar(); //Gives Display Name of the filter such as Category,Price etc.
            $items = $filter->getItems(); //Gives all available filter options in that particular filter
            $filterValues = array();
            $j = 0;
            foreach ($items as $item) {
                $filterValues[$j]['display'] = strip_tags($item->getLabel());
                $filterValues[$j]['value']   = $item->getValue();
                $filterValues[$j]['count']   = $item->getCount(); //Gives no. of products in each filter options
                $j++;
            }
            if (!empty($filterValues) && count($filterValues) > 0) {
                $filterArray['availablefilter'][$availablefilter] =  $filterValues;
            }
            $i++;
        }

        $data = [];
        foreach ($filterArray as $filter) {
            foreach ($filter as $key => $value) {
                $data[] = array('name' => $this->getFilterName($filters, $key), 'code' => $key, 'items' => $value);
            }
        }
        return $data;
    }

    /**
     * retrieve filter name from filter list
     * @param array|Filter\AbstractFilter[] $filters
     * @param string $code
     * @return string
     */
    private function getFilterName($filters, $code)
    {
        foreach ($filters as $filter) {
            if ($filter->getRequestVar() == $code) {
                return (string)$filter->getName();
            }
        }
        return '';
    }
}
