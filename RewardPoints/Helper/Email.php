<?php

namespace MIT\RewardPoints\Helper;

use Mageplaza\RewardPoints\Helper\Email as HelperEmail;
use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\RewardPoints\Helper\Account;
use MIT\Customer\Helper\AttributeChecker;
use MIT\Customer\Helper\SMSSender;

class Email extends HelperEmail
{
    /**
     * @var \MIT\Customer\Helper\AttributeChecker
     */
    private $attributeChecker;

    /**
     * @var SMSSender
     */
    private $smsSender;

    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param Account $accountHelper
     * @param \MIT\Customer\Helper\AttributeChecker $attributeChecker
     * @param SMSSender $smsSender
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        Account $accountHelper,
        \MIT\Customer\Helper\AttributeChecker $attributeChecker,
        SMSSender $smsSender,
        StoreManagerInterface $storeManagerInterface
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->accountHelper = $accountHelper;
        $this->attributeChecker = $attributeChecker;
        $this->smsSender = $smsSender;
        $this->storeManagerInterface = $storeManagerInterface;
        parent::__construct($context, $objectManager, $storeManager, $transportBuilder, $accountHelper);
    }

    /**
     * @param $customerId
     * @param $emailType
     * @param array $templateParams
     * @param null $storeId
     * @param string $sender
     * @param null $email
     *
     * @return $this
     * @throws LocalizedException
     */
    public function sendEmailTemplate(
        $customerId,
        $emailType,
        $templateParams = [],
        $storeId = null,
        $sender = self::XML_PATH_EMAIL_SENDER,
        $email = null
    ) {
        $customer = $this->accountHelper->getCustomerById($customerId);
        $storeId = $storeId ?: $customer->getStoreId();

        if (!$this->isEmailEnable('', $storeId) || !$this->isEmailEnable($emailType, $storeId)) {
            return $this;
        }

        $account = $this->accountHelper->getByCustomerId($customerId);
        if (!$account || !$account->getId() || !$account->getData('notification_' . $emailType)) {
            return $this;
        }

        $templateParams['customer_name'] = $customer->getName();

        try {

            $loginType = $this->attributeChecker->checkEmailOrPhone($customer->getEmail());

            if ($loginType == AttributeChecker::PHONE_TYPE) {
                $baseUrl = $this->storeManagerInterface->getStore()->getBaseUrl();
                if ($emailType == Email::XML_PATH_EXPIRE_EMAIL_TYPE) {
                    $message = sprintf('Dear %s, We would like to inform you that your transaction will be expired after %s. You should also note that if your earned points is expired, your rewards will become invalid. Please check more details %s ', $customer->getName(), $templateParams['expiration_date'], $baseUrl . 'customer/rewards');
                    $this->smsSender->sendSMS($customer->getEmail(), $message);
                } else if ($emailType == Email::XML_PATH_UPDATE_TRANSACTION_EMAIL_TYPE) {
                    $message = sprintf('Dear %s, We would like to inform you that your rewards have been updated. Please check more details %s ', $customer->getName(), $baseUrl . 'customer/rewards');
                    $this->smsSender->sendSMS($customer->getEmail(), $message);
                }
            } else if ($loginType == AttributeChecker::EMAIL_TYPE) {
                $templateId = $this->getEmailConfig($emailType . '/template', $storeId);

                $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                    ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                    ->setTemplateVars($templateParams)
                    ->setFrom($this->getEmailConfig($sender, $storeId))
                    ->addTo($email ?: $customer->getEmail(), $customer->getName())
                    ->getTransport();

                $transport->sendMessage();
            }
        } catch (Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this;
    }
}
