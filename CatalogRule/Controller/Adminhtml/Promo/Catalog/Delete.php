<?php

namespace MIT\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog\Delete as CatalogDelete;

class Delete extends CatalogDelete
{

    /**
     * @return void
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                /** @var \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface $ruleRepository */
                $ruleRepository = $this->_objectManager->get(
                    \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface::class
                );
                $ruleRepository->deleteById($id);
                $this->_eventManager->dispatch(
                    'adminhtml_controller_catalogrule_after_save_custom',
                    ['id' => $id, 'type' => 'delete']
                );

                $this->_objectManager->create(\Magento\CatalogRule\Model\Flag::class)->loadSelf()->setState(1)->save();
                $this->messageManager->addSuccessMessage(__('You deleted the rule.'));
                $this->_redirect('catalog_rule/*/');
                return;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('We can\'t delete this rule right now. Please review the log and try again.')
                );
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
                $this->_redirect('catalog_rule/*/edit', ['id' => $this->getRequest()->getParam('id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a rule to delete.'));
        $this->_redirect('catalog_rule/*/');
    }
}
