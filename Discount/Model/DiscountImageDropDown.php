<?php

namespace MIT\Discount\Model;

use Magento\Framework\Data\OptionSourceInterface;
use MIT\Discount\Model\LabelImageRepository;
use Magento\Framework\Api\SearchCriteriaInterface;

class DiscountImageDropDown implements OptionSourceInterface
{
    /**
     * @var LabelImageRepository
     */
    private $labelImageRepository;

    /**
     * @var SearchCriteriaInterface
     */
    private $searchCriteriaInterface;

    public function __construct(
        LabelImageRepository $labelImageRepository,
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteriaInterface
    ) {
        $this->labelImageRepository = $labelImageRepository;
        $this->searchCriteriaInterface = $searchCriteriaInterface;
    }

    public function toOptionArray()
    {
        return $this->getOptions();
    }

    protected function getOptions()
    {
        $res = [];
        $imageDataList = $this->labelImageRepository->getList($this->searchCriteriaInterface);
        foreach($imageDataList->getItems() as $item) {
            $res[] = array('label' => $item->getName(), 'value' => $item->getLabelImageId());
        }
        if (count($res) > 0) {
            array_unshift(
                $res,
                ['value' => -1, 'label' => __('Please select image.')]
            );
        }
        return $res;
    }
}
