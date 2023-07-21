<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block\Adminhtml;

class Orders extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'orders/view.phtml';
    protected $_customerCollectionFactory;
    protected $_sellersCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magebay\Marketplace\Model\SellersFactory $sellersFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_sellersCollectionFactory = $sellersFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create orders , edit/add orders row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Orders
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'orders',
            $this->getLayout()->createBlock('Magebay\Marketplace\Block\Adminhtml\Orders\Grid', 'orders.view.grid')
        );
        return parent::_prepareLayout();
    }
                
    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'orders/*/new'
        );
    }
 
    /**
     * Render orders
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('orders');
    }
    
    public function getInforSeller()
    {
        return $this->_customerCollectionFactory->create()->load($this->getRequest()->getParam('sellerid'));
    }
    
    public function getInforPaymentSeller()
    {
        $data = $this->_sellersCollectionFactory->create()->getCollection()->addFieldToFilter('user_id',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $data['paymentsource'];
    }
    
    public function getTotalAmounPay()
    {
        $data = $this->_objectmanager->create('Magebay\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $data['amountremain'];
    }
    
    public function getTotalAmounSeller()
    {
        $data = $this->_objectmanager->create('Magebay\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $this->_objectmanager->create('Magento\Framework\Pricing\Helper\Data')->currency($data['amountremain']);
    }
    
    public function getTotalAmounReceivedSeller()
    {
        $data = $this->_objectmanager->create('Magebay\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$this->getRequest()->getParam('sellerid'))->getFirstItem();
        return $this->_objectmanager->create('Magento\Framework\Pricing\Helper\Data')->currency($data['amountreceived']);
    }
    
    public function getBackUrl(){
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/orders/index', array('sellerid'=>$this->getRequest()->getParam('sellerid')));
        return $url;
    }
    
    public function getPayAction(){
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/pay/index', array('sellerid'=>$this->getRequest()->getParam('sellerid')));
        return $url;
    }
}