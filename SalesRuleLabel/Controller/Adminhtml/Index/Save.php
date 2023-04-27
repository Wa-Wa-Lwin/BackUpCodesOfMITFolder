<?php

namespace MIT\SalesRuleLabel\Controller\Adminhtml\Index;

use MIT\SalesRuleLabel\Model\CustomConditionFactory;
use Magento\Backend\App\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use MIT\SalesRuleLabel\Helper\Data;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var CustomConditionFactory
     */
    protected $ruledatamodel;

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        Action\Context $context,
        CollectionFactory $productCollectionFactory,
        CustomConditionFactory $ruledatamodel,
        Data $helper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->ruledatamodel = $ruledatamodel;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (isset($data['rule']['conditions'])) {
            $data['conditions'] = $data['rule']['conditions'];
        }
        if (isset($data['rule'])) {
            unset($data['rule']);
        }
        try {
            $model = $this->ruledatamodel->create();
            $id = $this->getRequest()->getParam('rule_id');
            if ($id) {
                $model->load($id);
            }
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This rule is no longer exists.'));
                return $resultRedirect->setPath('mit_salesrulelabel/index/index');
            }

            if (isset($data['sale_rule_id'])) {
                $salesRule = $this->helper->getSalesRuleById($data['sale_rule_id']);
                if ($salesRule && $salesRule->getRuleId() > 0) {
                    $data['from_date'] = $salesRule->getFromDate();
                    $data['to_date'] = $salesRule->getToDate();
                    $data['websites'] = implode(',' ,$salesRule->getWebsiteIds());
                    $data['customer_groups'] = implode(',', $salesRule->getCustomerGroupIds());
                    $data['sort_order'] = $salesRule->getSortOrder();
                } else {
                    $this->messageManager->addErrorMessage( __('Something went wrong while saving the data.'));
                    return $resultRedirect->setPath('mit_salesrulelabel/index/add');
                }
            } else {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the data.'));
                return $resultRedirect->setPath('mit_salesrulelabel/index/add');
            }

            $model->loadPost($data);
            $model->save();
            $this->_eventManager->dispatch(
                'adminhtml_controller_salesrulelabel_after_save_custom',
                ['id' => $model->getId(), 'model'=> $model]
            );
            $this->messageManager->addSuccess(__('Rule has been successfully saved.'));
            if ($this->getRequest()->getParam('back')) {
                if ($this->getRequest()->getParam('back') == 'add') {
                    return $resultRedirect->setPath('mit_salesrulelabel/index/add');
                } else {
                    return $resultRedirect->setPath(
                        'mit_salesrulelabel/index/edit',
                        [
                            'rule_id' => $model->getId(),
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            return $resultRedirect->setPath('mit_salesrulelabel/index/add');
        }
        return $resultRedirect->setPath('mit_salesrulelabel/index/index');
    }
}
