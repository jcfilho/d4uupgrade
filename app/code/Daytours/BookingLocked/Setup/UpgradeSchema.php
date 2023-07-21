<?php

namespace Daytours\BookingLocked\Setup;

use Daytours\BookingLocked\Model\BookingLocked;
use Magento\Framework\DB\Ddl\Table as TableAlias;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();


        if (version_compare($context->getVersion(), '0.1.1', '<')) {
            $connection = $setup->getConnection();
            $connection->addColumn($setup->getTable( BookingLocked::TABLE_NAME),'calendar_number',
                [
                    'type' => TableAlias::TYPE_INTEGER,
                    'length' => null,
                    'nullable' => true,
                    'comment' => 'Calendar number',
                    'default' => 1
                ]
            );
        }


        $setup->endSetup();
    }
}