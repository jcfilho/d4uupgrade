<?php 
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magebay\Marketplace\Block\Adminhtml\Transactions;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_transactionsCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebay\Marketplace\Model\TransactionsFactory $transactionsFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_transactionsCollection = $transactionsFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('transactionsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('transactions_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_transactionsCollection->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
 
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'seller_id1',
            [
                'header' => __('Seller Id'),
                'index' => 'seller_id'
            ]
        );
        $this->addColumn(
            'seller_id',
            [
                'header' => __('Seller Name'),
                'index' => 'seller_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\TransactionsGridSellerName'
            ]
        );
        $this->addColumn(
            'transaction_id',
            [
                'header' => __('Transaction ID'),
                'index' => 'transaction_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\TransactionsGridTransactionId'
            ]
        );
        $this->addColumn(
            'transaction_amount',
            [
                'header' => __('Amount'),
                'index' => 'transaction_amount',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\TransactionsGridAmount'
            ]
        );
        $this->addColumn(
            'admin_comment',
            [
                'header' => __('Comment'),
                'index' => 'admin_comment',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'paid_status',
            [
                'header' => __('Payment Status'),
                'index' => 'paid_status',
                'type' => 'options',
                'options' => array(
                    '1'=>'Pending',
                    '2'=>'Completed',
                    '3'=>'Canceled'
                ),
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\TransactionsGridPaymentsStatus'
            ]
        );
        $this->addColumn(
            'id',
            [
                'header' => __('Pay Action'),
                'index' => 'id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Renderer\PayForSeller'
            ]
        );
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
    }
 
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    
    public function getRowUrl($row)
    {
        return '#';
    }
    
    public function getpaidStatuses(){
        return array(
            '0'=>'Pending',
            '1'=>'Paid',
            '2'=>'Hold',
            '3'=>'Refunded',
            '4'=>'Voided'
        );
    }
    
    public function getOrderStatuses(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	return $objectManager->create('Magento\Sales\Model\Order\Config')->getStatuses();
    }
}