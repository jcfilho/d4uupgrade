<?php

namespace Daytours\Provider\Setup;

use Daytours\Provider\Model\OptionsToAttrProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * UpgradeSchema constructor.
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

         if (version_compare($context->getVersion(), '0.1.1', '<')) {
             $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

             /** @var  \Magento\Eav\Setup\EavSetup $eavSetup */
             $attribute = $eavSetup->getAttribute(Product::ENTITY, 'booking_provider');
             if (!$attribute || !isset($attribute['attribute_id'])) {
                 $eavSetup->addAttribute(
                     Product::ENTITY,
                     'booking_provider',
                     [
                         'backend' => '',
                         'frontend' => '',
                         'class' => '',
                         'global' => Attribute::SCOPE_STORE,
                         'visible' => true,
                         'required' => false,
                         'user_defined' => true,
                         'searchable' => false,
                         'filterable' => false,
                         'comparable' => false,
                         'visible_on_front' => false,
                         'used_in_product_listing' => false,
                         'default' => '',
                         'unique' => false,
                         'apply_to' => '',
                         'type' => 'int',
                         'label' => 'Provider',
                         'input' => 'select',
                         'source' => OptionsToAttrProduct::class
                     ]
                 );

                 $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
                 $attributeSetLoad = $eavSetup->getAttributeSet($entityTypeId, 'Default', 'attribute_set_name');
                 $eavSetup->addAttributeToGroup(
                     $entityTypeId,
                     $attributeSetLoad,
                     'General',
                     'booking_provider',
                     100
                 );

                 $attributeSetLoad = $eavSetup->getAttributeSet($entityTypeId, 'Transfer', 'attribute_set_name');
                 $eavSetup->addAttributeToGroup(
                     $entityTypeId,
                     $attributeSetLoad,
                     'General',
                     'booking_provider',
                     100
                 );


             }

         }

        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            /** @var  \Magento\Eav\Setup\EavSetup $eavSetup */
            $attribute = $eavSetup->getAttribute(Product::ENTITY, 'last_minute_hour');
            if (!$attribute || !isset($attribute['attribute_id'])) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    'last_minute_hour',
                    [
                        'backend' => '',
                        'frontend' => '',
                        'class' => '',
                        'global' => Attribute::SCOPE_STORE,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => false,
                        'default' => '',
                        'unique' => false,
                        'apply_to' => '',
                        'type' => 'text',
                        'label' => 'Meeting Hour',
                        'input' => 'text',
                        'source' => ''
                    ]
                );

                $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
                $attributeSetLoad = $eavSetup->getAttributeSet($entityTypeId, 'Default', 'attribute_set_name');
                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetLoad,
                    'Last Minute',
                    'last_minute_hour',
                    100
                );

                $attributeSetLoad = $eavSetup->getAttributeSet($entityTypeId, 'Transfer', 'attribute_set_name');
                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetLoad,
                    'Last Minute',
                    'last_minute_hour',
                    100
                );
            }

            $attribute = $eavSetup->getAttribute(Product::ENTITY, 'last_minute_meeting_point');
            if (!$attribute || !isset($attribute['attribute_id'])) {
                $eavSetup->addAttribute(
                    Product::ENTITY,
                    'last_minute_meeting_point',
                    [
                        'backend' => '',
                        'frontend' => '',
                        'class' => '',
                        'global' => Attribute::SCOPE_STORE,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => true,
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => false,
                        'used_in_product_listing' => false,
                        'default' => '',
                        'unique' => false,
                        'apply_to' => '',
                        'type' => 'text',
                        'label' => 'Meeting point',
                        'input' => 'text',
                        'source' => ''
                    ]
                );

                $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
                $attributeSetLoad = $eavSetup->getAttributeSet($entityTypeId, 'Default', 'attribute_set_name');
                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetLoad,
                    'Last Minute',
                    'last_minute_meeting_point',
                    101
                );

                $attributeSetLoad = $eavSetup->getAttributeSet($entityTypeId, 'Transfer', 'attribute_set_name');
                $eavSetup->addAttributeToGroup(
                    $entityTypeId,
                    $attributeSetLoad,
                    'Last Minute',
                    'last_minute_meeting_point',
                    101
                );
            }

        }

        $setup->endSetup();
    }
}