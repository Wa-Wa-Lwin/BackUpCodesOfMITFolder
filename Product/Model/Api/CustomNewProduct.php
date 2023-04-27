<?php

namespace MIT\Product\Model\Api;

use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use MIT\Product\Api\CustomNewProductInterface;
use MIT\Product\Api\Data\CustomProductSearchResultsInterface;

class CustomNewProduct implements CustomNewProductInterface
{
    /**
     * @var BlockRepository
     */
    protected $blockRepository;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomProduct
     */
    private $customProduct;

    /**
     * @var CustomProductSearchResultsInterface
     */
    private $customProductSearchResultsInterface;

    public function __construct(
        BlockRepository $blockRepository,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CustomProduct $customProduct,
        CustomProductSearchResultsInterface $customProductSearchResultsInterface
    ) {
        $this->blockRepository = $blockRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->customProduct = $customProduct;
        $this->customProductSearchResultsInterface = $customProductSearchResultsInterface;
    }

    public function getNewProductList(SearchCriteriaInterface $searchCriteria)
    {
        // $block = $this->blockRepository->getById($id);
        $blockContent = $this->getBlockContent();
        if (isset($blockContent)) {

            $start = strpos($blockContent, 'attribute');
            $end = strpos($blockContent, '^]^]');
            $result = substr($blockContent, $start + 11, $end - (11 + $start));
            $result = str_replace('`', '', $result);
            $result = str_replace('value:', '', $result);
            $resultList = explode(',', $result);
            $attribute = array_shift($resultList);
            

            if ($attribute == 'sku') {
                $filteredId = $this->_filterBuilder
                ->setConditionType('in')
                ->setField('sku')
                ->setValue($resultList)
                ->create();

                $filterGroupList = $searchCriteria->getFilterGroups();
                $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                
                $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList)->setSortOrders($searchCriteria->getSortOrders());
                


            } else if ($attribute == 'category_ids') {
                $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('category_id')
                ->setValue($resultList[0])
                ->create();

                $filterGroupList = $searchCriteria->getFilterGroups();
                $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                
                $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList)->setSortOrders($searchCriteria->getSortOrders());
                
            }
            $this->_searchCriteriaBuilder->setPageSize($searchCriteria->getPageSize())->setCurrentPage($searchCriteria->getCurrentPage());
            return $this->customProduct->getList($this->_searchCriteriaBuilder->create());
        }
        return $this->customProductSearchResultsInterface->create();
    }

    private function getBlockContent()
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        if ($storeCode == 'mm') {
            $filteredId = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('identifier')
            ->setValue('new_product')
            ->create();
        } else {
            $filteredId = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('identifier')
            ->setValue('new_product')
            ->create();
        }
       

        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $blockData = $this->blockRepository->getList($this->_searchCriteriaBuilder->create());
        foreach ($blockData->getItems() as $data) {
            if ($data) {
                return $data->getContent();
            }
        }
        return '';
    }
}