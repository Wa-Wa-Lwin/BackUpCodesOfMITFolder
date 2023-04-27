<?php

namespace MIT\Report\Model\ResourceModel\Order\Product\Collection;

/**
 * @api
 * @since 100.0.2
 */
class Refunded extends \Magento\Reports\Model\ResourceModel\Report\Collection
{
    /**
     * Report sub-collection class name
     *
     * @var string
     */
    protected $_reportCollection = \MIT\Report\Model\ResourceModel\Order\Product\RefundedCollection::class;


    /**
     * Get interval for a day
     *
     * @param \DateTime $dateStart
     * @return array
     */
    protected function _getDayInterval(\DateTime $dateStart)
    {
        $interval = [
            'period' => $this->_localeDate->formatDate($dateStart, \IntlDateFormatter::MEDIUM),
            'start' => $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 00:00:00')),
            'end' => $this->_localeDate->convertConfigTimeToUtc($dateStart->format('Y-m-d 23:59:59')),
        ];
        return $interval;
    }
}
