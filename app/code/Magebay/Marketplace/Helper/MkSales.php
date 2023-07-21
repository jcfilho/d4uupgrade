<?php
/**
 * @Author      : Dream + Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Helper;
/**
 * Custom Module Email helper
 */
class MkSales extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_resource;
    protected $_customerFactory;
    protected $_objectmanager;
    protected $_mkProduct;
    protected $_saleslist;
	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magebay\Marketplace\Model\ProductsFactory $mkProduct,
        \Magebay\Marketplace\Model\SaleslistFactory  $saleslist
    ) {
        parent::__construct($context);
		$this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
        $this->_objectmanager = $objectmanager; 
        $this->_mkProduct = $mkProduct;
        $this->_saleslist = $saleslist;
    }
    /* 
	* get seller name for product in order detail
	*/
	function getSellerNameSalesOrder($orderId,$productId,$productPrice)
	{
        $seller = null;
        $selesListModel = $this->_saleslist->create()->getCollection()
                        ->addFieldToFilter('orderid',$orderId)
                        ->addFieldToFilter('prodid',$productId)
                        ->addFieldToFilter('proprice',$productPrice);
        
        //Kien 19/5/2016 - update filter      
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $selesListModel->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.sellerid = mk_user.user_id',array('*'));
        if($selesListModel->getFirstItem() && $selesListModel->getFirstItem()->getId())
		{
            $seller = $selesListModel->getFirstItem();
        }
        return $seller;
	}
    /* 
	* get seller name for product in checkout cart page
	*/
	function getSellerNameCheckoutCart($productId)
	{
        $seller = null;
        $mkProductData = $this->_mkProduct->create()->getCollection()
                        ->addFieldToFilter('product_id',$productId)
                        ->addFieldToFilter('status',1);
                        
        //Kien 19/5/2016 - update filter seller approve        
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $mkProductData->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
            ->where('mk_user.userstatus = 1');
            
        $mkProductData = $mkProductData->getFirstItem();
    	if($mkProductData && $mkProductData->getId())
		{
			$useId = $mkProductData->getUserId();
			$tableSellers = $this->_resource->getTableName('multivendor_user');
			$customerModel = $this->_customerFactory->create();
			$sellers = $customerModel->getCollection();
			$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))
				->where('table_sellers.userstatus = 1')
				->where('table_sellers.user_id = ?',$useId);
			$seller = $sellers->getFirstItem();
		}
        return $seller;
	}
	
	/* David : check order of seller */
	function checkSellerOrder($orderid , $sellerid )
	{
        $seller = null;
        $mkOrderData = $this->_saleslist->create()->getCollection()
                        ->addFieldToFilter('orderid',$orderid)
                        ->addFieldToFilter('sellerid',$sellerid);
        $sellerid = $mkOrderData->getLastItem()->getSellerid();  
        return $sellerid;
	}
}