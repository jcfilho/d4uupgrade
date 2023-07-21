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

class SellerBackend extends \Magento\Framework\View\Element\Template
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
    //protected $_messages;
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
        //\Magebay\Messages\Model\Messages $messages,  
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
        $this->_transactionsFactory = $transactionsFactory;
        $this->_partnerFactory = $partnerFactory;   
        $this->_objectmanager = $objectmanager;   
        $this->_customerSession = $customerSession;   
        //$this->_messages = $messages;   

    }
    
	/* Customize  */
	function getSellerProfile()
	{
		//$sellerId =  $this->_customerSession->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('\Magento\Customer\Model\Session');
		$sellerId =  $customer->getId();        
		$tableSellers = $this->_resource->getTableName('multivendor_user');
        $customerModel = $this->_customerFactory->create();
        $sellers = $customerModel->getCollection();
        $sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))
                             ->where('table_sellers.userstatus = 1');
        if($sellerId > 0)
        {
            $sellers->getSelect()->where('table_sellers.user_id=?',$sellerId);
            $seller = $sellers->getFirstItem();
        }
		
		return $seller;
	}
    
	function getMkBaseMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
	
	function getMessageUnread()
	{
        $customer = $this->_customerSession;
		$messages = \Magento\Framework\App\ObjectManager::getInstance()->create('Magebay\Messages\Model\Messages')->getCollection()->addFieldToFilter('is_active',1);
		//$messages->getSelect()->where('main_table.user_id=?', $customer->getId())->orWhere('main_table.usercontact_id=?', $customer->getId());
		$messages->getSelect()->where("main_table.user_id= '{$customer->getId()}' OR main_table.usercontact_id='{$customer->getId()}'");
		$ee = '%"'.$customer->getId().'":"unread"%';
		$messages->getSelect()->where("main_table.status LIKE '{$ee}'");
		return $messages;
	}
}