<?php

namespace MIT\Queue\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use MIT\Queue\Model\Config;
use Magento\Framework\App\Area;

class EmailHelper extends AbstractHelper
{

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $stateInterface;

    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        TransportBuilder $transportBuilder,
        StateInterface $stateInterface,
        Config $config
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->stateInterface = $stateInterface;
        $this->config = $config;
    }

    /**
     * send mail
     * @param string $name
     * @param string $email
     * @param int $storeId
     * @return $this
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendMail($name, $email, $storeId)
    {

        $this->stateInterface->suspend();
        $this->transportBuilder
            ->setTemplateIdentifier($this->config->getEmailTemplate($storeId))
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )->setFromByScope(
                $this->config->getSender($storeId),
                $storeId
            )->setTemplateVars(
                [
                    'customer_name' => $name,
                    'customer_email' => $email
                ]
            )->addTo(
                $email,
                $name
            );
        $transport = $this->transportBuilder->getTransport();
        $transport->sendMessage();

        $this->stateInterface->resume();

        return $this;
    }
}
