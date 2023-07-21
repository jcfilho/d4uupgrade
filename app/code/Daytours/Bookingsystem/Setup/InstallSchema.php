<?php
 
namespace Daytours\Bookingsystem\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Get module table
        $bookingCalendars = $installer->getTable('booking_calendars');
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($bookingCalendars) == true) {
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
                $connection->addColumn($bookingCalendars, $name, $definition);
            }

        }

        $bookingOrders = $installer->getTable('booking_orders');
        // Check if the table already exists
        if ($setup->getConnection()->isTableExists($bookingOrders) == true) {
            // Declare data
            $columns = [
                'bkorder_check_in_two' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Checkin calendar two',
                ],
                'bkorder_check_out_two' => [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                    'nullable' => true,
                    'comment' => 'Checkout calendar two',
                ],
            ];
            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($bookingOrders, $name, $definition);
            }

        }

        $installer->endSetup();
    }
}
 