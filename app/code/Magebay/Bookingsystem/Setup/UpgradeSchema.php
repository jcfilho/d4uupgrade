<?php 

namespace Magebay\Bookingsystem\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
class UpgradeSchema implements  UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context){
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.1') < 0) {
            // Get module table
            $tableAddonOptoin = $setup->getTable('booking_options');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableAddonOptoin) == true) {
                // Declare data
                $columns = [
                    'option_price_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        'nullable' => true,
                        'comment' => 'option price type',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableAddonOptoin, $name, $definition);
                }

            }
        }
		if (version_compare($context->getVersion(), '2.0.1') < 0) {
			$bookingAtc = $setup->getTable('booking_act');
			if ($setup->getConnection()->isTableExists($bookingAtc) != true) {
				$tableBookingAtc = $setup->getConnection()
					->newTable($bookingAtc)
					 ->addColumn(
                'act_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Id'
            )
            ->addColumn(
                'domain_count',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Domain Count'
            )
            ->addColumn(
                'domain_list',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'No comment'
            )
            ->addColumn(
                'path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'No comment'
            )
            ->addColumn(
                'extension_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'No comment'
            )
            ->addColumn(
                'act_key',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'No comment'
            )
            ->addColumn(
                'domains',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                500,
                ['nullable' => false],
                'No comment'
            )
            ->addColumn(
                'created_time',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                null,
                ['nullable' => false],
                'No comment'
            )
            ->addColumn(
                'is_valid',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['nullable' => false, 'default' => 0],
                'No comment'
            )
			->setComment('Booking ACT')
			->setOption('type', 'InnoDB')
			->setOption('charset', 'utf8');
				$setup->getConnection()->createTable($tableBookingAtc);
			}
		}
		if (version_compare($context->getVersion(), '2.1.1') < 0) {
            // Get module table
            $tableBookingSystems = $setup->getTable('booking_systems');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableBookingSystems) == true) {
                // Declare data
                $columns = [
                    'disable_days' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Disable days for rent',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableBookingSystems, $name, $definition);
                }

            } 
			$tableBookingRooms = $setup->getTable('booking_rooms');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableBookingRooms) == true) {
                // Declare data
                $columns = [
                    'disable_days' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Disable days for rooms',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableBookingRooms, $name, $definition);
                }

            }
        }
       if (version_compare($context->getVersion(), '2.1.2') < 0) {
            // Get module table
            $tableBookingSystems = $setup->getTable('booking_systems');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableBookingSystems) == true) {
                // Declare data
                $columns = [
                    'booking_type_intevals' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        ['nullable' => false, 'default' => 0],
                        'comment' => 'Type Intalvals',
                    ],
                    'store_id' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        ['nullable' => false, 'default' => 0],
                        'comment' => 'Store ID',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableBookingSystems, $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(), '2.1.2') < 0) {
            // Get module table
            $tableIntervalhours = $setup->getTable('booking_intervalhours');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableIntervalhours) == true) {
                // Declare data
                $columns = [
                    'intervalhours_status' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        ['nullable' => false, 'default' => 1],
                        'comment' => 'Type Intalvals',
                    ],
                    'intervalhours_price' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_FLOAT,
                        ['nullable' => false, 'default' => 1],
                        'comment' => 'Special Price',
                    ],
                    'intervalhours_label' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        ['nullable' => true, 'default' => ''],
                        'comment' => 'Label For Item',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableIntervalhours, $name, $definition);
                }
            }
            //update discount table
            $tableDiscount = $setup->getTable('booking_discounts');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableDiscount) == true) {
                // Declare data
                $columns = [
                    'discount_group' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        ['nullable' => false, 'default' => ''],
                        'comment' => 'Group Customer',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableDiscount, $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(), '2.1.3') < 0) {
            $tableCalendars = $setup->getTable('booking_calendars');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableCalendars) == true) {
                // Declare data
                $columns = [
                    'extract_persons' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        ['nullable' => true, 'default' => ''],
                        'comment' => 'Group Customer',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableCalendars, $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(), '2.1.4') < 0) {
            $tableBk = $setup->getTable('booking_systems');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableBk) == true) {
                // Declare data
                $columns = [
                    'booking_tour_type' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        ['nullable' => true, 'default' => 0],
                        'comment' => 'Bk Tour Type',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableBk, $name, $definition);
                }
            }
            $tableInters = $setup->getTable('booking_intervalhours');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableInters) == true) {
                // Declare data
                $columns = [
                    'intervalhours_days' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        ['nullable' => true, 'default' => ''],
                        'comment' => 'Enable Days',
                    ],
                ];
                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableInters, $name, $definition);
                }
            }
        }
        $setup->endSetup();
    }
}