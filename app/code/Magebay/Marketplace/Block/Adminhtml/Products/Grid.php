<?php 
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Products;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_productsCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebay\Marketplace\Model\ProductsFactory $productsFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_productsCollection = $productsFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('productsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('products_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productsCollection->create()->getCollection();
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
            'product_image',
            [
                'header' => __('Image'),
                'index' => 'product_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ProductsGridProductImage'
            ]
        );
        $this->addColumn(
            'seller_name',
            [
                'header' => __('Seller Name'),
                'index' => 'user_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ProductsGridSellerName'
            ]
        );
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'index' => 'product_id',
                'type'   => 'number',
            ]
        );
        $this->addColumn(
            'product_name',
            [
                'header' => __('Product Name'),
                'index' => 'product_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ProductsGridProductName'
            ]
        );
        $this->addColumn(
            'product_price',
            [
                'header' => __('Price'),
                'index' => 'product_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ProductsGridProductPrice'
            ]
        );
        $this->addColumn(
            'product_quatity',
            [
                'header' => __('Quatity'),
                'index' => 'product_id',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ProductsGridProductQuatity'
            ]
        );
        $this->addColumn(
            'product_created',
            [
                'header' => __('Created'),
                'index' => 'created',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'product_modified',
            [
                'header' => __('Modified'),
                'index' => 'modified',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'product_status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => array(
                    '0'=>'Pending',
                    '1'=>'Approved',
                    '2'=>'Unapproved',
                    '3'=>'Not Submitted'
                ),
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ProductsGridProductStatus'
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
                'label' => __('Approval'),
                'url' => $this->getUrl('*/*/massStatus/status/1/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to Approval selected items?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'unapprove',
            [
                'label' => __('Unapproval'),
                'url' => $this->getUrl('*/*/massStatus/status/2/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to Unapproval selected items?"
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