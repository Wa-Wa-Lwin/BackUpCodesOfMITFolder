<?php

namespace MIT\Queue\Model;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerConfiguration;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Notification\NotifierInterface;
use MIT\Queue\Helper\EmailHelper;

class Consumer extends ConsumerConfiguration {

    const CONSUMER_NAME = "notifycustomer.massmail";
    const QUEUE_NAME = "notifycustomer.massmail";

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var EmailHelper
     */
    private $emailHelper;

    /**
     * @var NotifierInterface
     */
    private $notifierInterface;

    public function __construct(
        JsonHelper $jsonHelper,
        EmailHelper $emailHelper,
        NotifierInterface $notifierInterface
    )
    {
        $this->jsonHelper = $jsonHelper;
        $this->emailHelper = $emailHelper;
        $this->notifierInterface = $notifierInterface;
    }

    /**
     * consumer process start
     * @param string $messagesBody
     * @return string
     */
    public function process($request) {
        try {
            $data = $this->jsonHelper->jsonDecode($request);
            $storeId = $data['storeId'];
            $emailSent = 0;
            $totalMail = count($data['customer']);
            $unsendMail = [];
            foreach($data['customer'] as $customer) {
                try {
                    $this->emailHelper->sendMail($customer['name'], $customer['email'], $storeId);
                    $emailSent++;
                } catch (LocalizedException $e) {
                    $unsendMail[] = $customer['email'];
                    $this->notifierInterface->addMajor(__('err'), __($e->getMessage()));
                } catch (\Exception $e) {
                    $unsendMail[] = $customer['email'];
                   $this->notifierInterface->addMajor(__('err'), __($e->getMessage()));
                }
            }

            if ($emailSent) {
                $this->notifierInterface->addNotice(__('Mail Sending Success %1 out of %2 ', $emailSent, $totalMail), __('Unsent mail list: ', implode(',', $unsendMail)));
            }
        } catch(Exception $e) {
            $this->notifierInterface->addMajor(__('err'), __($e->getMessage()));
        }
    }


}