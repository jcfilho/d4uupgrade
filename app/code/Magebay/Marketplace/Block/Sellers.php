<?php
/**
 * @Author      : Dream
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Registry;
use Magebay\Marketplace\Model\ReviewsFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Helper\Image as CatalogImages;
use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Session as ModelSession;
use Magento\Review\Model\Review\SummaryFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Data\Form\FormKey;
use Magebay\Marketplace\Model\ProductsFactory as MkProduct;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Sales\Model\OrderFactory;
class Sellers extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_directoriesFactory;	
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	protected $_resource;
	protected $_modelSession;
    protected $_product;
    protected $_reviewsFactory;
    protected $_summaryFactory;
    protected $_customerFactory;
    protected $_formKey;
    protected $_mkProduct;
    protected $_country;
    protected $_mkCoreOrder;    
  
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $directoriesFactory,
		Registry $coreRegistry,
		CatalogImages $catalogImages,
		ResourceConnection $resource,
		Product $product,
		ModelSession $modelSession,
		ReviewsFactory  $reviewsFactory,
		SummaryFactory  $summaryFactory,
		CustomerFactory  $customerFactory,
		FormKey  $formKey,
		MkProduct $mkProduct,
		Country $country,
        OrderFactory $mkCoreOrder,        
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_directoriesFactory = $directoriesFactory;
		$this->_coreRegistry = $coreRegistry;
		$this->_catalogImages = $catalogImages;
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_reviewsFactory = $reviewsFactory;
		$this->_summaryFactory = $summaryFactory;
		$this->_customerFactory = $customerFactory;
		$this->_formKey = $formKey;
		$this->_mkProduct = $mkProduct;
		$this->_country = $country;
        $this->_mkCoreOrder = $mkCoreOrder;        
    }
	/**
	* get list Sellers
	* @return $items
	**/
	function getSellerProfile($_pId = null)
	{
		$seller = null;
		if($this->_coreRegistry->registry('seller_profile'))
		{
			$seller = $this->_coreRegistry->registry('seller_profile');
		}
		elseif($this->_coreRegistry->registry('product') or $_pId)
		{
			if($_product = $this->_coreRegistry->registry('product')){
				$productId = $_product->getId();
			}else{
				$productId = $_pId;
			}			
			$mkProductModel = $this->_mkProduct->create();
			$mkCollection = $mkProductModel->getCollection()
				->addFieldToFilter('product_id',$productId)
                ->addFieldToFilter('status',1);
                
            //Kien 19/5/2016 - update filter seller approve        
            $tableMKuser = $this->_resource->getTableName('multivendor_user');
            $mkCollection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                ->where('mk_user.userstatus = 1');
            
			$mkProductData = $mkCollection->getFirstItem();
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
		}
		return $seller;
	}
	/**
	* get seller reviews
	**/
	function getMKsellerReview($userId)
	{
		$reviewsModel = $this->_reviewsFactory->create();
		return $reviewsModel->getMKReview($userId);
	}
	/* 
	* get list reivew
	*/
	function getMKReViewItem($useId)
	{
		$reviewModel = $this->_reviewsFactory->create();
		$customerTable = $this->_resource->getTableName('customer_entity');
		$collection = $reviewModel->getCollection();
		$collection->addFieldToFilter('status',1);
		$collection->addFieldToFilter('userid',$useId);
		$collection->getSelect()->joinLeft(array('table_custmer'=>$customerTable),'main_table.user_review_id = table_custmer.entity_id',array('firstname','lastname'));
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
		return $collection;
	}
    protected function _prepareLayout()
    {
        if($this->getRequest()->getActionName() == 'review'){
            $seller = $this->getSellerProfile();
            $collection = $this->getMKReViewItem($seller->getId());
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
    }
    /**
     * @return method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    } 
	function getMkProduct()
	{
		if($this->_coreRegistry->registry('product'))
		{
			return $this->_coreRegistry->registry('product');
		}
		return null;
	}
	/* 
	* get str country
	* param $countryId
	*/
	function getStrCountry($countryId)
	{
		$countries = $this->_country->toOptionArray();
		$strCountry = '';
		foreach($countries as $country)
		{
			if($country['value'] == $countryId)
			{
				$strCountry = $country['label'];
				break;
			}
		}
		return $strCountry;
	}
	function getMKFormKey()
	{
		return $this->_formKey->getFormKey();
	}
	function getMkConfig($field)
	{
		return $this->_scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE);
	}
	function getMkBaseMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
	function getBkCatalogHelper()
	{
		return $this->_catalogImages;
	}
	/**
	* check customer session 
	**/
	function checkMkCustomerLogin()
	{
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Session')->isLoggedIn();	   	   	   	   	   	   	   
		if($customer){
			return true;
		}else{
			return false;
		}
	}
    function checkCustomerOrderSeller($seller_id,$customer_email)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ReviewOnOnlyOrderPurchase = $objectManager->create('Magebay\Marketplace\Helper\Data')->getReviewOnOnlyOrderPurchase();	   	   
        if($ReviewOnOnlyOrderPurchase){
            $orders = $this->_mkCoreOrder->create()->getCollection();
            $tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
    		$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('sellerid')
            );
    		$orders->getSelect()->where('mk_sales_list.sellerid=?',$seller_id);
            $orders->getSelect()->where('main_table.customer_email=?',$customer_email);
            $orders->getSelect()->group('main_table.entity_id');
            return count($orders);
        }else{
            return true;
        }
    }  
    /**
     * @return array
     */
    public function _getOptionsCountry()
    {
        $options = $this->_directoriesFactory->create()->load()->toOptionArray(false);
        array_unshift($options, ['value' => '', 'label' => __('All Countries')]);
        return $options;
    }
}