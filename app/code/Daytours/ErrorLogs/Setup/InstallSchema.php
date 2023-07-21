<?php 
namespace Daytours\ErrorLogs\Setup;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface{
    public function install(SchemaSetupInterface $setup,ModuleContextInterface $context){
        $setup->startSetup();
        $conn = $setup->getConnection();
        $tableName = $setup->getTable('error_logs');
        if($conn->isTableExists($tableName) != true){
            $table = $conn->newTable($tableName)
                            ->addColumn(
                                'id',
                                Table::TYPE_INTEGER,
                                null,
                                ['identity'=>true,'unsigned'=>true,'nullable'=>false,'primary'=>true]
                                )
                            ->addColumn(
                                'moduleName',
                                Table::TYPE_TEXT,
                                50,
                                ['nullable'=>false, 'default' => '']
                            )
                            ->addColumn(
                                'message',
                                Table::TYPE_TEXT,
                                256,
                                ['nullable'=>false, 'default' => '']
                            )
                            ->addColumn(
                                'date',
                                Table::TYPE_TEXT,
                                128,
                                ['nullable'=>false, 'default' => '']
                            )
                            ->addColumn(
                                'location',
                                Table::TYPE_TEXT,
                                256,
                                ['nullable'=>false, 'default' => '']
                            )
                            ->setOption('charset','utf8');
            $conn->createTable($table);
        }
        $setup->endSetup();
    }
}
