<?php

/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace MIT\Payatstore\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Directory\Model\CountryFactory;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Sales\Model\Order\AddressFactory;
use MitPickUp\Store\Model\CustomStorePickUpFactory;

class StoreList extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var CountryFactory
     */
    private $countryFactory;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @var CustomStorePickUpFactory
     */
    private $customStorePickUpFactory;

    public function __construct(
        JsonFactory $resultJsonFactory,
        Context $context,
        CustomStorePickUpFactory $customStorePickUpFactory,
        CountryFactory $countryFactory,
        AddressFactory $addressFactory,
        Renderer $renderer
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customStorePickUpFactory = $customStorePickUpFactory;
        $this->countryFactory = $countryFactory;
        $this->addressFactory = $addressFactory;
        $this->renderer = $renderer;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $collection = $this->customStorePickUpFactory->create()->getCollection();
        $collection->addFieldToSelect(['id', 'address', 'phone', 'country', 'shop']);
        $collection->getSelect()->join('eadesign_romcity as cityTbl', 'main_table.city = cityTbl.entity_id', ['city', 'postal_code']);
        $collection->getSelect()->join('directory_country_region_name as regionTbl', 'main_table.region = regionTbl.region_id', 'name as region');

        $result = [];

        foreach ($collection as $store) {
            $address = $this->addressFactory->create();
            $address->setStreet($store->getAddress());
            $address->setRegion($store->getRegion());
            $address->setCity($store->getCity());
            // $address->setPostcode($store->getPostalCode());
            $address->setCountryId($this->getCountryIdByCode($store->getCountry()));
            $address->setTelephone($store->getPhone());
            $result[] = [
                'id' => $store->getId(),
                'name' => $store->getShop(),
                'address' => $this->renderer->format($address, 'html')
            ];
        }
        return $resultJson->setData(['res' => $result]);
    }

    /**
     * get country id by country code
     * @param string $countryCode
     * @return int
     */
    private function getCountryIdByCode($countryCode)
    {
        return $this->countryFactory->create()->loadByCode($countryCode)->getId();
    }
}
