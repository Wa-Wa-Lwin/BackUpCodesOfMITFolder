<?php

namespace MIT\Delivery\Model\Quote;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Cart\ShippingMethodConverter;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Rate;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;

use Magento\Quote\Model\ShippingMethodManagement as ModelShippingMethodManagement;

class ShippingMethodManagement extends ModelShippingMethodManagement
{

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Shipping method converter
     *
     * @var ShippingMethodConverter
     */
    protected $converter;

    /**
     * Customer Address repository
     *
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var AddressInterfaceFactory $addressFactory
     */
    private $addressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Constructor
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param ShippingMethodConverter $converter
     * @param AddressRepositoryInterface $addressRepository
     * @param TotalsCollector $totalsCollector
     * @param AddressInterfaceFactory|null $addressFactory
     * @param QuoteAddressResource|null $quoteAddressResource
     * @param CustomerSession|null $customerSession
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        ShippingMethodConverter $converter,
        AddressRepositoryInterface $addressRepository,
        TotalsCollector $totalsCollector,
        AddressInterfaceFactory $addressFactory = null,
        QuoteAddressResource $quoteAddressResource = null,
        CustomerSession $customerSession = null
    ) {
        parent::__construct(
            $quoteRepository,
            $converter,
            $addressRepository,
            $totalsCollector,
            $addressFactory,
            $quoteAddressResource,
            $customerSession
        );
        $this->quoteRepository = $quoteRepository;
        $this->converter = $converter;
        $this->addressRepository = $addressRepository;
        $this->totalsCollector = $totalsCollector;
        $this->addressFactory = $addressFactory ?: ObjectManager::getInstance()
            ->get(AddressInterfaceFactory::class);
        $this->quoteAddressResource = $quoteAddressResource ?: ObjectManager::getInstance()
            ->get(QuoteAddressResource::class);
        $this->customerSession = $customerSession ?? ObjectManager::getInstance()->get(CustomerSession::class);
    }


    // public function estimateByExtendedAddress($cartId, AddressInterface $address)
    // {
    //     /** @var Quote $quote */
    //     $quote = $this->quoteRepository->getActive($cartId);

    //     // no methods applicable for empty carts or carts with virtual products
    //     if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
    //         return [];
    //     }
    //     return parent::getShippingMethods($quote, $address);
    // }
}
