<?php


namespace MIT\ConfigurableProduct\Plugin\Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns;

class Name
{

    public function afterPrepareDataSource(
        \Magento\ConfigurableProduct\Ui\Component\Listing\AssociatedProduct\Columns\Name $subject,
        $result
    ) {
        if (isset($result['data']['items'])) {
            $fieldName = $subject->getData('name');
            foreach ($result['data']['items'] as &$item) {
                if (isset($item[$fieldName]) && isset($item['entity_id'])) {
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $product = $objectManager->get('Magento\Catalog\Model\Product')->load($item['entity_id']);
                    $item['my_sku'] = $product->getMySku();
                }
            }
        }
        return $result;
    }
}
