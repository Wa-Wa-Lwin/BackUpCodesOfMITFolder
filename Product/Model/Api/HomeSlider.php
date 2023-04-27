<?php

namespace MIT\Product\Model\Api;

use Magento\Store\Model\StoreManagerInterface;
use MIT\CatalogRule\Helper\CustomHTMLEditor;
use MIT\CatalogRule\Helper\HomeSliderGenerator;
use MIT\Product\Api\HomeSliderInterface;

class HomeSlider implements HomeSliderInterface
{
    /**
     * cms block identifier for home slider
     */
    const HOME_SLIDER_BLOCK_IDENTIFIER = ['et_home_slider', 'et_home_slider_mm', 'home_slider_one', 'home_slider_one_mm'];

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $category;

    /**
     * @var HomeSliderGenerator
     */
    private $homeSliderHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var CustomHTMLEditor
     */
    private $customHtmlEditor;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        HomeSliderGenerator $homeSliderGenerator,
        StoreManagerInterface $storeManagerInterface,
        CustomHTMLEditor $customHTMLEditor
    ) {
        $this->category = $categoryFactory;
        $this->homeSliderHelper = $homeSliderGenerator;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->customHtmlEditor = $customHTMLEditor;
    }

    public function getHomeSlider($type)
    {
        $blockContent = '';
        $result = [];
        $currentStore = $this->storeManagerInterface->getStore();
        $baseUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if ($currentStore->getId() > 1) {
            if ($type == 1) {
                $blockContent = $this->homeSliderHelper->getBlockContent('identifier', self::HOME_SLIDER_BLOCK_IDENTIFIER[3]);
            } else {
                $blockContent = $this->homeSliderHelper->getBlockContent('identifier', self::HOME_SLIDER_BLOCK_IDENTIFIER[1]);
            }
        } else {
            if ($type == 1) {
                $blockContent = $this->homeSliderHelper->getBlockContent('identifier', self::HOME_SLIDER_BLOCK_IDENTIFIER[2]);
            } else {
                $blockContent = $this->homeSliderHelper->getBlockContent();
            }
        }
        if (isset($blockContent)) {
            $blockContent = $blockContent->getContent();
            $imageArr = $this->customHtmlEditor->getCustomData($blockContent, 'store url=', '}}', 'media url=', '}}', []);

            if (count($imageArr) > 0) {
                for ($x = 0; $x < count($imageArr); $x += 4) {
                    $categories = $this->category->create()
                        ->getCollection()
                        ->addAttributeToFilter('url_path', str_replace("'", '', str_replace(".html'", '', str_replace($currentStore->getBaseUrl(), '', $imageArr[$x]))))
                        ->addAttributeToSelect('*');

                    $result[] = array(
                        'web_img' => str_replace("'", '', $baseUrl . $imageArr[$x + 1]),
                        'mobile_img' => str_replace("'", '', $baseUrl . $imageArr[$x + 2]),
                        'category_id' => $categories->getFirstItem()->getId(),
                    );
                }
            }
        }
        return $result;
    }
}
