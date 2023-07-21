<?php

namespace Daytours\Provider\Setup;

use Daytours\Provider\Model\Provider as ProviderAlias;
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

        // Create booking_provider table
        $bookingProvider = $setup->getTable(ProviderAlias::TABLE_NAME);
        $table = $setup->getConnection()
            ->newTable($bookingProvider)
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
                'name',
                Table::TYPE_TEXT,
                300,
                ['nullable' => true],
                'Name'
            )->addColumn(
                'email',
                Table::TYPE_TEXT,
                300,
                ['nullable' => true],
                'Email provider'
            )
            ->setComment('Main Table Booking providers')
            ->setOption('type', 'InnoDB')
            ->setOption('charset', 'utf8');
        $setup->getConnection()->createTable($table);

        $installer->endSetup();
    }
}