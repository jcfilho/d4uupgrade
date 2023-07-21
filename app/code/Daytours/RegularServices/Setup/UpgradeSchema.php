<?php

namespace Daytours\RegularServices\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $tableName = $setup->getTable('catalog_product_option');
        if (version_compare($context->getVersion(), '0.1.0', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $column = [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 6,
                    'nullable' => false,
                    'comment' => 'Is Multiplier',
                    'default' => '0'
                ];
                $connection->addColumn($tableName, 'is_multiplier', $column);
                $column = [
                    'type' => Table::TYPE_SMALLINT,
                    'length' => 6,
                    'nullable' => false,
                    'comment' => 'Is Child',
                    'default' => '0'
                ];
                $connection->addColumn($tableName, 'is_child', $column);
            }
        }

        $tableName = $setup->getTable('catalog_product_option_type_price');
        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $column = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'nullable' => true,
                    'comment' => 'Child Price',
                    'default' => '0.0000'
                ];
                $connection->addColumn($tableName, 'child_price', $column);
            }
        }

        $setup->endSetup();
    }
}