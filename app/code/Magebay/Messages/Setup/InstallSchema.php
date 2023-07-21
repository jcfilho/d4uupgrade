<?php
namespace Magebay\Messages\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'multivendor_messages'
         */
		if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_messages_reply')) != true) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_messages')
            )->addColumn(
                    'messages_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'ID'
                )->addColumn(
                    'user_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    ['nullable' => true],
                    'User Id'
                )->addColumn(
                    'usercontact_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    255,
                    ['nullable' => true],
                    'User Contact Id'
                )->addColumn(
                    'title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Title'
                )->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => true],
                    'Description'
                )->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable' => true],
                    'Product Id'
                )->addColumn(
                    'order_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    11,
                    ['nullable' => true],
                    'Order Id'
                )->addColumn(
                    'is_active',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '1'],
                    'Is Post Active'
                )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )->addColumn(
                    'reply_date',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Reply Date'
                )->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true],
                    'Status'
                )
                ->setComment('Multivendor Messages Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
		}

		/**
         * Create table 'multivendor_messages_reply'
         */
		if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_messages_reply')) != true) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_messages_reply')
            )->addColumn(
                    'reply_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Reply Id'
                )
                ->addColumn(
                    'messages_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Messages id'
                )
                ->addColumn(
                    'user_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'User Id'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Description'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created at'
                )              
                ->setComment('Multivendor Messages Reply Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}