<?php

namespace MIT\Product\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use MIT\Product\Api\Data\CustomKeyValInterface;

class CustomKeyVal extends AbstractExtensibleModel implements CustomKeyValInterface
{

    /**
     * @inheritdoc
     */
    public function setKey($key)
    {
        return $this->setData(self::KEY, $key);
    }

    /**
     * @inheritdoc
     */
    public function getKey()
    {
        return $this->getData(self::KEY);
    }

    /**
     * @inheritdoc
     */
    public function setValue($val)
    {
        return $this->setData(self::VAL, $val);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->getData(self::VAL);
    }
}
