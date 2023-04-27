<?php

namespace MIT\Product\Model\Api;

use Magento\Catalog\Model\CategoryList;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use MIT\Product\Api\CustomCategoryInterface;
use MIT\Product\Model\CustomCategoryFactory;

class CustomCategory implements CustomCategoryInterface
{

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CustomCategoryFactory
     */
    protected $customCategoryFactory;

    protected $blockRepository;

    /**
     * @var FilterGroupBuilder
     */
    private $_filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

    protected $categoryList;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    private $storeManager;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CustomCategoryFactory $customCategoryFactory,
        BlockRepository $blockRepository,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        CategoryList $categoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->customCategoryFactory = $customCategoryFactory;
        $this->blockRepository = $blockRepository;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->categoryList = $categoryList;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * get category by id including child data
     * @param int $id
     * @return \MIT\Product\Api\Data\CustomCategoryManagementInterface
     */
    public function getCategoryById($id)
    {
        $factory = $this->customCategoryFactory->create();
        $model = $factory->load($id);
        return $model;
        // if ($model->getIsActive() && $model->getIncludeInMenu()) {
        //     return $model;
        // }
    }

    public function getCategoryListForHomePage(SearchCriteriaInterface $searchCriteria)
    {
        // $block = $this->blockRepository->getById($id);
        $blockContent = $this->getBlockContent();
        if (isset($blockContent)) {
            $categoryIdx = strpos($blockContent, "categories=\"");
            $content = substr($blockContent, $categoryIdx, -1);
            $subContent = substr($content, strlen('categories="'), -1);
            $result = substr($subContent, 0, strpos($subContent, '"'));
            $categoryIds = explode(',', $result);

            $filteredId = $this->_filterBuilder
                ->setConditionType('eq')
                ->setField('entity_id')
                ->setValue($categoryIds[0])
                ->create();

            $filterGroupList = [];
            $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
            $currentStore = $this->storeManager->getStore();
            $baseUrl = $currentStore->getBaseUrl();

            $searchCriteria->setFilterGroups($filterGroupList);
            $result = $this->categoryList->getList($searchCriteria);
            foreach ($result->getItems() as $item) {
                if ($item->getCustomAttributes()) {
                    foreach ($item->getCustomAttributes() as $customAttribute) {
                        if (in_array($customAttribute->getAttributeCode(), ['image', 'magepow_thumbnail'])) {
                            $customAttribute->setValue($baseUrl . $customAttribute->getValue());
                        }
                    }
                }
            }


            $dataList = $result->getItems();

            $index = 0;
            foreach($categoryIds as $id) {
                if ($index > 0) {
                    $filteredId = $this->_filterBuilder
                    ->setConditionType('eq')
                    ->setField('entity_id')
                    ->setValue($id)
                    ->create();
    
                    $filterGroupList = [];
                    $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();
                    $searchCriteria->setFilterGroups($filterGroupList);
                    $res = $this->categoryList->getList($searchCriteria);
                    if ($res->getTotalCount() > 0) {
                        foreach($res->getItems() as $item) {
                            if ($item->getCustomAttributes()) {
                                foreach ($item->getCustomAttributes() as $customAttribute) {
                                    if (in_array($customAttribute->getAttributeCode(), ['image', 'magepow_thumbnail'])) {
                                        $customAttribute->setValue($baseUrl . $customAttribute->getValue());
                                    }
                                }
                            }
                            $dataList[] = $item;
                        }
                    }
                }
                $index++;
            }
            $result->setItems($dataList);

	    return $result;
        }
    }

    private function getBlockContent()
    {
        $filteredId = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField('identifier')
            ->setValue('category_list_block')
            ->create();

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
