<?php

namespace Daytours\EditOrder\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpgradeData implements UpgradeDataInterface
{

    private $eavSetupFactory;

    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion()
            && version_compare($context->getVersion(), '0.1.1') < 0
        ) {
            $group = 'Product Details';

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'extra_info',
                [
                    'group' => $group,
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Request Extra Info',
                    'input' => 'boolean',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if ($context->getVersion()
            && version_compare($context->getVersion(), '0.1.2') < 0
        ) {
            $installer = $setup;
            $installer->startSetup();
            $connection = $installer->getConnection();

            if ($connection->tableColumnExists('sales_order', 'post_venta') === false) {
                $connection
                    ->addColumn(
                        $setup->getTable('sales_order'),
                        'post_venta',
                        [
                            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                            'length' => 1,
                            'comment' => 'If information was saved',
                            'default' => 0
                        ]
                    );
            }
            $installer->endSetup();
        }

        $setup->endSetup();
    }
}