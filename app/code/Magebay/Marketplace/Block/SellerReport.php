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
class SellerReport extends \Magento\Catalog\Block\Product\ListProduct
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
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
	protected $_customerFactory;
	protected $_collectionFactory;
	protected $_startDate;
	protected $_endDate;
    protected $_productsFactory;
	
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Sales\Model\OrderFactory $mkCoreOrder,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Sales\Model\Order\Address $orderAddess,
		\Magento\Directory\Model\Country $country,
		\Magebay\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magebay\Marketplace\Model\TransactionsFactory $transactionsFactory,  
        \Magebay\Marketplace\Model\PartnerFactory $partnerFactory,    
        \Magebay\Marketplace\Model\ReviewsFactory $reviewsFactory,
		\Magento\Customer\Model\CustomerFactory $customerFactory,
		//\Magento\Reports\Model\ResourceModel\Product\Index\ViewedFactory $collectionFactory,
		//\Magento\Reports\Model\Product\Index\ViewedFactory $collectionFactory,
        \Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory,
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_summaryFactory = $summaryFactory;
		$this->_customerSession = $customerSession;
		$this->_mkCoreOrder = $mkCoreOrder;
		$this->_priceHelper = $priceHelper;
		$this->_orderAddess = $orderAddess;
		$this->_country = $country;
		$this->_saleslistFactory = $saleslistFactory;
        $this->_transactionsFactory = $transactionsFactory;
        $this->_partnerFactory = $partnerFactory;  
        $this->_reviewsFactory = $reviewsFactory;
		$this->_customerFactory = $customerFactory;
        $this->_productsFactory = $productsFactory;
		//$this->_collectionFactory = $collectionFactory;
        
        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
		$start_date = $this->getRequest()->getParam('start-date');
				
		if ( $this->getRequest()->getPost('start-date') && $this->getRequest()->getPost('start-date') != '' ) {
			$this->_startDate = $this->getFormatDate( $this->getRequest()->getPost('start-date') ) . ' 00:00:00';
		} else {
			$this->_startDate = date("Y-m-d 00:00:00", strtotime("7 days ago"));
		}
		if ( $this->getRequest()->getPost('end-date') && $this->getRequest()->getPost('end-date') != '' ) {
			$this->_endDate = $this->getFormatDate( $this->getRequest()->getPost('end-date') ) . ' 23:59:59';
		} else {
			$this->_endDate = date('Y-m-d 23:59:59');
		}
		
		if ( $this->getRequest()->getPost('select_filter') && $this->getRequest()->getPost('select_filter') != '' ) {
			switch ( $this->getRequest()->getPost('select_filter') ) {
				case 'today':
					$this->_startDate = date('Y-m-d 00:00:00');
					$this->_endDate = date('Y-m-d 23:59:59');
				break;
				case 'last 7 days':
					$this->_startDate = date("Y-m-d 00:00:00", strtotime("7 days ago"));
					$this->_endDate = date('Y-m-d 23:59:59');
				break;
				case 'current month':
					$this->_startDate = date("Y-m-d 00:00:00", strtotime("first day of this month"));
					$this->_endDate = date("Y-m-d 23:59:59", strtotime("last day of this month"));
				break;
				case 'last month':
					$this->_startDate = date("Y-m-d 00:00:00", strtotime("first day of previous month"));
					$this->_endDate = date("Y-m-d 23:59:59", strtotime("last day of previous month"));
				break;
				case 'last week':
					$this->_startDate = date("Y-m-d 00:00:00", strtotime("monday last week"));
					$this->_endDate = date("Y-m-d 23:59:59", strtotime("sunday last week"));
				break;
				case 'current week':
					$this->_startDate = date("Y-m-d 00:00:00", strtotime("monday this week"));
					$this->_endDate = date("Y-m-d 23:59:59", strtotime("sunday this week"));
				break;	
			}
		}
    }
    
    public function getTotalSales()
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
            
			$fromDate = $this->_startDate;
			$toDate = $this->_endDate;
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
        
    public function getTotalIncome()
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
            
			$fromDate = $this->_startDate;
			$toDate = $this->_endDate;
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
	
	public function getTotalProductSales( $prodid )
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
            
			$fromDate = $this->_startDate;
			$toDate = $this->_endDate;
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'proqty'=>"SUM(proqty)",'sellerid','prodid' )
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid); 
			$orders->getSelect()->where('mk_sales_list.prodid=?',$prodid);
			$orders->getSelect()->group('main_table.entity_id');
		}
		$proqty = 0;
        if(count($orders)){
            foreach($orders as $order){
                $proqty += $order->getProqty();
            }
        }
		return $proqty;
	}
	
	public function getTotalProductIncome( $prodid )
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
            
			$fromDate = $this->_startDate;
			$toDate = $this->_endDate;
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid','prodid')
            );
			/* $orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid); */
			$orders->getSelect()->where('mk_sales_list.prodid=?',$prodid);
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
    
    public function getWeekAmount()
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
    
	/* convert m_d_y to y m d time  */ 
	public function getFormatDate( $date )
    {
		$year = date('Y', strtotime($date));
		$month = date('m', strtotime($date));
		$day = date('d', strtotime($date));
		$format_date = $year."-".$month."-".$day;
		return $format_date;
	}
    
	public function getCurrentStartDate( )
    {
		return $this->_startDate;
	}
    
	public function getCurrentEndDate( )
    {
		return $this->_endDate;
	}
	
	public function getCustomsale( $select_filter , $date_from , $date_to )
    {
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$data = array();	
		$curryear = date('Y');
		$date = $this->getCurrentStartDate();
		$end = $this->getCurrentEndDate();
        
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
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid','prodid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
			
		    $sum = array();
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
	
	public function getProductCustomsale( $prodid ,$select_filter , $date_from , $date_to )
    {
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$data = array();	
		$curryear = date('Y');
		$date = $this->getCurrentStartDate();
		$end = $this->getCurrentEndDate();
		
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
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'proqty'=>"SUM(proqty)",'sellerid','prodid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->where('mk_sales_list.prodid=?',$prodid );
			$orders->getSelect()->group('main_table.entity_id');
			
		    $sum = array();
		    $temp = 0;
		    $earn = 0;
		    $proqty = 0;
			foreach ($orders as $order) {
				$temp = $temp+$order->getactualparterprocost();
				$earn += $order->getActualparterprocost();
				$proqty += $order->getProqty();
			}
			$i = $month."-".$day;
			$item['period'] = $i;
			$item['sales'] = $proqty;
			$item['earn'] = $this->getMkPriceHelper()->currency( $earn ,true,false);
			$data[] = $item;
		}
		return json_encode($data);
	}
        
    public function getPartnerAmount ()
    {
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
    
    public function getLatestReview()
    {
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
			$fromDate = $this->getCurrentStartDate();
			$toDate = $this->getCurrentEndDate();
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			} 
			if ( $code != 'all' ) {
                $orders->addFieldToFilter('status', $code );
			}
			//$orders->addAttributeToFilter('status', array('nin' => array('canceled','complete')));
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
		return count($orders);
	}
	
	/* Customize  */
	function getSellerProfile()
	{
		$sellerId =  $this->_modelSession->getId();
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
	
	protected function _getProductCollection()
	{
		$seller = $this->getSellerProfile();
		$collection = null;
		$litmit = $this->getRequest()->getParam('limit',5);
		$orderBy = $this->getRequest()->getParam('product_list_order','position');
		$sortOrder = $this->getRequest()->getParam('product_list_dir','DESC');
		$curPage = $this->getRequest()->getParam('p',1);
		if($seller && $seller->getId())
		{
			$customerSession = $this->_modelSession;
			$tableMKproduct = $this->_resource->getTableName('multivendor_product');
			$collection = $this->_product->getCollection();
			$collection->addAttributeToSelect(array('*'));
			if($customerSession->isLoggedIn()){
				$collection->addAttributeToFilter('visibility', array('in' => array(2,3,4)));
			}else{
				$collection->addAttributeToFilter('status',1);
			}
			$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array())
					   ->where('mk_product.user_id=?',$seller->getId())
                       ->where('mk_product.status = 1');                    
			$collection->addAttributeToSort($orderBy,$sortOrder);
			if($litmit > 0)
			{
				$collection->setPageSize($litmit);
			}	
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
		}
		$this->_productCollection = $collection;
		return parent::_getProductCollection();
	}
	
	public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }
    
    protected function _prepareLayout()
    {
        $collection = $this->getLoadedProductCollection();
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
     * @return  method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
	public function getProductSales( $prodid ) 
    {		
        $customerSession = $this->_modelSession;
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
            
			$fromDate = $this->_startDate;
			$toDate =  $this->_endDate;
            if($fromDate != '' && $toDate != '')
			{
				$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('proqty'=>"SUM(proqty)",'total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'prodid')
            );
			$orders->getSelect()->where('mk_sales_list.prodid=?',$prodid);
			//$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
		}
        $proqty = 0;
        if(count($orders)){
            foreach($orders as $order){
                $proqty += $order->getProqty();
            }
        }
		return $proqty;
	}
    
	public function getEarnAmount( $prodid ) 
	{
		$customerSession = $this->_modelSession;
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
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'prodid')
            );
			$orders->getSelect()->where('mk_sales_list.prodid=?',$prodid);
			//$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
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
    
	public function getViewCount( $prodid ) 
	{
        $count = 0;
		$fromDate = $this->_startDate;
		$toDate = $this->_endDate;
        $collection = $this->_productsFactory->create()->addViewsCount($fromDate,$toDate);
        foreach($collection as $product) {
            if($product->getData('entity_id') == $prodid)
            {
                $count = $product->getData('views');
            }
        }
		return $count;
	}
	
	public function getViewCountDate( $prodid , $fromDate , $toDate ) 
	{
        $count = 0;
		$fromDate = $fromDate;
		$toDate = $toDate;
        $collection = $this->_productsFactory->create()->addViewsCount($fromDate,$toDate);
        foreach($collection as $product) {
            if($product->getData('entity_id') == $prodid)
            {
                $count = $product->getData('views');
            }
        }
		return $count;
	}
	
	public function getProductReportView( $prodid ) 
	{
		$data = array();
		$date = $this->getCurrentStartDate();
		$end = $this->getCurrentEndDate();
		
		while( strtotime($date) <= strtotime($end) ) {
			$item = array();
			$year = date('Y', strtotime($date));
			$month = date('m', strtotime($date));
			$day = date('d', strtotime($date));
			$fromDate = $year."-".$month."-".$day." 00:00:00";
			$toDate = $year."-".$month."-".$day." 23:59:59";
			$date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
			$i = $month."-".$day;
			$item['period'] = $i;
			$item['sales'] = $this->getViewCountDate($prodid , $fromDate , $toDate );
			$data[] = $item;
		}
		return json_encode($data);
		
        $count = 0;
		$fromDate = $this->_startDate;
		$toDate = $this->_endDate;
        $collection = $this->_productsFactory->create()->addViewsCount($fromDate,$toDate);
        foreach($collection as $product) {
            if($product->getData('entity_id') == $prodid)
            {
                $count = $product->getData('views');
            }
        }
		return $count;
	}
	
	public function getFormatCurrency() 
	{
		return $this->_priceHelper;
	}
}