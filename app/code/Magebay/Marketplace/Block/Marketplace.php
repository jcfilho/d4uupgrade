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

class Marketplace extends \Magento\Framework\View\Element\Template
{
	/**
     *
     * @var Magento\Framework\App\ResourceConnection
    */
	protected $_resource;
	/**
	* var \Magento\Customer\Model\Customer;
	**/
	protected $_customerFactory;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		ResourceConnection $resource,
		CustomerFactory $customerFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
    }
	/**
	* get list Sellers
	* @return $items
	**/
	function getSellers($sellerId = 0)
	{
		$tableSellers = $this->_resource->getTableName('multivendor_user');
		$customerModel = $this->_customerFactory->create();
		$sellers = $customerModel->getCollection();
		$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))
			->where('table_sellers.userstatus = 1');
		if($sellerId > 0)
		{
			$sellers->getSelect()->where('table_sellers.user_id=?',$sellerId);
		}
		return $sellers;
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