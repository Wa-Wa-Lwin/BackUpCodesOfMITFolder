<?php

namespace MIT\Cart\Api\Data;

use Magento\Quote\Api\Data\TotalsItemInterface;

interface CustomTotalsItemInterface extends TotalsItemInterface
{

    const KEY_IMG_Path = 'imgpath';

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
