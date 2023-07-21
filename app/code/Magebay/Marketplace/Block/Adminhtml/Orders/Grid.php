<?php 
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Orders;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_ordersCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebay\Marketplace\Model\SaleslistFactory $ordersFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_ordersCollection = $ordersFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ordersGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('orders_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_ordersCollection->create()->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'));;
        $this->setCollection($collection);
        parent::_prepareCollection();
        foreach ($collection as $item) {

        } 
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
            'realorderid',
            [
                'header' => __('Order #'),
                'index' => 'realorderid',
            ]
        );
        $this->addColumn(
            'proname',
            [
                'header' => __('Product Name'),
                'index' => 'proname'
            ]
        );
        $this->addColumn(
            'proqty',
            [
                'header' => __('Product QTY'),
                'index' => 'proqty',
                'type'	=> 'number',
            ]
        );
        $this->addColumn(
            'proprice',
            [
                'header' => __('Product Price'),
                'index' => 'proprice',
                'type'   => 'currency',
            ]
        );
        $this->addColumn(
            'totalamount',
            [
                'header' => __('Total Amount'),
                'index' => 'totalamount',
                'type'   => 'currency',
            ]
        );
        $this->addColumn(
            'totaltax',
            [
                'header' => __('Total Tax'),
                'index'  => 'totaltax',
                'type'   => 'currency',
            ]
        );
        $this->addColumn(
            'totalcommision',
            [
                'header' => __('Total Commision'),
                'index' => 'totalcommision',
                'type'   => 'currency',
            ]
        );
        $this->addColumn(
            'actualparterprocost',
            [
                'header' => __('Total Seller Amount'),
                'index' => 'actualparterprocost',
                'type'   => 'currency',
            ]
        );
        $this->addColumn(
            'order_status',
            [
                'header'  => __('Status Order'),
                'index'   => 'order_status',
                'type'    => 'options',
                'options' => $this->getOrderStatuses(),
            ]
        );
        /*$this->addColumn(
            'paidstatus',
            [
                'header' => __('Paid Status'),
                'index' => 'paidstatus',
                'type'    => 'options',
                'options' => $this->getpaidStatuses(),
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
        );*/
        //$item->view = '<a class="mst_sellerorderstatus" href="'.$this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('sales/order/view', array('order_id'=>$item->getOrderid())).'" title="'.__('View Order').'">'.__('View Order').'</a>';
        $this->addColumn(
            'vieworder',
            [
                'header' => __('View Order'),
                'type' => 'action',
                'getter' => 'getOrderid',
                'actions' => [
                    [
                        'caption' => __('View Order'),
                        'url' => [
                            'base' => 'sales/order/view'
                        ],
                        'field' => 'order_id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
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