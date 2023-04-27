<?php

namespace MIT\HomeSlider\Helper;

use MIT\CatalogRule\Helper\CustomDataUpdater;
use MIT\CatalogRule\Helper\HomeSliderGenerator;

class HomeSliderGenerate
{

    /**
     * @var CustomDataUpdater 
     */
    protected $customDataUpdater;

    /**
     * @var HomeSliderGenerator
     */
    protected $homeSliderGenerator;

    public function __construct(
        CustomDataUpdater $customDataUpdater,
        HomeSliderGenerator $homeSliderGenerator
    ) {
        $this->customDataUpdater = $customDataUpdater;
        $this->homeSliderGenerator = $homeSliderGenerator;
    }

    /**
     * Generate home slider and call helper method at CatalogRule Rule
     * @param int $id
     * @param string $dataType
     * @param int $categoryId
     */
    public function homeSliderGenerate($id, $dataType, $categoryId)
    {

        $this->customDataUpdater->updateData($id, $dataType, $categoryId);
    }
}
