<?php 

namespace Daytours\Bookingsystem\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            // Get module table
            $tableIntervals = $setup->getTable('booking_intervalhours');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableIntervals) == true) {
                // Declare data
                $columns = [
                    'calendar_number' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => '1',
                        'comment' => 'calendar 1 or 2',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableIntervals, $name, $definition);
                }

            }
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            // Get module table
            $tableOrders = $setup->getTable('booking_orders');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableOrders) == true) {
                // Declare data
                $columns = [
                    'bkorder_quantity_interval_two' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => '0',
                        'comment' => 'qty interval two',
                    ],
                    'bkorder_interval_time_two' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'default' => '',
                        'comment' => 'qty time text',
                    ],
                    'bkorder_qty_two' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'default' => '0',
                        'comment' => 'qty two',
                    ]
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableOrders, $name, $definition);
                }

            }
        }

        // DAYT-293 special price to intervals
        if (version_compare($context->getVersion(), '1.0.4') < 0){
            $tableName = $setup->getTable('booking_intervalhours');
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // Declare data
                $columns = [
                    'intervalhours_special_price' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        ['nullable' => true,'default' => 0],
                        'comment' => 'Special Price interval',
                        'after' => 'intervalhours_price',
                    ]
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }
        $setup->endSetup();
    }
}