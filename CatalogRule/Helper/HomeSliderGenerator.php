<?php

namespace MIT\CatalogRule\Helper;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;

use function PHPSTORM_META\type;

class HomeSliderGenerator extends AbstractHelper
{
    const SLIDER_SAMPLE =
    '
    <div class="item promo-[[code]]">
        <div class="item-inner">
            <a href="{{store url=\'[[url]]\'}}">
                <picture>
                    <source media="(min-width:541px)" srcset="{{media url=\'[[imgUrl]]\'}}">
                    <source media="(min-width:105px)" srcset="{{media url=\'[[mobileImgUrl]]\'}}">
                    <img src="{{media url=\'[[imgUrl]]\'}}" alt="image">
                </picture>
            </a>
        </div>
    </div>
    ';

    const SLIDER_SAMPLE_NON_CATEGORY =  '
    <div class="item promo-[[code]]">
        <div class="item-inner">
                <picture>
                    <source media="(min-width:541px)" srcset="{{media url=\'[[imgUrl]]\'}}">
                    <source media="(min-width:105px)" srcset="{{media url=\'[[mobileImgUrl]]\'}}">
                    <img src="{{media url=\'[[imgUrl]]\'}}" alt="image">
                </picture>
        </div>
    </div>
    ';

    const PROMO_SAMPLE =
    '
    <div class="item promo-[[code]]">
        <div class="item-inner promoslide">
            <a href="{{store url=\'[[url]]\'}}">
                <img src="{{media url=\'[[imgUrl]]\'}}" alt="image" />
            </a>
        </div>
    </div>
    ';

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
     * @var BlockFactory
     */
    private $blockFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * @var CustomHTMLEditor
     */
    private $customHtmlEditor;

    public function __construct(
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        StoreManagerInterface $storeManagerInterface,
        CustomHTMLEditor $customHTMLEditor
    ) {
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->blockFactory = $blockFactory;
        $this->blockRepository = $blockRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->customHtmlEditor = $customHTMLEditor;
    }

    /**
     * read home slider sample code and replace or add dynamic data
     * @param string $fieldName
     * @param string $filedVal
     * @param int $code
     * @param string $imgUrl
     * @param string $discountPercentage
     * @param string $categoryName
     * @param string $url
     * @param int $categoryId
     */
    public function generateAndUpdateHomeSlider($fieldName, $fieldVal, $code, $imgUrl, $mobileImgUrl, $url = '#',$categoryId)
    {
       
        $blockData = $this->getBlockContent($fieldName, $fieldVal);
        if ($blockData) {
            if ($this->customHtmlEditor->checkSlideExist($blockData->getContent(), 'promo-' . $code . '"')) {
                $sliderUrlArr = $this->customHtmlEditor->retrieveDataFromBlockContent($blockData->getContent(), 'store url=', '}}', 'media url=', '}}', 'promo-' . $code);
                $sliderUrlArrNoneCategory = $this->customHtmlEditor->retrieveDataFromBlockContent($blockData->getContent(), 'media url=', '}}', 'media url=', '}}', 'promo-' . $code);
                if (count($sliderUrlArr) == 4 && $categoryId) {
                    $contents = str_replace($sliderUrlArr[0], "'" . $url . "'", $blockData->getContent());                    
                    $contents = str_replace($sliderUrlArr[1], "'" . $imgUrl, $contents);
                    $contents = str_replace($sliderUrlArr[2], "'" . $mobileImgUrl, $contents);
                    $contents = str_replace($sliderUrlArr[3], "'" . $imgUrl, $contents);
                    $this->updateBlockByBlockId($blockData->getId(), $contents);
                }else if($categoryId == 0 && count($sliderUrlArrNoneCategory) == 3){
                    $contents = str_replace($sliderUrlArrNoneCategory[0], "'" . $imgUrl, $blockData->getContent());
                    $contents = str_replace($sliderUrlArrNoneCategory[1], "'" . $mobileImgUrl, $contents);
                    $contents = str_replace($sliderUrlArrNoneCategory[2], "'" . $imgUrl, $contents);
                    $this->updateBlockByBlockId($blockData->getId(), $contents);
                }
                else{
                    $updatedContent = $this->customHtmlEditor->removeSliderByPromoCodeId(
                        $blockData->getContent(),
                        '<div class="item promo-' . $code . '"',
                        '</div>',
                        0,
                        2
                    );
                    $pos = strpos($updatedContent, 'item promo-');
                    if ($pos === false) {
                        $updatedContent = '';
                    }
                    if($categoryId){
                        $contents = str_replace('[[code]]', $code, self::SLIDER_SAMPLE);
                       }else{
                        $contents = str_replace('[[code]]', $code, self::SLIDER_SAMPLE_NON_CATEGORY);
                       }
                        $contents = str_replace('[[url]]', $url, $contents);
                        $contents = str_replace('[[imgUrl]]', $imgUrl, $contents);
                        $contents = str_replace('[[mobileImgUrl]]', $mobileImgUrl, $contents);

                        $existedContent = $updatedContent;

                        if ($existedContent && $this->customHtmlEditor->checkSlideExist($updatedContent, 'promo-')) {
                            $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                            $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                            $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                            $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                        } else {
                            if ($fieldVal == 'et_home_slider' || $fieldVal == 'et_home_slider_mm') {
                                $existedContent = '
        <div data-content-type="html" data-appearance="default" data-element="main">
            <div class="slider-wrapper">
                <div class="content-main home-autoslide">
                    <div id="home-slider" class="slides owl-carousel owl-theme">
                            ';
                            } else {
                                $existedContent = '
        <div data-content-type="html" data-appearance="default" data-element="main">
            <div class="slider-wrapper">
                <div class="content-main ">
                    <div id="home-slider-one" class="slides owl-carousel owl-theme">
                            ';
                            }
                        }
        
                        $existedContent .= '
                                    ' . $contents .
                            '
                    </div>
                </div>
            </div>
        </div>
                                        ';
        
                        $this->updateBlockByBlockId($blockData->getId(), $existedContent);

                }
            } else {
               if($categoryId){
                $contents = str_replace('[[code]]', $code, self::SLIDER_SAMPLE);
               }else{
                $contents = str_replace('[[code]]', $code, self::SLIDER_SAMPLE_NON_CATEGORY);
               }
                $contents = str_replace('[[url]]', $url, $contents);
                $contents = str_replace('[[imgUrl]]', $imgUrl, $contents);
                $contents = str_replace('[[mobileImgUrl]]', $mobileImgUrl, $contents);

                $existedContent = $blockData->getContent();

                if ($existedContent && $this->customHtmlEditor->checkSlideExist($blockData->getContent(), 'promo-')) {
                    $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                    $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                    $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                    $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                } else {
                if ($fieldVal == 'et_home_slider' || $fieldVal == 'et_home_slider_mm') {
                    $existedContent = '
<div data-content-type="html" data-appearance="default" data-element="main">
<div class="slider-wrapper">
    <div class="content-main home-autoslide">
        <div id="home-slider" class="slides owl-carousel owl-theme">
                ';
                } else {
                    $existedContent = '
<div data-content-type="html" data-appearance="default" data-element="main">
<div class="slider-wrapper">
    <div class="content-main ">
        <div id="home-slider-one" class="slides owl-carousel owl-theme">
                ';
                }
                }

                $existedContent .= '
                            ' . $contents .
                    '
            </div>
        </div>
    </div>
</div>
                                ';

                $this->updateBlockByBlockId($blockData->getId(), $existedContent);
            }
        }
    }

    /**
     * read promo slider sample and replace or add promo slider dynamic data
     * @param string $fieldName
     * @param string $fieldVal
     * @param int $code
     * @param string $url
     * @param string $imgUrl
     */
    public function generateAndUpdatePromoSlider($fieldName, $fieldVal, $code, $url, $imgUrl)
    {
        $blockData = $this->getBlockContent($fieldName, $fieldVal);
        if ($blockData) {
            if ($this->customHtmlEditor->checkSlideExist($blockData->getContent(), 'promo-' . $code)) {
                $sliderUrlArr = $this->customHtmlEditor->retrieveDataFromBlockContent($blockData->getContent(), 'store url', '}}', 'media url', '}}', 'promo-' . $code);
                if (count($sliderUrlArr) == 2) {
                    $contents = str_replace($sliderUrlArr[0], "='" . $url . "'", $blockData->getContent());
                    $contents = str_replace($sliderUrlArr[1], "='" . $imgUrl . "'", $contents);
                    $this->updateBlockByBlockId($blockData->getId(), $contents);
                }
            } else {
                $contents = str_replace('[[code]]', $code, self::PROMO_SAMPLE);
                $contents = str_replace('[[url]]', $url, $contents);
                $contents = str_replace('[[imgUrl]]', $imgUrl, $contents);

                $existedContent = $blockData->getContent();
                if ($existedContent && $this->customHtmlEditor->checkSlideExist($blockData->getContent(), 'promo-')) {
                    $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                    $existedContent = $this->str_lreplace('</div>', '', $existedContent);
                } else {
                    if ($fieldVal == 'promotion_slider') {
                        $existedContent = '
<div data-content-type="html" data-appearance="default" data-element="main">
    <h2 class="promotion-label">
        <span>Promotions</span>
    </h2>
    <div class="product-items widget-new-grid pslide new-products-grid-slider owl-carousel owl-theme">
                    ';
                    } else if ($fieldVal == 'promotion_slider_mm') {
                        $existedContent = '
<div data-content-type="html" data-appearance="default" data-element="main">
    <h2 class="promotion-label">
        <span>ပရိုမိုးရှင်း ပစ္စည်းများ</span>
    </h2>
    <div class="product-items widget-new-grid pslide new-products-grid-slider owl-carousel owl-theme">
                        ';
                    }
                }
                $existedContent .= '
                            ' . $contents .
                    '
    </div>
</div>
                                ';

                $this->updateBlockByBlockId($blockData->getId(), $existedContent);
            }
        }
    }

    /**
     * delete specific slider block
     * @param string $fieldName
     * @param string $fieldVal
     * @param int $code
     */
    public function deleteSliderBlock($fieldName, $fieldVal, $code, $endIdx = 0, $step = 3)
    {
        $blockData = $this->getBlockContent($fieldName, $fieldVal);
        if ($blockData) {
            $contents = $this->customHtmlEditor->removeSliderByPromoCodeId(
                $blockData->getContent(),
                '<div class="item promo-' . $code . '"',
                '</div>',
                $endIdx,
                $step
            );
            $pos = strpos($contents, 'item promo-');
            if ($pos === false) {
                $contents = '';
            }
            $this->updateBlockByBlockId($blockData->getId(), $contents);
        }
    }

    /**
     * Update Block by block id
     * @param int $blockId
     * @param string $content
     */
    private function updateBlockByBlockId($blockId, $content)
    {
        $block = $this->blockFactory->create()->load($blockId);
        $block->setContent($this->customHtmlEditor->formatContent($content));
        $block->save();
    }


    /**
     * get block by condition
     * @param string $fieldType
     * @param string $fieldVal
     * @return \Magento\Cms\Api\Data\BlockInterface | null
     */
    public function getBlockContent($fieldType = 'identifier', $fieldVal = 'et_home_slider')
    {
        $filteredId = $this->_filterBuilder
            ->setConditionType('eq')
            ->setField($fieldType)
            ->setValue($fieldVal)
            ->create();

        $filterGroupList = [];
        $filterGroupList[] = $this->_filterGroupBuilder->addFilter($filteredId)->create();

        $this->_searchCriteriaBuilder->setFilterGroups($filterGroupList);
        $blockData = $this->blockRepository->getList($this->_searchCriteriaBuilder->create());
        foreach ($blockData->getItems() as $data) {
            if ($data) {
                if ($data->getContent()) {
                    $data->setContent($this->customHtmlEditor->convertHTMLEntities($data->getContent()));
                }
                return $data;
            }
        }
        return null;
    }

    /**
     * replace last occurrence
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    public function str_lreplace($search, $replace, $subject)
    {
        $pos = strrpos($subject, $search);

        if ($pos !== false) {
            $subject = substr_replace($subject, $replace, $pos, strlen($search));
        }

        return $subject;
    }

    /**
     * get media url for upload image
     * @return string
     */
    public function getMediaUrl()
    {
        $mediaUrl = $this->storeManagerInterface->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'homeslider/images/image/';
        return $mediaUrl;
    }

}
