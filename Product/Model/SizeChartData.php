<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\SizeChartInterface;

class SizeChartData extends AbstractExtensibleModel implements SizeChartInterface
{
    /**
     * @inheritdoc
     */
    public function setIsSizeChart($isSizeChart)
    {
        return $this->setData(self::IS_SIZE_CHART, $isSizeChart);
    }

    /**
     * @inheritdoc
     */
    public function getIsSizeChart()
    {
        return $this->getData(self::IS_SIZE_CHART);
    }

    /**
     * @inheritdoc
     */
    public function setLabel($label)
    {
        return $this->setData(self::LABEL, $label);
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->getData(self::LABEL);
    }

    /**
     * @inheritdoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setImgPath($imgPath)
    {
        return $this->setData(self::IMG_PATH, $imgPath);
    }

    /**
     * @inheritdoc
     */
    public function getImgPath()
    {
        return $this->getData(self::IMG_PATH);
    }
}
