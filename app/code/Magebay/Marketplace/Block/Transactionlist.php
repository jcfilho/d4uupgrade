<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\CustomerFactory;

class Transactionlist extends \Magento\Framework\View\Element\Template
{
	/**
     *
     * @var Magento\Framework\App\ResourceConnection
    */
	protected $_resource;
	protected $_transactionsFactory;    
    protected $_partnerFactory;   
    protected $_objectmanager;
    protected $_customerSession;
    protected $_priceHelper;
    protected $_saleslistFactory;
	/**
	* var \Magento\Customer\Model\Customer;
	**/
	protected $_customerFactory;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		ResourceConnection $resource,
		CustomerFactory $customerFactory,
        \Magebay\Marketplace\Model\TransactionsFactory $transactionsFactory,  
        \Magebay\Marketplace\Model\PartnerFactory $partnerFactory,      
        \Magento\Framework\ObjectManagerInterface $objectmanager,  
        \Magento\Customer\Model\Session $customerSession,  
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magebay\Marketplace\Model\SaleslistFactory $saleslistFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
        $this->_transactionsFactory = $transactionsFactory;
        $this->_partnerFactory = $partnerFactory;   
        $this->_objectmanager = $objectmanager;   
        $this->_customerSession = $customerSession;   
        $this->_priceHelper = $priceHelper;
		$this->_saleslistFactory = $saleslistFactory;
    }
    
    public function getPaymentMethods()
    {
        return $this->_objectmanager->create('Magebay\Marketplace\Model\Payments')->getCollection()
                                                                                  ->addFieldToFilter('status',1)   
                                                                                  ->setOrder('sortorder','ASC');
    }
    
    public function getPaymentMethodById($id)
    {
        return $this->_objectmanager->create('Magebay\Marketplace\Model\Payments')->load($id)->getData();
    }
    
    public function getPrice($price)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($price,true,false);
    }
    
    public function getPendingAmount($seller_id)
    {
        $data = $this->_objectmanager->create('Magebay\Marketplace\Model\Transactions')->getCollection()
                                                                                       ->addFieldToFilter('seller_id',$seller_id)
                                                                                       ->addFieldToFilter('paid_status',1);
        $value = 0;
        foreach($data as $dt){
            $value = $value + $dt['transaction_amount'];
        }
        return $value;
    }
    
    public function checkAmountPay($can_withdraw,$amount)
    {
        if($can_withdraw >= $amount){
            return true;
        }else{
            return false;
        }
    }
    
    function getDetailTransaction()
	{
        $customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
        $collection = $this->_partnerFactory->create()->getCollection()->addFieldToFilter('sellerid',$sellerid)->getFirstItem();
        return $collection;
	}
	/**
	* get list Transactions
	* @return $items
	**/
	function getTransactions()
	{
	   	$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
	   	$collection = null;
        if($sellerid > 0)
		{
            $collection = $this->_transactionsFactory->create()->getCollection()->addFieldToFilter('seller_id',$sellerid);
            $params = $this->getRequest()->getPost();
        	if(count($params)){
        		if(isset($params['transaction_id']) && $params['transaction_id'] != ''){
        			$transactionId = trim($params['transaction_id']);
        			$collection->addFieldToFilter('transaction_id',array('like'=>'%'.$transactionId.'%'));	
        		}
        		$fromDate = isset($params['from_date']) ? trim($params['from_date']) : '';
        		$toDate = isset($params['to_date']) ? trim($params['to_date']) : '';
        		if($fromDate != '' && $toDate == ''){
        			$collection->addFieldToFilter('created_at',array('gteq'=>$fromDate));
        		}elseif($fromDate == '' && $toDate != ''){
        			$collection->addFieldToFilter('created_at',array('lteq'=>$toDate));
        		}elseif($fromDate != '' && $toDate != ''){
        			$collection->addFieldToFilter('created_at',array('gteq'=>$fromDate));
        			$collection->addFieldToFilter('created_at',array('lteq'=>$toDate));
        		}
            }
            $collection->setOrder('id','DESC');
            $limit = $this->getRequest()->getParam('limit',5);
			if($limit > 0)
			{
				$collection->setPageSize($limit);
			}
            $curPage = $this->getRequest()->getParam('p',1);
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
        }
        return $collection;
	}
    
    protected function _prepareLayout()
    {
        $collection = $this->getTransactions();
        parent::_prepareLayout();
        if ($collection) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all')); 
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }

    /**
     * @return method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    } 
    
    /* 
	* get Price Helper
	* return \Magento\Framework\Pricing\Helper\Data
	*/
	function getMkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/*
	* get Current Customer Id
	*/
	function getMkCurrentCustomerId()
	{
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		return $sellerid;
	}
	/**
	* get detail transaction
	* return $item
	**/
	function getMkDetailTransaction()
	{
		$transactionId = $this->getRequest()->getParam('id',0);
		$transaction = null;
		$customerSession = $this->_customerSession;
		$customerId = $customerSession->getId();
		if($customerSession->isLoggedIn())
		{
			if($transactionId > 0)
			{
				$collection = $this->_transactionsFactory->create()->getCollection()
                                                                    ->addFieldToFilter('id',$transactionId)
                                                                    ->addFieldToFilter('seller_id',$customerId)->getFirstItem();
			}
		}
		return $collection;
	}
    
    function getMkDetailTransactionForPay($transactionId)
	{
		$transaction = null;
		if($transactionId > 0)
		{
			$collection = $this->_transactionsFactory->create()->getCollection()
                                                                ->addFieldToFilter('id',$transactionId)
                                                                ->getFirstItem();
		}
		return $collection;
	}
    
    function getDetailPartnerForPay($sellerid)
	{
        $collection = $this->_partnerFactory->create()->getCollection()->addFieldToFilter('sellerid',$sellerid)->getFirstItem();
        return $collection;
	}
    
    public function getSellerDetailById($seller_id){
		$customer = $this->_objectmanager->create('Magento\Customer\Model\Customer')->load( $seller_id );
		$customer_name = $customer->getData('firstname') . ' ' . $customer->getData('lastname');
        return $customer_name;
    }
    /* 
	* get data from saleslist model
	*/
	function getSalelist($transactionId)
	{
		$collection = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
                        					 ->addFieldToFilter('transid',$transactionId)
                        					 ->addFieldToFilter('sellerid',$sellerid);
			}
		}
		return $collection;
	}
	
	/**
	* get order address
	* return $item
	**/
	function getMkOrderAdderess($addressId)
	{
		$addressModel = $this->_orderAddess;
		return $addressModel->load($addressId);
	}
	function getBkCountryName($code)
	{
		$country = $this->_country->loadByCode($code);
		$name = '';
		if($country->getId())
		{
			$name = $country->getName();
		}
		return $name;
	}
	/**
	* get Mk config
	* @return string 
	**/
	function getMkConfig($field)
	{
		return $this->_scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE);
	}
	function getMkBaseMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
}