<?php


namespace MIT\Delivery\Controller\Adminhtml\Index;

class Delete extends \MIT\Delivery\Controller\Adminhtml\CustomDelivery
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\MIT\Delivery\Model\CustomDelivery::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Delivery.'));
                // go to grid
                return $resultRedirect->setPath('*/*/grid');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Delivery to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/grid');
    }
}
