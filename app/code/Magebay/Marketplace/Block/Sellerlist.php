<?php
/**
 * @Author      : Kien + Dream
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;
class Sellerlist extends \Magento\Framework\View\Element\Template
{
	protected $_customerSession;
	protected $_resource;
	protected $_mkCoreOrder;
	protected $_priceHelper;
	protected $_orderAddess;
	protected $_saleslistFactory;
	protected $_country;
    protected $_objectmanager;
    protected $_shippingConfig;
    protected $_coreRegistry = null;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Sales\Model\OrderFactory $mkCoreOrder,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Sales\Model\Order\Address $orderAddess,
		\Magento\Directory\Model\Country $country,
		\Magebay\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
		$this->_customerSession = $customerSession;
		$this->_resource = $resource;
		$this->_mkCoreOrder = $mkCoreOrder;
		$this->_priceHelper = $priceHelper;
		$this->_orderAddess = $orderAddess;
		$this->_country = $country;
		$this->_saleslistFactory = $saleslistFactory;
        $this->_objectmanager = $objectmanager; 
        $this->_shippingConfig = $shippingConfig;   
        $this->_coreRegistry = $registry;    
        parent::__construct($context, $data);
    }
	/* 
	* get List Sellers orders
	* @param int $cutomerId
	* return $items
	*/
	function getSellerOrders()
	{
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$orders = null;
		if($sellerid > 0)
		{
			//get all orders of seller
			$mkSalelistModel = $this->_saleslistFactory->create();
			$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
			$coreOrderModel = $this->_mkCoreOrder->create();
			$orders = $coreOrderModel->getCollection();
			$params = $this->getRequest()->getPost();
			if(count($params))
			{
				if(isset($params['order_id']) && $params['order_id'] != '')
				{
					$orderId = trim($params['order_id']);
					$orders->addFieldToFilter(array('entity_id','increment_id'),
												array(
													array('eq'=>$params['order_id']),
													array('like'=>'%'.$params['order_id'].'%')
												)	
											);	
				}
				$fromDate = isset($params['from_date']) ? trim($params['from_date']) : '';
				$toDate = isset($params['to_date']) ? trim($params['to_date']) : '';
				if($fromDate != '' && $toDate == '')
				{
					$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				}
				elseif($fromDate == '' && $toDate != '')
				{
					$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
				}
				elseif($fromDate != '' && $toDate != '')
				{
					$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
					$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
				}
				$orderStatus = isset($params['order_status']) ? trim($params['order_status']) : '';
				if($orderStatus != '')
				{
					$orders->addFieldToFilter('status',$orderStatus);
				}
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
            $orders->setOrder('id','DESC');
			$limit = $this->getRequest()->getParam('limit',5);
			if($limit > 0)
			{
				$orders->setPageSize($limit);
			}
			$curPage = $this->getRequest()->getParam('p',1);
			if($curPage > 1)
			{
				$orders->setCurPage($curPage);
			}
		}
		return $orders;
	}
    
    protected function _prepareLayout()
    {
        $collection = $this->getSellerOrders();
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
     
	/**
	* get Price Helper
	* return \Magento\Framework\Pricing\Helper\Data
	**/
	function getMkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/** 
	* get data from saleslist model
	**/
	function getSalelist($orderId,$porductId,$assignProduct)
	{
		$saleItem = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
					->addFieldToFilter('orderid',$orderId)
					->addFieldToFilter('prodid',$porductId)
					->addFieldToFilter('sellerid',$sellerid);
                    $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
                    //Assign product  
                    if($moduleManager->isEnabled('Magebay_SellerAssignProduct')){
                        $collection->addFieldToFilter('multivendor_assign_product_id',$assignProduct);
                    }  
                    //end Assign product  
				if(count($collection))
				{
					$saleItem = $collection->getFirstItem();
				}
			}
		}
		return $saleItem;
	}
	/**
	* get Current Customer Id
	**/
	function getMkCurrentCustomerId()
	{
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		return $sellerid;
	}
	/**
	* get detail order
	* return $item
	**/
	function getMkDetailOrder()
	{
		$orderId = $this->getRequest()->getParam('order_id',0);
		$order = null;
		$customerSession = $this->_customerSession;
		$customerId = $customerSession->getId();
		if($customerSession->isLoggedIn())
		{
			if($orderId > 0)
			{
				$collection = $this->getSellerOrders();
				$collection->addFieldToFilter('entity_id',$orderId);
				if(count($collection))
				{
					foreach($collection as $collect)
					{
						if($collect->getSellerid() == $customerId)
						{
							$order = $collect;
							break;
						}
						
					}
				}
			}
		}
		return $order;
	}
    /**
	* get total commision fix 5/9/2106 for product custom option by kien magebay.com
	* return $item
	**/
    public function getTotalcommision($order_id,$product_id,$product_price){
        $saleItem = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
					->addFieldToFilter('orderid',$order_id)
					->addFieldToFilter('prodid',$product_id)
					->addFieldToFilter('sellerid',$sellerid)
                    ->addFieldToFilter('proprice',$product_price);
				if(count($collection))
				{
					$saleItem = $collection->getFirstItem()->getTotalcommision();
				}
			}
		}
		return $saleItem;
    }
    /**
	* get actual parter procost fix 9/2/2016 for product custom option by kien magebay.com
	* return $item
	**/
    public function getActualparterprocost($order_id,$product_id,$product_price){
        $saleItem = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
					->addFieldToFilter('orderid',$order_id)
					->addFieldToFilter('prodid',$product_id)
					->addFieldToFilter('sellerid',$sellerid)
                    ->addFieldToFilter('proprice',$product_price);
				if(count($collection))
				{
					$saleItem = $collection->getFirstItem()->getActualparterprocost();
				}
			}
		}
		return $saleItem;
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
     * @return array
     */
    public function getBackUrl(){
        $url = $this->getUrl('marketplace/seller/vieworder', array('order_id'=>$this->getRequest()->getParam('order_id')));
        return $url;
    }
    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }
    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->_shippingConfig->getAllCarriers();
    }
    /**
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_getCarriersInstances();
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }
}