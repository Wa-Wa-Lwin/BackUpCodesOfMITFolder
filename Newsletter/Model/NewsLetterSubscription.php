<?php
namespace MIT\Newsletter\Model;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Newsletter\Model\SubscriberFactory;
class NewsLetterSubscription implements \MIT\Newsletter\Api\NewsLetterSubscriptionInterface
{
    /**
    * @var EmailValidator
    */
    private $emailValidator;

    /**
    * @var SubscriptionManagerInterface
    */
    private $subscriptionManager;
  
    /**
    * @var \Magento\Integration\Model\Oauth\TokenFactory
    */
    private $tokenFactory;

    /**
    * @var \Magento\Framework\Stdlib\DateTime
    */
    private $dateTime;

    /**
    * @var \Magento\Framework\Stdlib\DateTime\DateTime
    */
    private $date;

    /**
    * @var \Magento\Integration\Helper\Oauth\Data
    */
    private $oauthHelper;


    public function __construct(
        SubscriberFactory $subscriberFactory,
        StoreManagerInterface $storeManager,
        SubscriptionManagerInterface $subscriptionManager,
        EmailValidator $emailValidator = null,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Integration\Helper\Oauth\Data $oauthHelper
    ) {
        $this->subscriptionManager = $subscriptionManager;
        $this->emailValidator = $emailValidator ?: ObjectManager::getInstance()->get(EmailValidator::class);
        $this->_storeManager = $storeManager;
        $this->_subscriberFactory = $subscriberFactory;
        $this->tokenFactory = $tokenFactory;
        $this->dateTime = $dateTime;
        $this->date = $date;
        $this->oauthHelper = $oauthHelper;
    }

    /**
    * Validates the format of the email address
    *
    * @param string $email
    * @throws LocalizedException
    * @return void
    */
    protected function validateEmailFormat($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new LocalizedException(__('Please enter a valid email address.'));
        }
    }

    public function postNewsLetter($email)
    {
        if($email){
            $this->validateEmailFormat($email);
            $websiteId = (int)$this->_storeManager->getStore()->getWebsiteId();
            
            /** @var Subscriber $subscriber */
            $subscriber = $this->_subscriberFactory->create()->loadBySubscriberEmail($email, $websiteId);
            if ($subscriber->getId()
                && (int)$subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED) {
                throw new LocalizedException(
                    __('This email address is already subscribed.')
                );
              }
              $storeId = (int)$this->_storeManager->getStore()->getId();
              $currentCustomerId = $this->getCustomerIdByToken();
              $subscriber = $currentCustomerId
                  ? $this->subscriptionManager->subscribeCustomer($currentCustomerId, $storeId)
                  : $this->subscriptionManager->subscribe($email, $storeId);
            return true;

        }

    }
    /**
    * get customerId from token
    * @return int
    */
    private function getCustomerIdByToken()
    {
        if (isset($_SERVER['HTTP_TOKEN'])) {
            $result = $this->tokenFactory->create()->loadByToken($_SERVER['HTTP_TOKEN']);
            if (!$this->checkTokenExpired($result->getCreatedAt())) {
                return $result->getCustomerId();
            }
        }
        return 0;
    }
    
    /**
    * check token expired or not
    * @param string $tokenCreatedAt
    * @return bool
    */
    private function checkTokenExpired($tokenCreatedAt)
    {
        return $tokenCreatedAt <= $this->dateTime->formatDate($this->date->gmtTimestamp() - $this->oauthHelper->getCustomerTokenLifetime() * 60 * 60);
    }

}
