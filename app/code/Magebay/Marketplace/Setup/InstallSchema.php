<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Setup;
 
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
 
        // Check if the table multivendor_product already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_product')) != true) {
            // Create multivendor_product table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_product')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'user_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'User Id'
                )
                ->addColumn(
                    'store_ids',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Store Ids'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Status'
                )
                ->addColumn(
                    'adminassign',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Admin Assign'
                )
                ->addColumn(
                    'created',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created'
                )
                ->addColumn(
                    'modified',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Modified'
                )
                ->addColumn(
                    'position',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Position'
                )                
                ->setComment('Multivendor Product Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_reviews already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_reviews')) != true) {
            // Create multivendor_reviews table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_reviews')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'userid',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'User Id'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Status'
                )
                ->addColumn(
                    'user_review_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'User Review Id'
                )
                ->addColumn(
                    'price',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Price'
                )
                ->addColumn(
                    'value',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Value'
                )
                ->addColumn(
                    'quality',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Quality'
                )
                ->addColumn(
                    'nickname',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Nickname'
                )
                ->addColumn(
                    'summary',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Summary'
                )
                ->addColumn(
                    'review',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Review'
                )
                ->addColumn(
                    'createdate',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created Date'
                )              
                ->setComment('Multivendor Reviews Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_saleperpartner already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_saleperpartner')) != true) {
            // Create multivendor_saleperpartner table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_saleperpartner')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'sellerid',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Seller Id'
                )
                ->addColumn(
                    'totalsale',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Totalsale'
                )
                ->addColumn(
                    'amountreceived',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Amountreceived'
                )
                ->addColumn(
                    'amountpaid',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Amountpaid'
                )
                ->addColumn(
                    'amountremain',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Amountremain'
                )
                ->addColumn(
                    'commission',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Commission'
                )
                ->addColumn(
                    'discount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Discount'
                )
                ->setComment('Multivendor Sale Per Partner Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_saleslist already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_saleslist')) != true) {
            // Create multivendor_saleslist table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_saleslist')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'realorderid',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Realorder Id'
                )
                ->addColumn(
                    'orderid',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Order Id'
                )
                ->addColumn(
                    'prodid',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'sellerid',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Seller Id'
                )
                ->addColumn(
                    'buyerid',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Buyer Id'
                )
                ->addColumn(
                    'order_status',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Order Status'
                )
                ->addColumn(
                    'proprice',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Product Price'
                )
                ->addColumn(
                    'proname',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Product Name'
                )
                ->addColumn(
                    'proqty',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product Qty'
                )
                ->addColumn(
                    'totalamount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Total Amount'
                )
                ->addColumn(
                    'totalcommision',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Total Commision'
                )
                ->addColumn(
                    'actualparterprocost',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Actual Parter Product Cost'
                )
                ->addColumn(
                    'paidstatus',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Paid Status'
                )
                ->addColumn(
                    'transid',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Transaction Id'
                )
                ->addColumn(
                    'totaltax',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Total Tax'
                )
                ->setComment('Multivendor Sales List Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_sellertransaction already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_sellertransaction')) != true) {
            // Create multivendor_sellertransaction table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_sellertransaction')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'seller_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Seller Id'
                )
                ->addColumn(
                    'transaction_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Transaction Id'
                )
                ->addColumn(
                    'transaction_id_online',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Transaction Id'
                )
                ->addColumn(
                    'payment_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Payment Id'
                )
                ->addColumn(
                    'payment_email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Email'
                )
                ->addColumn(
                    'payment_additional',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Additional'
                )
                ->addColumn(
                    'transaction_amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Transaction Amount'
                )
                ->addColumn(
                    'amount_paid',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Amount Paid'
                )
                ->addColumn(
                    'amount_fee',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Amount Fee'
                )
                ->addColumn(
                    'commision',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Commision'
                )
                ->addColumn(
                    'admin_comment',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Admin Comment'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'paid_status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Paid Status'
                )
                ->setComment('Multivendor Seller Transaction Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_user already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_user')) != true) {
            // Create multivendor_user table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_user')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'is_vendor',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Is Vendor'
                )
                ->addColumn(
                    'userstatus',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'User Status'
                )
                ->addColumn(
                    'user_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'User Id'
                )
                ->addColumn(
                    'stores_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Stores Id'
                )
                ->addColumn(
                    'priority',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Priority'
                )
                ->addColumn(
                    'storeurl',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Store Url'
                )
                ->addColumn(
                    'storetitle',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Store Title'
                )
                ->addColumn(
                    'bannerimg',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Banner Img'
                )
                ->addColumn(
                    'logoimg',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Logo Img'
                )
                ->addColumn(
                    'short_description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Short Description'
                )
				->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Description'
                )				
                ->addColumn(
                    'meta_keyword',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Meta Keyword'
                )
                ->addColumn(
                    'meta_description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Meta Description'
                )
                ->addColumn(
                    'contactnumber',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Contact Number'
                )
                ->addColumn(
                    'returnpolicy',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Return Policy'
                )
                ->addColumn(
                    'shippingpolicy',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Shipping Policy'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Email'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Name'
                )
                ->addColumn(
                    'company',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Company'
                )
                ->addColumn(
                    'address',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Address'
                )
                ->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'City'
                )
                ->addColumn(
                    'zipcode',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Zipcode'
                )
                ->addColumn(
                    'country',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Country'
                )
                ->addColumn(
                    'state',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'State'
                )
                ->addColumn(
                    'paymentsource',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Payment Source'
                )
                ->addColumn(
                    'facebookid',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Facebook Id'
                )
                ->addColumn(
                    'twitterid',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Twitter Id'
                )
                ->addColumn(
                    'instagram_url',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Instagram Url'
                )
                ->addColumn(
                    'google_plus',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Google Plus Url'
                )   
                ->addColumn(
                    'youtube_url',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Youtube Url'
                )   
                ->addColumn(
                    'pinterest_url',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Pinterest Url'
                )  
                ->addColumn(
                    'vimeo_url',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Vimeo Url'
                )
                ->addColumn(
                    'created',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created'
                )
                ->addColumn(
                    'commission',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Commission'
                )
                ->setComment('Multivendor User Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_payment already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_payment')) != true) {
            // Create multivendor_payment table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_payment')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Name'
                )
                ->addColumn(
                    'fee',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Fee'
                )
                ->addColumn(
                    'minamount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Min Amount'
                )
                ->addColumn(
                    'maxamount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Max Amount'
                )
                ->addColumn(
                    'sortorder',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Sort Order'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Description'
                )
                ->addColumn(
                    'note',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Note'
                )
                ->addColumn(
                    'email_account',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Email Account'
                )
                ->addColumn(
                    'additional',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Additional'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Multivendor Payment Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}