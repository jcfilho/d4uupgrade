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

class SellerBecome extends \Magento\Framework\View\Element\Template
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
    
    function becomeSeller()
	{
        $customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$tableSellers = $this->_resource->getTableName('multivendor_user');
		$customerModel = $this->_customerFactory->create();
		$sellers = $customerModel->getCollection();
		$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))
			->where('table_sellers.userstatus = 0');
        if($sellerid > 0)
		{
			$sellers->getSelect()->where('table_sellers.user_id=?',$sellerid);
		}                        
		return $sellers;
	}
    
    public function checkIsLogin(){  
        $customerSession = $this->_customerSession;
        return $customerSession->getId();
    }
	
	public function checkIsSeller(){   
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
        $_dataObject = $customerSession->getCustomerData();
        if(is_object($_dataObject)){
            $_customerId = $_dataObject->getId();
            $_customerCollection = $this->getSellerById($_customerId);
            if(!count($_customerCollection)) return false;
            if($_customerCollection[0]['is_vendor'] && $_customerCollection[0]['userstatus'])
            $flag = true;
        }
        return $flag;
    }
	 
    public function getSellerById($id){  
        $sellerCollection = $this->_objectmanager->create('Magebay\Marketplace\Model\ResourceModel\Sellers\Collection')->addFieldToFilter('user_id',$id);
        return $sellerCollection->getData();
    }
}