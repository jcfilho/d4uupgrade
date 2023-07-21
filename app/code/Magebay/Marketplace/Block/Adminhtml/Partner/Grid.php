<?php 
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml\Partner;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_partnerCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magebay\Marketplace\Model\PartnerFactory $partnerFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_partnerCollection = $partnerFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('partnerGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('partner_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_partnerCollection->create()->getCollection();
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
            'seller_name',
            [
                'header' => __('Seller Name'),
                'index' => 'sellerid',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerName'
            ]
        );
        $this->addColumn(
            'seller_email',
            [
                'header' => __('Seller Email'),
                'index' => 'sellerid',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerEmail'
            ]
        );
        $this->addColumn(
            'seller_commission',
            [
                'header' => __('Commission %'),
                'index' => 'sellerid',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerCommission'
            ]
        );
        $this->addColumn(
            'seller_total_sales',
            [
                'header' => __('Total Sales'),
                'index' => 'totalsale',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerFormatPrice'
            ]
        );
        $this->addColumn(
            'seller_total_commission',
            [
                'header' => __('Total Commission'),
                'index' => 'commission',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerFormatPrice'
            ]
        );
        $this->addColumn(
            'seller_amount_remain',
            [
                'header' => __('Amount Remain'),
                'index' => 'amountremain',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerFormatPrice'
            ]
        );
        $this->addColumn(
            'seller_amount_received',
            [
                'header' => __('Amount Received'),
                'index' => 'amountreceived',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerFormatPrice'
            ]
        );
        $this->addColumn(
            'seller_payout_order',
            [
                'header' => __('Orders'),
                'index' => 'sellerid',
                'filter' => false,
                'renderer' => 'Magebay\Marketplace\Block\Adminhtml\Grid\Column\PartnerGridSellerOrders'
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