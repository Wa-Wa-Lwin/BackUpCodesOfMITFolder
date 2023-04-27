<?php

declare(strict_types=1);

namespace MIT\CatalogRule\Controller\Adminhtml\Promo\Catalog;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use MIT\CatalogRule\Model\ImageUploader;

/**
 * Class Upload
 */
class Upload extends Action
{
    /**
     * @var string
     */
    const ACTION_RESOURCE = 'Magebrew_ImageUploadFormField::imageuploadfield';

    /**
     * @var ImageUploader
     */
    protected $uploader;

    /**
     * Upload constructor.
     *
     * @param Context $context
     * @param ImageUploader $uploader
     */
    public function __construct(
        Context $context,
        ImageUploader $uploader
    ) {
        parent::__construct($context);
        $this->uploader = $uploader;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->uploader->saveFileToTmpDir($this->getFieldName());

            $result['cookie'] = [
                'name'     => $this->_getSession()->getName(),
                'value'    => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path'     => $this->_getSession()->getCookiePath(),
                'domain'   => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * @return string
     */
    protected function getFieldName()
    {
        return $this->_request->getParam('type');
    }
}
