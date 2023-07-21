<?php 
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Sellers;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_sellersCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebay\Marketplace\Model\SellersFactory $sellersFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_sellersCollection = $sellersFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sellersGrid');
        $this->setDefaultSort('user_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('sellers_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_sellersCollection->create()->getCollection();
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
            'user_id',
            [
                'header' => __('Seller ID'),
                'type' => 'number',
                'index' => 'user_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'seller_name',
            [
                'header' => __('Seller Name'),
                'index' => 'user_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\SellersGridSellerName'
            ]
        );
        $this->addColumn(
            'seller_email',
            [
                'header' => __('Seller Email'),
                'index' => 'email',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'storetitle',
            [
                'header' => __('Store Title'),
                'index' => 'storetitle',
                'type'   => 'text',
            ]
        );  
        $this->addColumn(
            'seller_phone',
            [
                'header' => __('Phone'),
                'index' => 'contactnumber',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'seller_zip',
            [
                'header' => __('Zip'),
                'index' => 'zipcode',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'seller_country',
            [
                'header' => __('Country'),
                'index' => 'country',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'seller_state',
            [
                'header' => __('State/Province'),
                'index' => 'state',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'seller_created',
            [
                'header' => __('Seller Since'),
                'index' => 'created',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'seller_order',
            [
                'header' => __('Orders'),
                'index' => 'user_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\SellerGridSellerOrders'
            ]
        );
        $this->addColumn(
            'seller_status',
            [
                'header' => __('Seller Status'),
                'index' => 'userstatus',
                'type' => 'options',
                'options' => array(
                    '1'=>'Approve',
                    '0'=>'Disapprove'
                ),
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\SellersGridSellerStatus'
            ]
        );
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
    }
    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
 
        $this->getMassactionBlock()->addItem(
            'approve',
            [
                'label' => __('Approve'),
                'url' => $this->getUrl('*/*/massStatus/status/1/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to approve selected customer as seller?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'disapprove',
            [
                'label' => __('Disapprove'),
                'url' => $this->getUrl('*/*/massStatus/status/0/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to disapprove selected seller as default customer?"
            ]
        );
 
        return $this;
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