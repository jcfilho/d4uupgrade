<?php
 
namespace Magebay\Bookingsystem\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ProductAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkOrderHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Directory\Model\Currency;
use Magento\Catalog\Helper\Image as CatalogImages;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\Region;
use Magebay\Bookingsystem\Helper\BkProduct as BkProductHelper;
use Magento\Review\Model\Review as ProductReview;
class Search extends Template
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
    */
    protected $_coreRegistry;
	/**
     *
     * @var Magento\Catalog\Model\ResourceModel\Eav\Attribute
    */
	protected $_productAttribute;
	/**
     *
     * @var Magento\Framework\App\ResourceConnection
    */
	protected $_resource;
	/**
     * Facilities model factory
     *
     * @var \Magento\Catalog\Model\Product
    */
	protected $_productModel;
	/**
     * Facilities model factory
     *
     * @var Magento\Framework\Stdlib\DateTime\Timezone
    */
	protected $_timezone;
	/**
     *
     * @var \Magebay\Bookingsystem\Model\BookingsFactory
    */
    protected $_bookingsFactory;
	/**
     *
     * @var \Magebay\Bookingsystem\Model\IntervalhoursFactory
    */
    protected $_intervalhoursFactory;
	 /**
     * Facilities model factory
     *
     * @var \Magebay\Bookingsystem\Model\FacilitiesFactory
     */
    protected $_facilitiesFactory;
	
	/**
     * Bookingorders model factory
     *
     * @var \Magebay\Bookingsystem\Model\BookingordersFactory
    */
    protected $_bookingordersFactory;
	/**
     *
     * @var Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     *
     * @var Magebay\Bookingsystem\Helper\BkOrderHelper
    */
	protected $_bkOrderHelper;
	/**
     *
     * @var Magento\Framework\Pricing\Helper\Data 
    */
	protected $_priceHelper;
	 /**
     * Result page factory
     *
     * @var \Magento\Directory\Model\Currency;
     */
	protected $_currency;
	/**
     * Result page factory
     *
     * @var \Magento\Directory\Model\Currency;
    */
	/**
     * @param \Magento\Directory\Model\Region
     * 
     */
	protected $_region;
	/**
     * @param \Magento\Directory\Model\Country
     * 
     */
	protected $_country;
	/**
     * @param \Magebay\Bookingsystem\Helper\BkProduct
     * 
     */
	protected $_bkProductHelper;
	/**
     * @param \Magebay\Bookingsystem\Helper\BkProduct
     * 
     */
	protected $_productReview;
	protected $_catalogImages;
	protected $_productAttributeRepository;
	/**
    * @param Template\Context $context
    * @param array $data
    */
	public function __construct(
		Template\Context $context,
		Registry $coreRegistry,
		ProductAttribute $productAttribute,
		ResourceConnection $resource,
		Product $productModel,
		Timezone $timezone,
		BookingsFactory $bookingsFactory,
		IntervalhoursFactory $intervalhoursFactory,
		FacilitiesFactory $facilitiesFactory,
		BookingordersFactory $bookingordersFactory,
		BkHelperDate $bkHelperDate,
		BkOrderHelper $bkOrderHelper,
		PriceHelper $priceHelper,
		Currency $currency,
		CatalogImages $catalogImages,
		Country $country,
		Region $region,
		BkProductHelper $bkProductHelper,
		ProductReview $productReview,
		\Magento\Catalog\Model\Product\Attribute\Repository $productAttributeRepository,
      array $data = []
	) 
	{
		$this->_coreRegistry = $coreRegistry;
		$this->_productAttribute = $productAttribute;
		$this->_resource = $resource;
		$this->_productModel = $productModel;
		$this->_timezone = $timezone;
		$this->_bookingsFactory = $bookingsFactory;
		$this->_intervalhoursFactory = $intervalhoursFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
		$this->_bookingordersFactory = $bookingordersFactory;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_bkOrderHelper = $bkOrderHelper;
		$this->_priceHelper = $priceHelper;
		$this->_currency = $currency;
		$this->_catalogImages = $catalogImages;
		$this->_country = $country;
		$this->_region = $region;
		$this->_bkProductHelper = $bkProductHelper;
		$this->_productReview = $productReview;
		$this->_productAttributeRepository = $productAttributeRepository;
        parent::__construct($context, $data);
	
	}
	function getListBookings()
	{
		if($this->_coreRegistry->registry('bk_booking_search'))
		{
			$collection = $this->_coreRegistry->registry('bk_booking_search');
			return $collection;
		}
		$bkAllIds = array();
		$storeId = $this->_bkHelperDate->getbkCurrentStore();
		$bkStore = $this->_bkHelperDate->getBkStore($storeId);
		if($bkStore->isDefault())
        {
            $storeId = 0;
        }
		$params = $this->_request->getParams();
		$checkIn = '';
		$checkOut = '';
		$okSerch = true;
		$curPage = 1;
		$bkHelperDate = $this->_bkHelperDate;
		$limit = $bkHelperDate->getFieldSetting('bookingsystem/search_setting/number_items');
		$formatDate = $bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		$starts = isset($params['stars']) ? $params['stars'] : array();
		$okAddress = $bkHelperDate->getFieldSetting('bookingsystem/setting/booking_address');
		$okAddress = $okAddress == 1 ? true : false;
		$intToday = $this->_timezone->scopeTimeStamp();
		$today = date('Y-m-d',$intToday);
		$tablePrices = $this->_resource->getTableName('booking_calendars');
		$tableRooms = $this->_resource->getTableName('booking_rooms');
		$tableReview = $this->_resource->getTableName('review_entity_summary');
		$bkMainTable = $this->_resource->getTableName('booking_systems');
		$tableCoreOrder = $this->_resource->getTableName('sales_order');
		$isEnter = $this->checkEnterPriseVersion();
		if(isset($params['check_in']) && trim($params['check_in']) != '')
		{
			if($bkHelperDate->validateBkDate($params['check_in'],$formatDate))
			{
				$checkIn = $bkHelperDate->convertFormatDate($params['check_in']);
			}
			else
			{
				$okSerch = false;
			}
		}
		if(isset($params['check_out'] ) && trim($params['check_out']) != '')
		{
			if($bkHelperDate->validateBkDate($params['check_out'],$formatDate))
			{
				$checkOut = $bkHelperDate->convertFormatDate($params['check_out']);
			}
			else
			{
				$okSerch = false;
			}
		}
		$bookingCategyId = isset($params['booking_category']) ? $params['booking_category'] : 0;
		$isDistance = false;
		if(isset($params['bk_lat']) && (double)$params['bk_lat'] != 0 && isset($params['bk_lng']) && (double)$params['bk_lng'] != 0)
		{
			$isDistance  = true;
		}
		$isDistanceSetting = $this->getBkHelperDate()->getFieldSetting('bookingsystem/search_setting/use_map_distance');
		if($isDistanceSetting != 1 || !$isEnter)
		{
			$isDistance = false;
		}
		$addressBookingIds = array();
		if($okAddress)
		{
			$arrayAddress = array();
			if(isset($params['text_rent_auto_complete']) && trim($params['text_rent_auto_complete']) != '')
			{
				$arrayAddress = array(
					'address'=>$params['text_rent_auto_complete'],
					'city' => $params['city_bk'],
					'states' => $params['state_bk_bk'],
					'country' => $params['country_bk']
				);
			}
			if(count($arrayAddress) && !$isDistance)
			{
				
				$bkSystemModel = $this->_bookingsFactory->create();
				$addressBookingIds = $bkSystemModel->getBkAddressIds($arrayAddress);
				if(!count($addressBookingIds ))
				{
					$okSerch = false;
				}
			}
		}
		//get booking facilities
		$facilityBkIds = array();
		$facilityIds = isset($params['facilities']) ? $params['facilities'] : array();
		if(count($facilityIds))
		{
			foreach($facilityIds as $facilityId)
			{
				$facilitiesModel = $this->_facilitiesFactory->create();
				$facility = $facilitiesModel->getBkFacility($facilityId);
				if($facility)
				{
					if($facility->getFacilityBookingIds() != '') 
					{
						if(count($facilityBkIds))
						{
							$facilityBkIds = array_intersect($facilityBkIds,explode(',',$facility->getFacilityBookingIds()));
						}
						else
						{
							$facilityBkIds = explode(',',$facility->getFacilityBookingIds());
						}
					}
				}
			}
			if(!count($facilityBkIds))
			{
				$okSerch = false;
			}
		}
		if(isset($params['page']))
		{
			$curPage = $params['page'];
		}
		$sortBy = isset($params['rent_sort_by']) ? $params['rent_sort_by'] : 'price'; 
		$maxPrice = $bkHelperDate->getFieldSetting('bookingsystem/search_setting/max_price');
		$toPrice = isset($params['rent_to_price']) ? (int)$params['rent_to_price'] : $maxPrice;
		$fromPrice = isset($params['rent_from_price']) ? (int)$params['rent_from_price'] : 0;
		$sortOrder = isset($params['rent_sort_order']) ? $params['rent_sort_order'] : 'ASC'; 
		$collection = null;
		if($okSerch)
		{
			$arrayPerDayBooker = array();
			$arrayHotelBooker = array();
			if($checkIn != '' && $checkOut != '')
			{
				//get booking order type perday
				//$tablePrices = 'booking_calendar';
				
				$bkOrderModel = $this->_bookingordersFactory->create();
				$perDayOrders = $bkOrderModel->getCollection();
				$perDayOrders->addFieldToSelect(array('bkorder_booking_id'));
				$perDayOrders->getSelect()->columns('SUM(main_table.bkorder_qty) as total_qty');
				$subQuery1 = new \Zend_Db_Expr("(SELECT tb_ca.calendar_booking_id,tb_ca.calendar_qty FROM(SELECT calendar_booking_id,calendar_qty FROM {$tablePrices} WHERE calendar_booking_type = 'per_day' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1 OR (calendar_default_value = 2 AND calendar_startdate <= '{$checkIn}' AND calendar_enddate >= '{$checkOut}')) ORDER BY FIND_IN_SET(calendar_default_value,'2') DESC) tb_ca GROUP BY tb_ca.calendar_booking_id)");
				$perDayOrders->getSelect()->joinLeft(array('p1'=>$subQuery1),'main_table.bkorder_booking_id = p1.calendar_booking_id',array('p1.calendar_qty'));
				$perDayOrders->getSelect()->joinLeft(array('bk_core_order'=>$tableCoreOrder),'main_table.bkorder_order_id = bk_core_order.entity_id',array());
				$perDayOrders->getSelect()->where("bk_core_order.status = 'pending' OR bk_core_order.status = 'processing'");
				$perDayOrders->getSelect()->where("(main_table.bkorder_check_in >= '{$checkIn}' AND main_table.bkorder_check_in <= '{$checkOut}') OR (main_table.bkorder_check_out >= '{$checkIn}' AND main_table.bkorder_check_out < '{$checkOut}')");
				$perDayOrders->getSelect()->where('main_table.bkorder_room_id=?',0);
				$perDayOrders->getSelect()->group('main_table.bkorder_booking_id');
				$perDayOrders->getSelect()->having("IF(SUM(main_table.bkorder_qty) >= p1.calendar_qty,1,0) = 1");
				//echo (string)$perDayOrders->getSelect();
				//get bookingOrder for intervals type
				$intervalOrderBkIds = $this->getIntervalsBookedIds($checkIn);
				if(count($perDayOrders))
				{
					foreach($perDayOrders as $perDayOrder)
					{
						$arrayPerDayBooker[] = $perDayOrder->getBkorderBookingId();
					}
				}
				/*if(count($intervalOrderBkIds))
				{
					foreach($intervalOrderBkIds as $intervalOrderBkId)
					{
						$arrayPerDayBooker[] = $intervalOrderBkId;
					}
				}*/
				//get booking order type hotel
				$hotelOrders = $bkOrderModel->getCollection();
				$hotelOrders->addFieldToSelect(array('bkorder_booking_id'));
				$hotelOrders->getSelect()->columns('SUM(main_table.bkorder_qty) as total_qty');
				$subQuery1 = new \Zend_Db_Expr("(SELECT tb_ca.calendar_booking_id,tb_ca.calendar_qty FROM(SELECT calendar_booking_id,calendar_qty FROM {$tablePrices} WHERE calendar_booking_type = 'hotel' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1 OR (calendar_default_value = 2 AND calendar_startdate <= '{$checkIn}' AND calendar_enddate >= '{$checkOut}')) ORDER BY FIND_IN_SET(calendar_default_value,'2') DESC) tb_ca GROUP BY tb_ca.calendar_booking_id)");
				$hotelOrders->getSelect()->joinLeft(array('p1'=>$subQuery1),'main_table.bkorder_booking_id = p1.calendar_booking_id',array('p1.calendar_qty'));
				$hotelOrders->getSelect()->joinLeft(array('bk_core_order'=>$tableCoreOrder),'main_table.bkorder_order_id = bk_core_order.entity_id',array());
				$hotelOrders->getSelect()->where("bk_core_order.status = 'pending' OR bk_core_order.status = 'processing'");
				$hotelOrders->getSelect()->where("(main_table.bkorder_check_in <= '{$checkIn}' AND main_table.bkorder_check_out > '{$checkIn}') OR (main_table.bkorder_check_in <= '{$checkOut}' AND main_table.bkorder_check_out > '{$checkOut}')");
				$hotelOrders->getSelect()->where('bkorder_room_id=?',1);
				$hotelOrders->getSelect()->group('main_table.bkorder_booking_id');
				$hotelOrders->getSelect()->having("IF(SUM(main_table.bkorder_qty) >= p1.calendar_qty,1,0) = 1");
				if(count($hotelOrders))
				{
					foreach($hotelOrders as $hotelOrder)
					{
						$arrayHotelBooker[] = $hotelOrder->getBkorderBookingId();
					}
				}
			}
			$productModel = $this->_productModel;
			$collection = $productModel->getCollection()
					->addAttributeToSelect(array('*'));
			$collection->addAttributeToFilter('status',1);
			$collection->addAttributeToFilter('type_id','booking');
			if($bookingCategyId > 0)
			{
					$collection->joinField('category_id', $this->_resource->getTableName('catalog_category_product'), 'category_id', 'product_id = entity_id', null, 'left')
						->addAttributeToFilter('category_id', array(array('finset' => $bookingCategyId) ));
			}
			if(count($facilityBkIds))
			{
				$collection->addAttributeToFilter('entity_id',array('in'=>$facilityBkIds));
			}
			if(count($arrayPerDayBooker))
			{
				$collection->addAttributeToFilter('entity_id',array('nin'=>$arrayPerDayBooker));
			}
			if(count($addressBookingIds))
			{
				$collection->addAttributeToFilter('entity_id',array('in'=>$addressBookingIds));
			}
			$subQuery1 = new \Zend_Db_Expr("(SELECT tb_ca.calendar_booking_id,calendar_price,calendar_promo, IF(tb_ca.calendar_promo != '',tb_ca.calendar_promo,tb_ca.calendar_price ) AS bk_common_price FROM(SELECT calendar_booking_id,calendar_price,calendar_promo,IF(calendar_promo != '',calendar_promo, calendar_price ) AS bk_common_price FROM {$tablePrices} WHERE calendar_booking_type = 'per_day' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1 OR (calendar_default_value = 2 AND calendar_enddate >= '{$today}'))  GROUP BY calendar_id ORDER BY bk_common_price ASC) tb_ca GROUP BY tb_ca.calendar_booking_id)");
			$strSubQuery2 = "SELECT  tb_ca2.calendar_price, tb_ca2.calendar_promo,IF(tb_ca2.calendar_promo != '',tb_ca2.calendar_promo,tb_ca2.calendar_price) AS bk_common_price, tb_ca2.room_id,tb_ca2.room_booking_id FROM ";
			$strSubQuery2 .= "(SELECT calendar_price, calendar_promo, room.room_id, room.room_booking_id, IF(calendar_promo != '',calendar_promo,calendar_price) AS bk_common_price FROM {$tablePrices} ";
			$strSubQuery2 .= "LEFT JOIN {$tableRooms} AS room ON calendar_booking_id = room.room_id WHERE calendar_booking_type = 'hotel' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1  OR (calendar_default_value = 2 AND calendar_enddate >= '{$today}')) AND room.room_status = 1 ";
			$strSubQuery2 .= "GROUP BY calendar_id ORDER BY bk_common_price ASC) AS tb_ca2 GROUP BY tb_ca2.room_booking_id";
			$subQuery2 = new \Zend_Db_Expr("(".$strSubQuery2.")");
			$queryStars = '';
			$reviewZeroBookingIds = array();
			if(count($starts))
			{
				foreach($starts as $start)
				{
					if($start == 0)
					{
						unset($starts[0]);
						$queryStars = '';
						$reviewZeroBookingIds = $this->getReviewBookingIds($starts);
						break;
					}
					else
					{
						$tempStart = $start * 20;
						$tempStart2 = $tempStart + 20;
						if($queryStars == '')
						{
							$queryStars = "(review.rating_summary >= {$tempStart} AND review.rating_summary < {$tempStart2})";
						}
						else
						{
							$queryStars .= " OR (review.rating_summary  >= {$tempStart} AND review.rating_summary < {$tempStart2})";
						}
					}
				}
			}
			if($checkIn != '' && $checkOut != '')
			{
				$subQuery1 = new \Zend_Db_Expr("(SELECT tb_ca.calendar_booking_id,calendar_price,calendar_promo,IF(tb_ca.calendar_promo != '',tb_ca.calendar_promo,tb_ca.calendar_price ) AS bk_common_price FROM(SELECT calendar_booking_id,calendar_price,calendar_promo, IF(calendar_promo != '', calendar_promo, calendar_price ) AS bk_common_price FROM {$tablePrices} WHERE calendar_booking_type = 'per_day' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1 OR (calendar_default_value = 2 AND calendar_startdate <= '{$checkOut}' AND calendar_enddate >= '{$checkIn}')) GROUP BY calendar_id ORDER BY calendar_default_value DESC, calendar_price ASC) tb_ca GROUP BY tb_ca.calendar_booking_id)");
				$strSubQuery2 = "SELECT  tb_ca2.calendar_price, tb_ca2.calendar_promo,IF(tb_ca2.calendar_promo != '',tb_ca2.calendar_promo,tb_ca2.calendar_price) AS bk_common_price, tb_ca2.room_id,tb_ca2.room_booking_id FROM ";
				$strSubQuery2 .= "(SELECT calendar_price, calendar_promo, room.room_id, room.room_booking_id, IF(calendar_promo != '',calendar_promo,calendar_price) AS bk_common_price FROM {$tablePrices} ";
				$strSubQuery2 .= "LEFT JOIN {$tableRooms} AS room ON calendar_booking_id = room.room_id WHERE calendar_booking_type = 'hotel' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1  OR (calendar_default_value = 2 AND calendar_startdate <= '{$checkOut}'  AND calendar_enddate >= '{$checkIn}')) AND room.room_status = 1 ";
				$strSubQuery2 .= "GROUP BY calendar_id ORDER BY calendar_default_value DESC, bk_common_price ASC) AS tb_ca2 GROUP BY tb_ca2.room_booking_id";
				$subQuery2 = new \Zend_Db_Expr("(".$strSubQuery2.")");
				if($arrayHotelBooker)
				{
					$hotelBooked = implode(',',$arrayHotelBooker);
					$strSubQuery2 = "SELECT  tb_ca2.calendar_price, tb_ca2.calendar_promo,IF(tb_ca2.calendar_promo != '',tb_ca2.calendar_promo,tb_ca2.calendar_price) AS bk_common_price, tb_ca2.room_id,tb_ca2.room_booking_id FROM ";
					$strSubQuery2 .= "(SELECT calendar_price, calendar_promo, room.room_id, room.room_booking_id, IF(calendar_promo != '',calendar_promo,calendar_price) AS bk_common_price FROM {$tablePrices} ";
					$strSubQuery2 .= "LEFT JOIN {$tableRooms} AS room ON calendar_booking_id = room.room_id WHERE calendar_booking_type = 'hotel' AND (calendar_status = 'available' OR calendar_status = 'special') AND (calendar_default_value = 1  OR (calendar_default_value = 2 AND calendar_startdate <= '{$checkOut}'  AND calendar_enddate >= '{$checkIn}')) AND room.room_status = 1 AND room.room_id NOT IN ({$hotelBooked}) ";
					$strSubQuery2 .= "GROUP BY calendar_id ORDER BY calendar_default_value DESC, bk_common_price ASC) AS tb_ca2 GROUP BY tb_ca2.room_booking_id";
					$subQuery2 = new \Zend_Db_Expr("(".$strSubQuery2.")");
				}
			}
			//fiter attribute
			if(isset($params['booking_attibute']) && count($params['booking_attibute']))
			{
				$arAttributes = array();
				//$strAttribute = isset($config['search_setting']['booking_attibute']) ? $config['search_setting']['booking_attibute'] : '';
				$strAttribute = $this->getBkHelperDate()->getFieldSetting('bookingsystem/search_setting/booking_attibute');
				if($strAttribute != '')
				{
					$arAttributes = explode(',',$strAttribute);
				}
				foreach($params['booking_attibute'] as $keyAttr => $attribute)
				{
					if(in_array($keyAttr,$arAttributes))
					{
						$objAttirute = $this->getBkAttribute()->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $keyAttr);
						if($objAttirute->getFrontendInput() == 'multiselect')
						{
							if(count($attribute))
							{
								foreach($attribute as $attrValue)
								{
									$collection->addAttributeToFilter($keyAttr,array('finset'=>$attrValue));
								}
							}
							
						}
						else
						{
							$collection->addAttributeToFilter($keyAttr,array('in'=>$attribute));
						}
						
					}
					
				}
			}
			$collection->getSelect()->joinLeft(array('p1'=>$subQuery1),'e.entity_id = p1.calendar_booking_id',array());
			//$collection->getSelect()->joinLeft(array('room'=>$tableRooms),'e.entity_id = room.booking_id',array());
			$collection->getSelect()->joinLeft(array('p2'=>$subQuery2),'e.entity_id = p2.room_booking_id',array('booking_price'=>"if(p1.calendar_price != '', p1.calendar_price, p2.calendar_price)", 'booking_promo'=>"if(p1.calendar_promo != '', p1.calendar_promo, p2.calendar_promo)",'booking_room_id'=>"IF(p2.room_id != '',p2.room_id,0)",'booking_common_price'=>"IF(p1.bk_common_price != '', p1.bk_common_price, p2.bk_common_price)"));
			$collection->getSelect()->where("p1.calendar_price != '' or p2.calendar_price != ''");
			//$collection->getSelect()->where("(p1.calendar_price >= {$fromPrice} AND p1.calendar_price <= {$toPrice}) OR (p2.calendar_price >= {$fromPrice} AND p2.calendar_price <= {$toPrice})");
			$collection->getSelect()->where("(CASE WHEN p1.calendar_promo != '' THEN p1.calendar_promo > {$fromPrice} ELSE p1.calendar_price > {$fromPrice} END AND CASE WHEN p1.calendar_promo != '' THEN p1.calendar_promo <= {$toPrice} ELSE p1.calendar_price <= {$toPrice} END ) OR (CASE WHEN p2.calendar_promo != '' THEN p2.calendar_promo > {$fromPrice} ELSE p2.calendar_price > {$fromPrice} END AND CASE WHEN p2.calendar_promo != '' THEN p2.calendar_promo <= {$toPrice} ELSE p2.calendar_price <= {$toPrice} END)");
			
			if($okAddress)
			{
				if($isDistance)
				{
					$PI = 3.141593;
					$bookingLat = isset($params['bk_lat']) ? (float)$params['bk_lat'] : 0;
					$bookingLng = isset($params['bk_lng']) ? (float)$params['bk_lng'] : 0;
					$bkDistance = (isset($params['bk_distance']) && (float)$params['bk_distance'] > 0) ? (float)$params['bk_distance'] : 0;
					$strBookingTable = "(SELECT * FROM (SELECT * , (6371 * 2 *  (ATAN2(SQRT(SIN((($bookingLat-booking_lat) * ($PI / 180)) / 2) * SIN((($bookingLat-booking_lat) * ($PI / 180)) / 2) + COS(booking_lat * ($PI / 180)) * COS($bookingLat * ($PI/ 180)) * SIN((($bookingLng-booking_lon) * ($PI / 180))/2) * SIN((($bookingLng-booking_lon) * ($PI / 180))/2)),SQRT(1 - (SIN((($bookingLat-booking_lat) * ($PI / 180)) / 2) * SIN((($bookingLat-booking_lat) * ($PI / 180)) / 2) + COS(booking_lat * ($PI / 180)) * COS($bookingLat * ($PI/ 180)) * SIN((($bookingLng-booking_lon) * ($PI / 180))/2) * SIN((($bookingLng-booking_lon) * ($PI / 180))/2)))))) as distance FROM booking_systems) AS Bkk_address)";
					$bkMainTable = new \Zend_Db_Expr($strBookingTable);
					$collection->getSelect()->joinLeft(array('bk_address'=>$bkMainTable),'e.entity_id = bk_address.booking_product_id',array('booking_address','booking_city','booking_state','booking_country','booking_state_id','booking_lat','booking_lon','distance'));
					if($bkDistance > 0)
					{
						$collection->getSelect()->where("bk_address.distance <= '{$bkDistance}'");
					}
				}
				else
				{
					$collection->getSelect()->joinLeft(array('bk_address'=>$bkMainTable),'e.entity_id = bk_address.booking_product_id',array('booking_address','booking_city','booking_state','booking_country','booking_state_id','booking_lat','booking_lon'));
				}
			    $collection->getSelect()->where('bk_address.store_id=?',$storeId);
			}
			 if($queryStars != '')
			{
				$collection->getSelect()->where($queryStars);
			}
			elseif(count($reviewZeroBookingIds))
			{
				// $reviewZeroBookingIds = implode(',',$reviewZeroBookingIds);
				$collection->addAttributeToFilter('entity_id',array('in'=>$reviewZeroBookingIds));
			}
			// $collection->getSelect()->group('e.entity_id');
			$collection->getSelect()->joinLeft(array('review'=>$tableReview),'e.entity_id = review.entity_pk_value',array('booking_rate'=>"if(LENGTH(review.store_id), review.rating_summary,0)",'booking_review'=>"if(LENGTH(review.store_id),review.reviews_count,0)"));
			$collection->getSelect()->where("CASE WHEN LENGTH(review.store_id) THEN review.store_id = '{$storeId}' ELSE review.store_id IS NULL END");
			if($sortBy == 'stars')
			{
				$collection->getSelect()->order("booking_rate {$sortOrder}");
			}
			else
			{
				$collection->getSelect()->order("booking_common_price {$sortOrder}");
			}
			$collection->setPageSize($limit);
			$collection->setCurPage($curPage);
			//echo (string)$collection->getSelect();
			$this->_coreRegistry->register('bk_booking_search',$collection);
		}
		return $collection;
	}
	/* check intervals booking with orders */
	function getIntervalsBookedIds($strDay)
	{
		$bookingIds = $this->getAllIntervalsBooking();
		if(count($bookingIds))
		{
			foreach($bookingIds as $key => $bookingId)
			{
				$intervalModel = $this->_intervalhoursFactory->create();
				$intervals = $intervalModel->getIntervals($bookingId,$strDay);
				$okDay = false;
				if(count($intervals))
				{
					$intBkTmpTimne =$this->_timezone->scopeTimeStamp();
					$currtime = date('H:i:s',$intBkTmpTimne);
					$dateCurrent = date('Y-m-d',$intBkTmpTimne);
					$intCurrtime = strtotime($currtime);
					foreach($intervals as $interval)
					{
						$interQty = $interval['intervalhours_quantity'];
						$intervalsHours = $interval['intervalhours_booking_time'];
						$arIntervals = explode('_',$interval['intervalhours_booking_time']);
						$tempIntHoursStart = strtotime("{$arIntervals[0]}:{$arIntervals[1]}:00");
						if($strDay == $dateCurrent && $tempIntHoursStart < $intCurrtime)
						{
							continue;
						}
						//get quantity from order
						$interOrdertotal = $this->_bkOrderHelper->getOrderIntervalsTotal($bookingId,$strDay,$intervalsHours);
						//get total qty in $cart
						$interQty = $interQty - $interOrdertotal;
						if($interQty > 0)
						{
							$okDay = true;
							break;
						}
					}
				}
				if($okDay)
				{
					unset($bookingIds[$key]);
				}
			}
		}
		return $bookingIds;
	}
	/* 
	* get all intervals Booking product
	*/
	function getAllIntervalsBooking()
	{
        $storeId = $this->_bkHelperDate->getbkCurrentStore();
        $bkStore = $this->_bkHelperDate->getBkStore($storeId);
        if($bkStore->isDefault())
        {
            $storeId = 0;
        }
		$collection = $this->_productModel->getCollection();
		$tableBooking = $this->_resource->getTableName('booking_systems');
		$collection->getSelect()->joinLeft(array('bk_system'=>$tableBooking),'e.entity_id = bk_system.booking_product_id',array());
		$collection->getSelect()->where('bk_system.store_id=?',$storeId);
		$collection->addAttributeToFilter('type_id','booking');
		$collection->addAttributeToFilter('status',1);
		$collection->getSelect()->where('bk_system.booking_time=?',3);
		$bookingIds = array();
		if(count($collection))
		{
			$bookingIds = $collection->getAllIds();
		}
		return $bookingIds;
	}
	function getBkproductAttributeRepository()
	{
		return $this->_productAttributeRepository;
	}
	function getReviewBookingIds($stars)
	{
		$tableReview = $this->_resource->getTableName('review_entity_summary');
		$collection = $this->_productModel->getCollection()
				->addAttributeToSelect(array('entity_id'));
		$collection->getSelect()->where("e.entity_id NOT IN (SELECT entity_pk_value FROM {$tableReview})");
		$bookingIds = $collection->getAllIds();
		if(count($stars))
		{
			$strWhere = '';
			foreach($stars as $star)
			{
				$startStar = $star * 20;
				$endStar = $startStar + 20;
				if($strWhere != '')
				{
					$strWhere .= " OR review.rating_summary >= {$startStar} AND review.rating_summary < {$endStar}";
				}
				else
				{
					$strWhere .= "review.rating_summary >= {$startStar} AND review.rating_summary < {$endStar}";
				}
			}
			if($strWhere != '')
			{
				$collection2 = $this->_productModel->getCollection()
						->addAttributeToSelect('entity_id');
				$collection2->getSelect()->joinLeft(array('review'=>$tableReview),'e.entity_id = review.entity_pk_value',array());
				$collection2->getSelect()->where($strWhere);
				$collection2->getSelect()->group('e.entity_id');
				$bokingIds2 = $collection2->getAllIds();
				if(count($bokingIds2))
				{
					$bookingIds = array_merge($bookingIds,$bokingIds2);
				}
			}
		}
		return $bookingIds;
	} 
	function getAttrBookingIds($attributeCode,$type,$value)
	{
		$collection = $this->_productModel->getCollection();
		if($type == 'multiselect')
		{
			$collection->addAttributeToFilter($attributeCode,array('finset'=>$value));
		}
		else
		{
			$collection->addAttributeToFilter($attributeCode,array('in'=>$value));
		}
		$productIds = array();
		if($collection)
		{
			$productIds = $collection->getAllIds();
		}
		return $productIds;
	}
	function getBkFacilities()
	{
		$model = $this->_facilitiesFactory->create();
		$arraySelect = array('facility_id','facility_title','facility_title_transalte','facility_booking_ids');
		$conditions = array('facility_status'=>1);
		$collection = $model->getBkFacilities($arraySelect,$conditions);
		$collection->getSelect()->where("facility_booking_type != 'room'");
		return $collection;
	}
	function getRateProductIds($bookingIds)
	{
		$storeId = $this->_bkHelperDate->getbkCurrentStore();
		$reviewSummary = $this->_resource->getTableName('review_entity_summary');
		$collection = $this->_productReview->getCollection();
		$collection->addFieldToSelect(array('entity_pk_value'));
		$collection->addFieldToFilter('status_id',1);
		$collection->getSelect()->joinLeft(array('summary'=>$reviewSummary),'main_table.entity_pk_value = summary.entity_pk_value',array('rating_summary','reviews_count'));
		$collection->getSelect()->joinLeft(array('bk_product'=>$this->_resource->getTableName('catalog_product_entity')),'main_table.entity_pk_value = bk_product.entity_id',array());
		$collection->getSelect()->where("summary.store_id=?",$storeId);
		$collection->getSelect()->where("summary.entity_type=?",1);
		$collection->getSelect()->where("bk_product.type_id=?",'booking');
		$collection->getSelect()->group('main_table.entity_pk_value');
		$rates[1] = array();
		$rates[2] = array();
		$rates[3] = array();
		$rates[4] = array();
		$rates[5] = array();
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				
				$rateItem = $collect->getRatingSummary() / 20;
				if($rateItem >= 1 && $rateItem < 2)
				{
					$rates[1][] = $collect->getEntityPkValue();
					$bookingIds = array_diff($bookingIds,$rates[1]);
				}
				elseif($rateItem >= 2 && $rateItem < 3)
				{
					$rates[2][] = $collect->getEntityPkValue();
					$bookingIds = array_diff($bookingIds,$rates[2]);
				}
				elseif($rateItem >= 3 && $rateItem < 4)
				{
					$rates[3][] = $collect->getEntityPkValue();
					$bookingIds = array_diff($bookingIds,$rates[3]);
				}
				elseif($rateItem >= 4 && $rateItem < 5)
				{
					$rates[4][] = $collect->getEntityPkValue();
					$bookingIds = array_diff($bookingIds,$rates[4]);
				}
				elseif($rateItem  >= 5)
				{
					$rates[5][] = $collect->getEntityPkValue();
					$bookingIds = array_diff($bookingIds,$rates[5]);
				}
			}
		}
		$rates[0] = $bookingIds;
		return $rates;
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
	function getBkRegionName($idRegion)
	{
		$region = $this->_region->load($idRegion);
		$name = '';
		if($region->getId())
		{
			$name = $region->getName();
		}
		return $name;
	}
	function getBkAttribute()
	{
		return $this->_productAttribute;
	}
	function getBkProductHelper()
	{
		return $this->_bkProductHelper;
	}
	function getBkAjaxUrl()
	{
		return $this->getBkHelperDate()->formatUrlPro($this->getUrl('bookingsystem/index/search'));
	}
	function getBkHelperDate()
	{
		return $this->_bkHelperDate;
	}
	function getBkPriceHelper()
	{
		return $this->_priceHelper;
	}
	function getBkCatalogHelper()
	{
		return $this->_catalogImages;
	}
	function getBkCurrencySymbol()
	{
		return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
		// return $this->_currency->getCurrencySymbol();
	}
	function getBkRequest()
	{
		return $this->_request;
	}
	function getBkRootCategoryId()
	{
		return $this->_storeManager->getStore()->getRootCategoryId();
	}
	/*
	 * get current time in config magento
	 * @return $strDate
	 * */
	function getBkCurrentDate()
    {
        $formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
        $intToday = $this->_timezone->scopeTimeStamp();
        $strDay = date($formatDate,$intToday);
        return $strDay;
    }
	function checkEnterPriseVersion()
	{
		$mapFile = BP.'/app/design/frontend/Magebay/bookingtheme/Magebay_Bookingsystem/templates/search/map.phtml';
		$ok = false;
		if(file_exists($mapFile))
		{
			$ok = true;
		}
		return $ok;
	}
}