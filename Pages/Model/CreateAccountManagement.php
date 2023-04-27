<?php
namespace MIT\Pages\Model;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CreateAccountManagement{

	/**
    * @var PageRepository
    */
    private $pageRepository;

    /**
    * @var SearchCriteriaBuilder
    */
    private $searchCriteriaBuilder;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageRepository $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
    *get content for HowToBuy api
    * @return array
    */
    public function getCreateAccount()
    {
		$searchCriteria = $this->searchCriteriaBuilder->addFilter('identifier', 'signup-signin','eq')->create();
		$pages = $this->pageRepository->getList($searchCriteria)->getItems();
        $currentStore = $this->storeManager->getStore();
		foreach($pages as $page){
            $pageContent = $page->getContent();
            preg_match_all( '@url=([^"]+)}}@' , $pageContent, $match );
            $src = array_pop($match);
            $image = array_unique($src);
            $imageArray = array();
            $n =0;
            $baseUrl = $currentStore->getBaseUrl();
            foreach($image as $res){
                $imageArray[$n] = $baseUrl.'media/'.$res;
                $n++;
            }

            $array = array( array() );
            $n = 0;
            foreach ( $imageArray as $image ) {               
                $array[$n]['image'] = $image;
                $n++;
            }
            $imgUrlJson = json_encode($array);
            return json_decode($imgUrlJson,true);
		}
       
    }

}