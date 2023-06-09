<?php

namespace MIT\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Delete as QuoteDelete;

class Delete extends QuoteDelete
{
    /**
     * Delete promo quote action
     *
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create(\Magento\SalesRule\Model\Rule::class);
                $model->load($id);
                $this->_eventManager->dispatch(
                    'salesrule_rule_delete_before',
                    ['id' => $model->getId(), 'model' => $model]
                );
                $model->delete();
                $this->_eventManager->dispatch(
                    'adminhtml_controller_salesrule_after_save_custom',
                    ['id' => $model->getId(), 'model' => $model]
                );
                $this->_eventManager->dispatch(
                    'adminhtml_controller_salesrule_after_delete_custom',
                    ['id' => $model->getId()]
                );
                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));
                $this->_redirect('sales_rule/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete the rule right now. Please review the log and try again.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_redirect('sales_rule/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        $this->_redirect('sales_rule/*/');
    }
}
