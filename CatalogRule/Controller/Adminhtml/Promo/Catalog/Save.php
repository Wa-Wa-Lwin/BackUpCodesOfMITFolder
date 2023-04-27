<?php


namespace MIT\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Save as CatalogRuleSave;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use MIT\CatalogRule\Model\ImageUploader;

class Save extends CatalogRuleSave
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var ImageUploader
     */
    private $imageUploader;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @param DataPersistorInterface $dataPersistor
     * @param TimezoneInterface $localeDate
     * @param ImageUploader $imageUploader
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Date $dateFilter,
        DataPersistorInterface $dataPersistor,
        TimezoneInterface $localeDate,
        ImageUploader $imageUploader
    ) {
        $this->localeDate = $localeDate;
        $this->imageUploader = $imageUploader;
        parent::__construct($context, $coreRegistry, $dateFilter, $dataPersistor, $localeDate);
    }

    /**
     * Execute save action from catalog rule
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            /** @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $ruleRepository */
            $ruleRepository = $this->_objectManager->get(
                \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class
            );
            /** @var \Magento\CatalogRule\Model\Rule $model */
            $model = $this->_objectManager->create(\Magento\CatalogRule\Model\Rule::class);

            try {
                $this->_eventManager->dispatch(
                    'adminhtml_controller_catalogrule_prepare_save',
                    ['request' => $this->getRequest()]
                );
                $data = $this->getRequest()->getPostValue();
                if (!$this->getRequest()->getParam('from_date')) {
                    $data['from_date'] = $this->localeDate->formatDate();
                }
                $filterValues = ['from_date' => $this->_dateFilter];
                if ($this->getRequest()->getParam('to_date')) {
                    $filterValues['to_date'] = $this->_dateFilter;
                }
                $inputFilter = new \Zend_Filter_Input(
                    $filterValues,
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = $this->getRequest()->getParam('rule_id');
                if ($id) {
                    $model = $ruleRepository->get($id);
                }

                $imgArr = $this->moveTmpFileAndUpload($data);
                $data['home_slider_img'] = $imgArr[0];
                $data['promo_slider_img'] = $imgArr[1];
                $data['home_slider_img_mobile'] = $imgArr[2];

                $validateResult = $model->validateData(new \Magento\Framework\DataObject($data));
                if ($validateResult !== true) {
                    foreach ($validateResult as $errorMessage) {
                        $this->messageManager->addErrorMessage($errorMessage);
                    }
                    $this->_getSession()->setPageData($data);
                    $this->dataPersistor->set('catalog_rule', $data);
                    $this->_redirect('catalog_rule/*/edit', ['id' => $model->getId()]);
                    return;
                }

                if (isset($data['rule'])) {
                    $data['conditions'] = $data['rule']['conditions'];
                    unset($data['rule']);
                }

                unset($data['conditions_serialized']);
                unset($data['actions_serialized']);

                $model->loadPost($data);

                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->dataPersistor->set('catalog_rule', $data);

                $ruleRepository->save($model);

                $this->_eventManager->dispatch(
                    'adminhtml_controller_catalogrule_after_save_custom',
                    ['id' => $model->getRuleId(), 'type' => 'upsert']
                );

                $this->messageManager->addSuccessMessage(__('You saved the rule.'));
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData(false);
                $this->dataPersistor->clear('catalog_rule');

                if ($this->getRequest()->getParam('auto_apply')) {
                    $this->getRequest()->setParam('rule_id', $model->getId());
                    $this->_forward('applyRules');
                } else {
                    if ($model->isRuleBehaviorChanged()) {
                        $this->_objectManager
                            ->create(\Magento\CatalogRule\Model\Flag::class)
                            ->loadSelf()
                            ->setState(1)
                            ->save();
                    }
                    if ($this->getRequest()->getParam('back')) {
                        $this->_redirect('catalog_rule/*/edit', ['id' => $model->getId()]);
                        return;
                    }
                    $this->_redirect('catalog_rule/*/');
                }

                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the rule data. Please review the error log.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_objectManager->get(\Magento\Backend\Model\Session::class)->setPageData($data);
                $this->dataPersistor->set('catalog_rule', $data);
                $this->_redirect('catalog_rule/*/edit', ['id' => $this->getRequest()->getParam('rule_id')]);
                return;
            }
        }
        $this->_redirect('catalog_rule/*/');
    }

    private function moveTmpFileAndUpload($data)
    {
        $homeImg = 'home_slider_img';
        $promoImg = 'promo_slider_img';
        $homeImgMobile = 'home_slider_img_mobile';
        return [$this->getImgPath($homeImg, $data), $this->getImgPath($promoImg, $data), $this->getImgPath($homeImgMobile, $data)];
    }

    private function getImgPath($key, $data)
    {
        return $this->imageUploader->uploadFileAndGetName($key, $data);
    }
}
