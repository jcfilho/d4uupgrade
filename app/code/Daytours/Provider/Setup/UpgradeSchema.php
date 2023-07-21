<?php

namespace Daytours\Provider\Setup;

use Daytours\Provider\Model\Provider as ProviderAlias;
use Magento\Framework\DB\Ddl\Table as TableAlias;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface as SchemaSetupInterfaceAlias;

class UpgradeSchema implements \Magento\Framework\Setup\UpgradeSchemaInterface
{
    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @param SchemaSetupInterfaceAlias $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterfaceAlias $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();


        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $connection = $setup->getConnection();
            $connection->addColumn($setup->getTable( ProviderAlias::TABLE_NAME),'phone',
                  [
                      'type' => TableAlias::TYPE_TEXT,
                      'length' => 255,
                      'nullable' => true,
                      'comment' => 'Provider phone'
                  ]
            );
        }


        $setup->endSetup();
    }
}