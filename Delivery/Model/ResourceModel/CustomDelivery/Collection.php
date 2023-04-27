<?php

namespace MIT\Delivery\Model\ResourceModel\CustomDelivery;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use MIT\Delivery\Model\CustomDelivery as CustomDeliveryModel;
use MIT\Delivery\Model\ResourceModel\CustomDelivery as CustomDeliveryResourceModel;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @param EntityFactoryInterface $entityFactory,
     * @param LoggerInterface        $logger,
     * @param FetchStrategyInterface $fetchStrategy,
     * @param ManagerInterface       $eventManager,
     * @param StoreManagerInterface  $storeManager,
     * @param AdapterInterface       $connection,
     * @param AbstractDb             $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->_init(CustomDeliveryModel::class, CustomDeliveryResourceModel::class);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->storeManager = $storeManager;
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        // $regionTable = $this->getTable('directory_country_region_name');
        // $cityTable = $this->getTable('eadesign_romcity');
        // $this->getSelect()->joinLeft(
        //     $regionTable . ' as rgt',
        //     'main_table.region = rgt.region_id',
        //     [
        //         'region_name' => 'rgt.name'
        //     ]
        // );
        // $this->getSelect()->joinLeft(
        //     $cityTable . ' as cyt',
        //     'main_table.city = cyt.entity_id',
        //     [
        //         'city_name' => 'cyt.city'
        //     ]
        // );

        // $this->addFilterToMap('region_name', 'rgt.name');
        // $this->addFilterToMap('city_name', 'cyt.city');
    }
}
