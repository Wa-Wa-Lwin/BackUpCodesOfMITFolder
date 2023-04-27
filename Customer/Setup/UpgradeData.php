<?php

namespace MIT\Customer\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $eavConfig;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $setup->startSetup();

            $attributeCode = 'terms_conditions';
            $eavSetup->addAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, 'terms_conditions', [
                'label' => 'Terms And Conditions',
                'required' => false,
                'user_defined' => 1,
                'system' => 0,
                'position' => 100,
                'input' => 'boolean',
                'type' => 'int'
            ]);

            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attributeCode
            );

            $amountId = $this->eavConfig->getAttribute(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode);
            $amountId->setData('used_in_forms', [
                'adminhtml_customer',
                'customer_account_create'
            ]);
            $amountId->getResource()->save($amountId);

            $setup->endSetup();
        }
    }
}
