<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Block;
class SellerDashboard extends \Magento\Framework\View\Element\Template
{
	protected $_customerSession;
	protected $_resource;
	protected $_mkCoreOrder;
	protected $_priceHelper;
	protected $_orderAddess;
	protected $_country;
    protected $_saleslistFactory;
    protected $_transactionsFactory;    
    protected $_partnerFactory;   
    protected $_reviewsFactory;
    protected $_productsFactory;
	
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Sales\Model\OrderFactory $mkCoreOrder,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Sales\Model\Order\Address $orderAddess,
		\Magento\Directory\Model\Country $country,
		\Magebay\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magebay\Marketplace\Model\TransactionsFactory $transactionsFactory,  
        \Magebay\Marketplace\Model\PartnerFactory $partnerFactory,    
        \Magebay\Marketplace\Model\ReviewsFactory $reviewsFactory,
		\Magento\Catalog\Model\ProductFactory $productsFactory,
        array $data = []
    ) {
		$this->_customerSession = $customerSession;
		$this->_resource = $resource;
		$this->_mkCoreOrder = $mkCoreOrder;
		$this->_priceHelper = $priceHelper;
		$this->_orderAddess = $orderAddess;
		$this->_country = $country;
		$this->_saleslistFactory = $saleslistFactory;
        $this->_transactionsFactory = $transactionsFactory;
        $this->_partnerFactory = $partnerFactory;  
        $this->_reviewsFactory = $reviewsFactory;
        $this->_productsFactory = $productsFactory;
        parent::__construct($context, $data);
    }
    
    public function getTodaySales(){
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
            
            //Code get time in today -> tomorrow
			$fromDate = date('Y-m-d');
			$toDate = date("Y-m-d", strtotime('tomorrow'));
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
		return count($orders);
    }
	
	public function getTotalOrders(){
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
            
            //Code get time in today -> tomorrow
			$fromDate = date('Y-m-d');
			$toDate = date("Y-m-d", strtotime('tomorrow'));
            
            /* if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			} */
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
		return count($orders);
	}
	
    public function getTodayAmount(){
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
            
            //Code get time in today -> tomorrow
			$fromDate = date('Y-m-d');
			$toDate = date("Y-m-d", strtotime('tomorrow'));
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
        $amount = 0;
        if(count($orders)){
            foreach($orders as $order){
                $amount += $order->getActualparterprocost();
            }
        }
		return $amount;
    }
    
    public function getWeekAmount(){
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
            
            //Code get time in week
            $d = time();
            $dayIndex = date('w', $d);
            if ($dayIndex >= 2) { 
                $dx = strtotime(date('Y-m-d 00:00:00', $d-($dayIndex-2)*24*3600));
                $dy = strtotime(date('Y-m-d 23:59:59', $dx+6*24*3600));
            } else {
                $dy = strtotime(date('Y-m-d 00:00:00', $d+(1-$dayIndex )*24*3600)); 
                $dx = strtotime(date('Y-m-d 23:59:59', $dy-6*24*3600));
            }
            
			$fromDate = date('Y-m-d', $dx);
			$toDate =  date('Y-m-d', $dy);
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
        $amount = 0;
        if(count($orders)){
            foreach($orders as $order){
                $amount += $order->getActualparterprocost();
            }
        }
		return $amount;
    }
    
    public function getMonthAmount(){
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
            
            //Code get time in month
			$fromDate = date("Y-m-d", strtotime("first day of this month"));
			$toDate =  date("Y-m-d", strtotime("last day of this month"));
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
        $amount = 0;
        if(count($orders)){
            foreach($orders as $order){
                $amount += $order->getActualparterprocost();
            }
        }
		return $amount;
    }
	public function getYearAmount(){
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
            
            //Code get time in month
			$fromDate = date("Y-m-d", strtotime("first day of january"));
			$toDate =  date("Y-m-d", strtotime("last day of december"));
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
        $amount = 0;
        if(count($orders)){
            foreach($orders as $order){
                $amount += $order->getActualparterprocost();
            }
        }
		return $amount;
	}
	
	public function getTotalAmount(){
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
            
            //Code get time in month
			$fromDate = date("Y-m-d", strtotime("first day of january"));
			$toDate =  date("Y-m-d", strtotime("last day of december"));
            
            /* if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			} */
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
        $amount = 0;
        if(count($orders)){
            foreach($orders as $order){
                $amount += $order->getActualparterprocost();
            }
        }
		return $amount;
	}
	
	public function getMonthlysale() {
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$data = array();	
		$curryear = date('Y');
		
		for($i=1;$i<=12;$i++){
			
			$mkSalelistModel = $this->_saleslistFactory->create();
			$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
			$coreOrderModel = $this->_mkCoreOrder->create();
			$orders = $coreOrderModel->getCollection();
			
			$fromDate = $curryear."-".$i."-01 00:00:00";
			$toDate = $curryear."-".$i."-31 23:59:59";
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
			
		    $sum = array();
		    $temp = 0;
			foreach ($orders as $record) {
				$temp = $temp+$record->getactualparterprocost();
			}
			$data[$i] = $temp;
		}
		return json_encode($data);
	}
	
	public function getCustomsale( $select_filter , $date_from , $date_to ) {
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$data = array();	
		$curryear = date('Y');
		/*$date = '05/30/2016';
		$end = '06/06/2016'; */
		$date = $date_from;
		$end = $date_to;
		
		
		/* for($i=1;$i<=30;$i++){ */
		while( strtotime($date) <= strtotime($end) ) {
			$item = array();
			$year = date('Y', strtotime($date));
			$month = date('m', strtotime($date));
			$day = date('d', strtotime($date));
			
			$fromDate = $year."-".$month."-".$day." 00:00:00";
			$toDate = $year."-".$month."-".$day." 23:59:59";
			
			$date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
					
			$mkSalelistModel = $this->_saleslistFactory->create();
			$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
			$coreOrderModel = $this->_mkCoreOrder->create();
			$orders = $coreOrderModel->getCollection();
			
			/* $fromDate = $curryear."-04-".$i." 00:00:00";
			$toDate = $curryear."-04-".$i." 23:59:59"; */
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
			
		    $sum = array();
		    /* old $temp = 0;
			foreach ($orders as $record) {
				$temp = $temp+$record->getactualparterprocost();
			}
			$i = $month."-".$day;
			$data[$i] = $temp; */
			$temp = 0;
		    $earn = 0;
			foreach ($orders as $record) {
				$temp = $temp+$record->getactualparterprocost();
				$earn += $record->getActualparterprocost();
			}
			$i = $month."-".$day;
			$item['period'] = $i;
			$item['sales'] = count($orders);
			$item['earn'] = $this->getMkPriceHelper()->currency( $earn ,true,false);
			$data[] = $item;
		}
		return json_encode($data);
	}
        
    public function getPartnerAmount (){
        $customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
        $collection = $this->_partnerFactory->create()->getCollection()->addFieldToFilter('sellerid',$sellerid)->getFirstItem();
        return $collection;
    }
    
	/* 
	* get List Sellers orders
	* @param int $cutomerId
	* return $items
	*/
	function getLastOrder()
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
					$orders->addFieldToFilter('increment_id',array('like'=>'%'.$orderId.'%'));	
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
			$orders->addAttributeToSort('created_at','DESC');
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
			$limit = $this->getRequest()->getParam('limit',5);
			if($limit > 0)
			{
				$orders->setPageSize($limit);
			}
			
		}
		return $orders;
	}
    public function getLatestReview(){
        $customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
        $reviewModel = $this->_reviewsFactory->create();
		$customerTable = $this->_resource->getTableName('customer_entity');
		$collection = $reviewModel->getCollection();
		$collection->addFieldToFilter('status',1);
		$collection->addFieldToFilter('userid',$sellerid);
		$collection->getSelect()->joinLeft(array('table_custmer'=>$customerTable),'main_table.user_review_id = table_custmer.entity_id',array('firstname','lastname'));
		$limit = $this->getRequest()->getParam('limit',5);
		if((int)$limit > 0)
		{
			$collection->setPageSize($limit);
		}
		return $collection;
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
	* get data from saleslist model
	*/
	function getSalelist($orderId,$porductId)
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
				if(count($collection))
				{
					$saleItem = $collection->getFirstItem();
				}
			}
		}
		return $saleItem;
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
	
	function getOrderStatus($code)
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
            
            //Code get time in today -> tomorrow
			/* $fromDate = date('Y-m-d');
			$toDate = date("Y-m-d", strtotime('tomorrow'));
            
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			} */
			if ( $code != 'all' ) {
			$orders->addFieldToFilter('status', $code );
			}
			
			// $orders->addAttributeToFilter('status', array('nin' => array('canceled','complete')));
            
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
		return count($orders);
	}
	
	public function getBestSellerProducts($categoryID = null)
    {
        $model = $this->_productsFactory->create();
        $storeId = $model->getStoreId();

        $collection = $model->getCollection();
		/* $collection->setVisibility($this->_productVisible->getVisibleInCatalogIds()); */
        $collection->addAttributeToSelect('*');
		/*->addStoreFilter()
		//->addPriceData()
		//->addTaxPercents()
		//->addUrlRewrite()
		->setPageSize( 10 ); */
		$litmit = 5;
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();			
		$tableMKproduct = $this->_resource->getTableName('multivendor_product');
		$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array('mkproductstatus'=>"mk_product.status"))->where('mk_product.user_id=?',$sellerid );
		$collection->setPageSize($litmit);
		   
		$saleyearly = $this->_resource->getTableName('sales_bestsellers_aggregated_yearly');
		$collection->getSelect()->joinLeft(array('saleyearly'=>$saleyearly),'e.entity_id = saleyearly.product_id' , array('saleyearly.qty_ordered AS qty_ordered') )->where('saleyearly.store_id=?',$storeId )->order('qty_ordered DESC');
		return $collection->load();

    }
	
	public function getTotalProducts($categoryID = null)
    {
        $model = $this->_productsFactory->create();
        $storeId = $model->getStoreId();

        $collection = $model->getCollection();
		/* $collection->setVisibility($this->_productVisible->getVisibleInCatalogIds()); */
        $collection->addAttributeToSelect('*');
		/*->addStoreFilter()
		//->addPriceData()
		//->addTaxPercents()
		//->addUrlRewrite()
		->setPageSize( 10 ); */
		$litmit = 5;
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();			
		$tableMKproduct = $this->_resource->getTableName('multivendor_product');
		$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array('mkproductstatus'=>"mk_product.status"))->where('mk_product.user_id=?',$sellerid );
		//$collection->setPageSize($litmit);
		   
		//$saleyearly = $this->_resource->getTableName('sales_bestsellers_aggregated_yearly');
		//$collection->getSelect()->joinLeft(array('saleyearly'=>$saleyearly),'e.entity_id = saleyearly.product_id' , array('saleyearly.qty_ordered AS qty_ordered') )->where('saleyearly.store_id=?',$storeId )->order('qty_ordered DESC');
		
		return $collection->load();

    }

	
}