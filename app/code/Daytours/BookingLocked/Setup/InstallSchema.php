<?php

namespace Daytours\BookingLocked\Setup;

use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     *
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        // Create booking_blocked_date table
        $bookingSystems = $setup->getTable('booking_locked_date');
        $tableLockedDate = $setup->getConnection()
            ->newTable($bookingSystems)
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true
                ],
                'Entity Id'
            )->addColumn(
                'booking_product_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => true],
                'Product Id'
            )->addColumn(
                'locked_date',
                Table::TYPE_DATE,
                null,
                ['nullable' => true],
                'Blocked date'
            )->addIndex(
                $installer->getIdxName('booking_locked_date', ['booking_product_id']),
                ['booking_product_id']
            )
            ->addForeignKey(
                $installer->getFkName(
                    'booking_locked_date',
                    'booking_product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'booking_product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            )
            ->setComment('Main Table Booking Locked date')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $setup->getConnection()->createTable($tableLockedDate);

        $installer->endSetup();
    }
}