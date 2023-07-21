<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class DeleteProduct implements ObserverInterface
{
    protected $_resource; 
	protected $_mkProduct;
	protected $_scopeConfig;
	protected $_saleslist;
    protected $_sellerpartner;   
	
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
		\Magebay\Marketplace\Model\ProductsFactory $mkProduct,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magebay\Marketplace\Model\SaleslistFactory  $saleslist,
        \Magebay\Marketplace\Model\PartnerFactory  $partner        
    )
    {
        $this->_resource = $resource;
        $this->_mkProduct = $mkProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_saleslist = $saleslist;
        $this->_sellerpartner = $partner;
    }

    //Action for delete product
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_mkProduct->create()
			 ->load($observer->getProduct()->getId(),'product_id')
			 ->delete();	
    }
}
