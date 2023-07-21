<?php 
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Reviews;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_reviewsCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebay\Marketplace\Model\ReviewsFactory $reviewsFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_reviewsCollection = $reviewsFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reviewsGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('reviews_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_reviewsCollection->create()->getCollection();
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
            'userid',
            [
                'header' => __('Seller Name'),
                'index' => 'userid',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ReviewsGridSellerName'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price Rating(star)'),
                'index' => 'price',
                'type'   => 'number',
            ]
        );
        $this->addColumn(
            'value',
            [
                'header' => __('Value Rating(star)'),
                'index' => 'value',
                'type'   => 'number',
            ]
        );
        $this->addColumn(
            'quality',
            [
                'header' => __('Quality Rating(star)'),
                'index' => 'quality',
                'type'   => 'number',
            ]
        );
        $this->addColumn(
            'summary',
            [
                'header' => __('Summary'),
                'index' => 'summary',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'review',
            [
                'header' => __('Review'),
                'index' => 'review',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'createdate',
            [
                'header' => __('Created'),
                'index' => 'createdate',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'review_status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => array(
                    '1'=>'Approve',
                    '0'=>'Disapprove'
                ),
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\ReviewsGridStatus'
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
                'confirm' => "Are you sure you wan't to approve selected customer as feedback?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'disapprove',
            [
                'label' => __('Disapprove'),
                'url' => $this->getUrl('*/*/massStatus/status/0/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to disapprove selected feedback as default customer?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/massDelete/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to delete selected feedback as default customer?"
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