<?php

namespace Magebay\Bookingsystem\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Directory\Model\Currency;
use Magento\Review\Model\Review\SummaryFactory;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\RentPrice;
use Magebay\Bookingsystem\Helper\BkOrderHelper;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\Image as ImageModel;

class Booking extends Template
{
	/**
     * Core registry
     *
     * @var \Magento\Framework\Registry
    */
    protected $_coreRegistry;
	/**
     *
     * @var \Magento\Framework\Pricing\Helper\Data
    */
	protected $_priceHelper;
	/**
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
    */
	protected $_timezone;
	/**
     *
     * @var \Magento\Directory\Model\Currency
    */
	protected $_currency;
	/**
     *
     * @var \Magento\Review\Model\Review\SummaryFactory
    */
	protected $_summaryFactory;
	/**
     * Booking Model
     *
     * @var \Magebay\Bookingsystem\Model\Bookings
    */
	protected $_bookingFactory;
	/**
     * optionsFactory Model
     *
     * @var \Magebay\Bookingsystem\Model\OptionsFactory
    */
	protected $_optionsFactory;
	/**
     * optionsFactory Model
     *
     * @var \Magebay\Bookingsystem\Model\DiscountsFactory
    */
	protected $_optionsdropdownFactory;
	/**
     * OptionsdropdownFactory Model
     *
     * @var \Magebay\Bookingsystem\Model\FacilitiesFactory
    */
	protected $_discountsFactory;
	/**
     * OptionsdropdownFactory Model
     *
     * @var \Magebay\Bookingsystem\Model\OptionsFactory
    */
	protected $_facilitiesFactory;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\RentPrice
    */
	protected $_rentPrice;
	/**
     * Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Helper\BkOrderHelper
    **/
	protected $_bkOrderHelper;  
	/** Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Helper\BkSimplePriceHelper
    **/
	protected $_bkSimplePriceHelper;
	/**
     *
     * @var  Magebay\Bookingsystem\Model\Image
    **/
	protected $_imageModel;
	/** Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Helper\BkText
    **/
	protected $_bkText;

	protected $_calendars;
	
	public function __construct(
		Template\Context $context,
		Registry $coreRegistry,
		PriceHelper $priceHelper,
		Timezone $timezone,
		Currency $currency,
		SummaryFactory $summaryFactory,
		BookingsFactory $bookingFactory,
		OptionsFactory $optionsFactory,
		OptionsdropdownFactory $optionsdropdownFactory,
		DiscountsFactory $discountsFactory,
		FacilitiesFactory $facilitiesFactory,
		BkHelperDate $bkHelperDate,
		RentPrice $rentPrice,
		BkOrderHelper $bkOrderHelper,
		BkSimplePriceHelper $bkSimplePriceHelper,
		BkText $bkText,
		ImageModel $imageModel,
        \Magebay\Bookingsystem\Model\Calendars $calendars,
		array $data = []
	) 
	{
		$this->_coreRegistry = $coreRegistry;
		$this->_priceHelper = $priceHelper;
		$this->_timezone = $timezone;
		$this->_currency = $currency;
		$this->_summaryFactory = $summaryFactory;
		$this->_bookingFactory = $bookingFactory;
		$this->_optionsFactory = $optionsFactory;
		$this->_optionsdropdownFactory = $optionsdropdownFactory;
		$this->_discountsFactory = $discountsFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_rentPrice = $rentPrice;
		$this->_bkOrderHelper = $bkOrderHelper;
		$this->_bkSimplePriceHelper = $bkSimplePriceHelper;
		$this->_bkText = $bkText;
		$this->_imageModel = $imageModel;
        $this->_calendars = $calendars;
        parent::__construct($context, $data);
	}
	function getBkProduct()
	{
	    if($this->_coreRegistry->registry('bk_booking_data'))
        {
            $booking = $this->_coreRegistry->registry('bk_booking_data');
        }
        else
        {
            $product = $this->_coreRegistry->registry('product');
            $bookingModel = $this->_bookingFactory->create();
            $booking = $bookingModel->getBooking($product->getId());
            $this->_coreRegistry->register('bk_booking_data',$booking);
        }
		return $booking;
	}
	/* get results when custom check booking 
	* @params string $checkIn, $checkOut, $typeBooking, $formDatem,$formType,$toDate,$toType int $number, $qty,  \
	* @return array $result include int $totalPrice, $totalPrmo,$totalDays,$totalHours, string $messageError
	*/
	function getBookingResult()
	{
		//get new object search
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$checkIn = '';
		$checkOut = '';
		$params = $this->getRequest()->getParams();
		$bookingId = isset($params['booking_id']) ? $params['booking_id'] : 0;
		$bookingFactory = $this->_bookingFactory->create();
		$booking = $bookingFactory->getBooking($bookingId);
		$itemId = 0;
		if(count($params))
		{
			if(isset($params['itemId']))
			{
				$itemId = (int)$params['itemId'];
			}
			if(isset($params['check_in']) && trim($params['check_in']) != '')
			{
				if($this->getBkHelperDate()->validateBkDate($params['check_in'],$formatDate))
				{
					$checkIn = $this->getBkHelperDate()->convertFormatDate($params['check_in']);
				}
			}
			if(isset($params['check_out']) && trim($params['check_out']) != '')
			{
				if($this->getBkHelperDate()->validateBkDate($params['check_out'],$formatDate))
				{
					$checkOut = $this->getBkHelperDate()->convertFormatDate($params['check_out']);
				}
			}
		}
		$arPrices = array();
		if($checkIn == '' || $checkOut == '' || ($checkIn != '' && $checkOut != '' && strtotime($checkOut) < strtotime($checkIn)))
		{
			$arPrices['str_error'] = $this->__('Check in or check out are not available, Please check again');
		}
		elseif($booking && $booking->getId())
		{
			$qty = (int)$params['qty'] > 1 ? $params['qty'] : 1;
			$paramAddons = isset($params['addons']) ? $params['addons'] : array();
			if($booking->getBookingTime() == 1 || $booking->getBookingTime() == 4)
			{
				$arPrices = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$qty,$itemId,$paramAddons);
			}
			elseif($booking->getBookingTime() == 5)
            {
                $arPeople = $this->getRequest()->getParam('number_persons',array());
                $fistLoad = isset($params['temp_check_load']) ? $params['temp_check_load'] : 1;
                if($itemId > 0 && $fistLoad == 0)
                {
                    $tourRequest = $this->_bkOrderHelper->getBkRequestItemOption($itemId,$booking->getId());
                    $arPeople = isset($tourRequest['number_persons']) ? $tourRequest['number_persons'] : array();
                }
                $arPrices = $this->_bkSimplePriceHelper->getBkTourPrice($booking,$checkIn,$checkOut,$qty,$itemId,$paramAddons,$arPeople);
            }
			else
			{
				//get time
				/*$fromHour = ($params['from_time_t'] == 2 && $params['from_time_h'] != 12) ? ($params['from_time_h']  + 12) : $params['from_time_h'];
				$toHour = ($params['to_time_t'] == 2 && $params['to_time_h'] != 12) ? ($params['to_time_h'] + 12) : $params['to_time_h'];
				$arPrices = $this->_bkSimplePriceHelper->getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$params['from_time_m'],$params['to_time_m'],$qty,$itemId,$paramAddons);*/
                $startTime = $this->getRequest()->getParam('service_start','');
                $endTime = $this->getRequest()->getParam('service_end','');
                $arPrices = $this->_bkSimplePriceHelper->getPricePerTime($booking,$checkIn,$checkOut,$startTime,$endTime,$qty,$itemId,$paramAddons);
			}
			$useDefaultPrice = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/default_price');
			if($useDefaultPrice == 1)
			{
				if($booking->getSpecialPrice() > 0)
				{
					if($arPrices['total_promo'] > 0)
					{
						$arPrices['total_price'] += $booking->getPrice();
						$arPrices['total_promo'] += $booking->getSpecialPrice();
					}
					else
					{
						$arPrices['total_price'] += $booking->getPrice();
						$arPrices['total_promo'] = $arPrices['total_price'] + $booking->getSpecialPrice();
					}
				}
				else
				{
					if($arPrices['total_promo'] > 0)
					{
						$arPrices['total_price'] += $booking->getPrice();
						$arPrices['total_promo'] += $booking->getPrice();
					}
				}
			}
			$arPrices['total_price'] *= $qty;
			$arPrices['total_promo'] *= $qty;
			$arPrices['booking_id'] = $booking->getId();
			$arPrices['booking_time'] = $booking->getBookingTime();
			$arPrices['check_in'] = isset($arPrices['check_in']) ? $arPrices['check_in'] : $checkIn;
			$arPrices['check_out'] = isset($arPrices['check_out']) ? $arPrices['check_out'] : $checkOut;
		}
		return $arPrices;
	}
	/*
	* get price when product load
	*/
	function getBkCurrentPrice()
	{
		$itemId = 0;
		$action = $this->getRequest()->getActionName();
		if($action == 'configure')
		{
			$itemId = $this->getRequest()->getParam('id',0);
		}
		$timeCurrent = $this->getBkTmpTime();
		$checkIn = date('Y-m-d',$timeCurrent);
		$booking = $this->getBkBookingItem();
		$arPrices = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkIn,1,$itemId);
		$useDefaultPrice = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/default_price');
		$price = 0;
		if($arPrices['str_error'] == '')
		{
			$price = $arPrices['total_promo'] > 0 ? $arPrices['total_promo'] : $arPrices['total_price'];
		}
		else
		{
			$arPrices = $this->getBkRentPriceHelper()->getPriceOfDay($booking->getId(),$checkIn,$booking->getBookingType());
			$price = $arPrices['promo'] > 0 ? $arPrices['promo'] : $arPrices['price'];
		}
			
		if($useDefaultPrice == 1)
		{
			if($booking->getSpecialPrice() > 0)
			{
				$price += $booking->getSpecialPrice();
			}
			else
			{
				$price += $booking->getPrice();
			}
		}
		return $price;
	}
	/**
	* get Current Booking Item
	* return $item
	**/
	function getBkBookingItem()
	{
	    return $this->getBkProduct();
	}
	/**
	* get addons Selles 
	* @return array $itens
	**/
	function getAddonsSelles()
	{
		$product = $this->_coreRegistry->registry('product');
		$bookingId = $product->getId();
		$model = $this->_optionsFactory->create();
		$collection = $model->getBkOptions($bookingId);
		return $collection;
	}
	/**
	* get Values options
	* @return $items
	**/
	function getBkOptionSelectValues($optionId)
	{
		$model = $this->_optionsdropdownFactory->create();
		$collection = $model->getBkValueOptions($optionId);
		return $collection;
	}
	function getBkDiscounts()
	{
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$booking = $this->getBkProduct();
		$model = $this->_discountsFactory->create();
		$intToday = $this->_timezone->scopeTimeStamp();
		$symbol = $this->_currency->getCurrencySymbol();
		$bkType = 'per_day';
		$filedSort = 'discount_priority';
		$collection = $model->getBkDiscountItems($booking->getId(),$formatDate,$intToday,$symbol,$bkType,$filedSort);
		return $collection;
	}
	function getBookingRequest()
	{
		$booking = $this->getBkProduct();
		$checkIn = '';
		$checkOut = '';
		$qty = 1;
		$fromTimeH = 0;
		$fromTimeM = 0;
		$fromTimeT = 0;
		$toTimeH = 0;
		$toTimeM = 0;
		$toTimeT = 0;
		$tempCheckIn = '';
		$tempCheckOut = '';
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		//get id item when edit cart
		$params = $this->getRequest()->getParams();
		$itemId = 0;
		$action = $this->getRequest()->getActionName();
		// if edit cart
		if($action == 'configure')
		{
			$itemId = isset($params['id']) ? $params['id'] : 0;
		}
		$request = array();
		if($itemId > 0)
		{
			$request = $this->_bkOrderHelper->getBkRequestItemOption($itemId,$booking->getId());
		}
		else
		{
			if($booking->getBookingTime() == 2)
			{
				$serviceStart = explode(',',$booking->getBookingServiceStart());
				$serviceEnd = explode(',',$booking->getBookingServiceEnd());
				//echo $hourStart;
				$fromTimeH = $serviceStart[0];
				$fromTimeM = $serviceStart[1];
				$fromTimeT = $serviceStart[2];
				$toTimeH = $serviceEnd[0];
				$toTimeM = $serviceEnd[1];
				$toTimeT = $serviceEnd[2];
			}
			if(isset($params['check-in']) && isset($params['check-in']))
			{
				//if page search
				$checkIn = date($formatDate,strtotime($params['check-in']));
				$checkOut = date($formatDate,strtotime($params['check-out']));
				$tempCheckIn = $params['check-in'];
				$tempCheckOut = $params['check-out'];
			}
			$request = array(
				'check_in'=>$checkIn,
				'check_out'=>$checkOut,
				'temp_check_in'=>$tempCheckIn,
				'temp_check_out'=>$tempCheckOut,
				'qty'=>$qty,
				'from_time_h'=>$fromTimeH,
				'from_time_m'=>$fromTimeM,
				'from_time_t'=>$fromTimeT,
				'to_time_h'=>$toTimeH,
				'to_time_m'=>$toTimeM,
				'to_time_t'=>$toTimeT,
                'number_persons'=>isset($params['number_persons']) ? $params['number_persons'] : array()
			);
		}
		$request['action'] = $action;
		$request['item_id'] = $itemId;
		return $request;
	}
	/*
	* get review product
	*/
	function getBkReview($productId)
	{
		$reviewModel = $this->_summaryFactory->create();
		$currentStore = $this->getbkCurrentStore();
		$summary = $reviewModel->setStoreId($currentStore)->load($productId);
		return $summary;
	}
	function getBkFacilities($bookingId,$bookingType)
	{
		$fatilityModel = $this->_facilitiesFactory->create();
		$arSelect = array('*');
		$arConditoin = array('facility_booking_type'=>$bookingType,'facility_status'=>1);
		$collection = $fatilityModel->getBkFacilitiesById($bookingId,$arSelect,$arConditoin);
		return $collection;
	}
	/**
	* get Current Time from core
	* return int $time
	**/
	function getBkTmpTime()
	{
		return $this->_timezone->scopeTimeStamp();
	}
	/**
	* return Rent Price Helper
	**/
	function getBkRentPriceHelper()
	{
		return $this->_rentPrice;
	}
	/**
	* get Core Helper Price
	**/
	function getBkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/**
	* get Core Bk Helper Date
	**/
	function getBkHelperDate()
	{
		return $this->_bkHelperDate;
	}
	function getBkCurrencySymboy()
	{
		return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencySymbol();
	}
	function getBkUrlAjax($bookingId,$itemId = 0)
	{
		$urlCalendar = $this->getUrl('bookingsystem/booking/loadCalendar/booking_id/'.$bookingId);
		$urlBooking = $this->getUrl('bookingsystem/booking/booking/booking_id/'.$bookingId);
		if($itemId > 0)
		{
			$urlCalendar .= 'itemId/'.$itemId;
			$urlBooking .= 'itemId/'.$itemId;
		}
		$urlBooking = $this->getBkHelperDate()->formatUrlPro($urlBooking);
		$urlCalendar = $this->getBkHelperDate()->formatUrlPro($urlCalendar);
		return array(
			'url_calendar'=>$urlCalendar,
			'url_booking'=>$urlBooking
		);
	}
    function getAllBlockDays($bookingId)
    {
        return $this->_calendars->getBlockCalendars($bookingId);
    }
    function getAllUnavailableDays($bookingId,$roomId = 0)
    {
        $itemId = 0;
        $action = $this->getRequest()->getActionName();
        if($action == 'configure')
        {
            $itemId = $this->getRequest()->getParam('id',0);
        }
        $cartItems = array();
        $arOrder = array();
        if($roomId > 0)
        {
            $itemId = $this->getRequest()->getParam('itemId',0);
            $cartItems = $this->_bkOrderHelper->getRoomArrayItemIncart($bookingId,$roomId,$itemId);
            $arOrder = $this->_bkOrderHelper->getArrayItemsInOrder($roomId,1);
        }
        else
        {
            $cartItems = $this->_bkOrderHelper->getArrayItemIncart($bookingId,$itemId);
            $arOrder = $this->_bkOrderHelper->getArrayItemsInOrder($bookingId,0);
        }
        $arOrder = array_merge($arOrder,$cartItems);
        return $arOrder;
    }
    function getCurrnetBkCalendars($bookingId,$bookingType = 'per_day')
    {
        $arrayseletct = array('calendar_startdate','calendar_enddate','calendar_qty','calendar_default_value','calendar_status');
        $conditions = array('calendar_booking_type'=>$bookingType);
        $calendars = $this->_calendars->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions,'calendar_default_value');
        return $calendars;
    }
    /*
     * get Extract price person
     * @return array
     * */
    function getBkPricePersons()
    {
        $extractPersons = array();
        $bookingId = $this->getRequest()->getParam('booking_id',0);
        $checkIn = $this->getRequest()->getParam('check_in','');
        $strDay = '';
        $formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
        if($this->_bkHelperDate->validateBkDate($checkIn,$formatDate))
        {
            $strDay = $this->_bkHelperDate->convertFormatDate($checkIn,$formatDate);
        }
        if($strDay == '' || (int)$bookingId == 0)
        {

        }
        else
        {
            $calendar = $this->_calendars->getCalendarBetweenDays($bookingId,$strDay);
            if($calendar && $calendar->getId() && $calendar->getExtractPersons() != '')
            {
                $extractPersons = json_decode($calendar->getExtractPersons(),true);
            }
        }
        return $extractPersons;
    }
    function  getBkRequestPersons()
    {
        $bkRequest = array();
        $itemId = (int)$this->getRequest()->getParam('itemId',0);
        $bookingId = $this->getRequest()->getParam('booking_id',0);
        if($itemId > 0)
        {
            $bkRequest = $this->_bkOrderHelper->getBkRequestItemOption($itemId,$bookingId);
        }
        return $bkRequest;
    }
	function getBkHelperText()
	{
		return $this->_bkText;
	}
	function getBkBaseUrl()
	{
		return $this->_imageModel->getBaseUrl();
	}
	/**resize image**/
	function imageResize($image,$width,$height)
	{
		$urlImage = $this->_imageModel->imageResize($image,$width,$height);
		return $urlImage;
	}
}