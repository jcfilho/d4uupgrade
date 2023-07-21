<?php

namespace Daytours\Quote\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('quote_item');
        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $column = [
                    'type' => Table::TYPE_INTEGER,
                    'size' => null,
                    'nullable' => true,
                    'comment' => 'Qty Custom'
                ];
                $connection->addColumn($tableName, 'qty_custom', $column);
            }
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $connection = $setup->getConnection();
                $column = [
                    'type' => Table::TYPE_DECIMAL,
                    'size' => null,
                    'nullable' => true,
                    'comment' => 'Original price when product is added to cart'
                ];
                $connection->addColumn($tableName, 'price_to_convert', $column);
            }
        }

        $setup->endSetup();
    }
}