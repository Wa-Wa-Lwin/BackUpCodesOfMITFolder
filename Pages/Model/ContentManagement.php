<?php

namespace MIT\Pages\Model;

use Magento\Cms\Model\PageRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ContentManagement
{

    /**
    * @var PageRepository
    */
    private $pageRepository;

    /**
    * @var SearchCriteriaBuilder
    */
    private $searchCriteriaBuilder;

    public function __construct(
        PageRepository $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
    *get content for HowToBuy api
    * @return array
    */
    public function getContent()
    {
		$searchCriteria = $this->searchCriteriaBuilder->addFilter('identifier', 'how-to-buy','eq')->create();
		$pages = $this->pageRepository->getList($searchCriteria)->getItems();
		foreach($pages as $page){
			return [['content' => $page->getContent()]];
		}
		return true;     
    }

}
