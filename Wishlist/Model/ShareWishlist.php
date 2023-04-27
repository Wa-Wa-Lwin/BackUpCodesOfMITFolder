<?php

namespace MIT\Wishlist\Model;
use MIT\Wishlist\Api\ShareWishlistInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Helper\AbstractHelper;



/**
 * Share Wishlist API
 */
class ShareWishlist extends AbstractHelper implements ShareWishlistInterface
{

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Wishlist\Model\Config
     */
    protected $_wishlistConfig;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    private $wishlist;

    protected $_customerRepositoryInterface;

    private $wishlistFactory;

    protected $_eventManager;

    protected $registry;

    /**
     * @param \Magento\Wishlist\Model\Config $wishlistConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Wishlist\Model\Config $wishlistConfig,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager

    ) {

        $this->_wishlistConfig = $wishlistConfig;
        $this->wishlist = $wishlist;
        $this->_transportBuilder = $transportBuilder;
        $this->wishlistFactory = $wishlistFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_eventManager = $eventManager;
        $this->_url = $url;
        $this->registry = $registry;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;

    }

    /**
     * Share wishlist
     * @param string email
     * @param string message
     * @param string customerId
     * @return boolean
     */
    public function shareWishList(array $email, $message, $customerId)
    {

        $wishlist_collection = $this->wishlist->loadByCustomerId($customerId, true)->getItemCollection();
        foreach ($wishlist_collection as $item) {
                    $wishlist_id      = $item->getWishlistId();
        }

        $wishlistId = (int)$wishlist_id;
        $wishlist = $this->wishlistFactory->create()->load($wishlistId);
        $this->registry->register('shared_wishlist', $wishlist);

        $emails = $email;
        $sent = 0;

        try {

            // $customerId = 2;
            $customer = $this->_customerRepositoryInterface->getById($customerId);

            $emails = array_unique($emails);
            $sharingCode = $wishlist->getSharingCode();

            try {
                foreach ($emails as $email) {
                    $transport = $this->_transportBuilder->setTemplateIdentifier(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $this->storeManager->getStore()->getStoreId(),
                        ]
                    )->setTemplateVars(
                        [
                            'customer' => $customer,
                            'customerName' =>$customer->getFirstname(). $customer->getLastname(),
                            'salable' => $wishlist->isSalable() ? 'yes' : '',
                            'viewOnSiteLink' => $this->_url->getUrl('wishlist/shared/index', ['code' => $sharingCode]),
                            'message' => $message,
                            'store' => $this->storeManager->getStore(),
                        ]
                    )->setFrom(
                        $this->scopeConfig->getValue(
                            'wishlist/email/email_identity',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        )
                    )->addTo(
                        $email
                    )->getTransport();

                    $transport->sendMessage();

                    $sent++;
                }
            } catch (\Exception $e) {
                $wishlist->setShared($wishlist->getShared() + $sent);
                $wishlist->save();
                throw $e;
            }
            $wishlist->setShared($wishlist->getShared() + $sent);
            $wishlist->save();

            $this->_eventManager->dispatch('wishlist_share', ['wishlist' => $wishlist]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
