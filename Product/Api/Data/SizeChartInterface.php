<?php

namespace MIT\Product\Api\Data;

interface SizeChartInterface {

    const IS_SIZE_CHART = 'is_size_chart';
    const LABEL = 'label';
    const DESCRIPTION = 'description';
    const IMG_PATH = 'img_path';

    /**
     * set size chart show or not
     * @param bool $isSizeChart
     * @return $this
     */
    public function setIsSizeChart($isSizeChart);

    /**
     * get size chart show or not
     * @return bool
     */
    public function getIsSizeChart();

    /**
     * set label
     * @param string $label
     * @return $this
     */
    public function setLabel($label);

    /**
     * get label
     * @return string
     */
    public function getLabel();

    /**
     * set description
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * get description
     * @return string
     */
    public function getDescription();

    /**
     * set image path
     * @param string $imgPath
     * @return $this
     */
    public function setImgPath($imgPath);

    /**
     * get image path
     * @return string
     */
    public function getImgPath();
}