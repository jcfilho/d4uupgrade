<?php

namespace Daytours\Bookingsystem\Block;

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
use Magento\Framework\Controller\Result\JsonFactory;
use Daytours\LastMinute\Model\Product as LastMinuteProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Booking extends \Magebay\Bookingsystem\Block\Booking
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
     * @var  \Magebay\Bookingsystem\Model\Image
    **/
	protected $_imageModel;
	/** Helper BkOrderHelper
     *
     * @var \Magebay\Bookingsystem\Helper\BkText
    **/
	protected $_bkText;

	protected $_calendars;

    /**
     * @var LastMinuteProduct
     **/
    protected $_lastMinuteProduct;

    /**
     * @var ProductRepositoryInterface
     **/
    protected $_productRepository;

    /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */

    protected $_resultJsonFactory;
    /**
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    private $dataBookingSystem;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;

    /**
     * Booking constructor.
     * @param Template\Context $context
     * @param Registry $coreRegistry
     * @param PriceHelper $priceHelper
     * @param Timezone $timezone
     * @param Currency $currency
     * @param SummaryFactory $summaryFactory
     * @param BookingsFactory $bookingFactory
     * @param OptionsFactory $optionsFactory
     * @param OptionsdropdownFactory $optionsdropdownFactory
     * @param DiscountsFactory $discountsFactory
     * @param FacilitiesFactory $facilitiesFactory
     * @param BkHelperDate $bkHelperDate
     * @param RentPrice $rentPrice
     * @param BkOrderHelper $bkOrderHelper
     * @param BkSimplePriceHelper $bkSimplePriceHelper
     * @param BkText $bkText
     * @param ImageModel $imageModel
     * @param \Magebay\Bookingsystem\Model\Calendars $calendars
     * @param JsonFactory $resultJsonFactory
     * @param LastMinuteProduct $lastMinuteProduct
     * @param ProductRepositoryInterface $productRepository
     * @param \Daytours\Bookingsystem\Helper\Data $dataBookingSystem
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     */
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
        JsonFactory $resultJsonFactory,
        LastMinuteProduct $lastMinuteProduct,
        ProductRepositoryInterface $productRepository,
        \Daytours\Bookingsystem\Helper\Data $dataBookingSystem,
        \Magento\Framework\Serialize\Serializer\Json $json,
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
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_lastMinuteProduct = $lastMinuteProduct;
        $this->_productRepository = $productRepository;
        parent::__construct($context,$coreRegistry,$priceHelper,$timezone,$currency,$summaryFactory,$bookingFactory,$optionsFactory,$optionsdropdownFactory,$discountsFactory,$facilitiesFactory,$bkHelperDate,$rentPrice,$bkOrderHelper,$bkSimplePriceHelper,$bkText,$imageModel,$calendars,$data);

        $this->dataBookingSystem = $dataBookingSystem;
        $this->json = $json;
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

    function getBkProductById($idProduct)
    {
        $bookingModel = $this->_bookingFactory->create();
        $booking = $bookingModel->getBooking($idProduct);
        return $booking;
    }

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

            /*Apply discount from catalog rule if exist, compare with special price and replace if is less than special price*/
            $arPrices['total_promo'] = $this->dataBookingSystem->getPriceBetweenSpecialCalendarAndCatalogRule($booking->getId(),$arPrices['total_price'],$arPrices['total_promo']);

            $arPrices['total_price'] *= $qty;
            $arPrices['total_promo'] *= $qty;
            $arPrices['booking_id'] = $booking->getId();
            $arPrices['booking_time'] = $booking->getBookingTime();
            $arPrices['check_in'] = isset($arPrices['check_in']) ? $arPrices['check_in'] : $checkIn;
            $arPrices['check_out'] = isset($arPrices['check_out']) ? $arPrices['check_out'] : $checkOut;
        }
        return $arPrices;
    }

    /* get results when custom check booking
	* @params string $checkIn, $checkOut, $typeBooking, $formDatem,$formType,$toDate,$toType int $number, $qty,  \
	* @return array $result include int $totalPrice, $totalPrmo,$totalDays,$totalHours, string $messageError
	*/
    function getBookingResultTwo()
    {
        //get new object search
        $formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
        $checkIn = '';
        $checkOut = '';
        $checkInTwo = '';
        $checkOutTwo = '';
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
            if(isset($params['check_in_two']) && trim($params['check_in_two']) != '')
            {
                if($this->getBkHelperDate()->validateBkDate($params['check_in_two'],$formatDate))
                {
                    $checkInTwo = $this->getBkHelperDate()->convertFormatDate($params['check_in_two']);
                }
            }
            if(isset($params['check_out_two']) && trim($params['check_out_two']) != '')
            {
                if($this->getBkHelperDate()->validateBkDate($params['check_out_two'],$formatDate))
                {
                    $checkOutTwo = $this->getBkHelperDate()->convertFormatDate($params['check_out_two']);
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
                $arPrices = $this->_bkSimplePriceHelper->getPriceBetweenDaysTwoCalendars($booking,$checkIn,$checkOut,$checkInTwo,$checkOutTwo,$qty,$itemId,$paramAddons);
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

            /*Apply discount from catalog rule if exist, compare with special price and replace if is less than special price*/
            $arPrices['total_promo'] = $this->dataBookingSystem->getPriceBetweenSpecialCalendarAndCatalogRule($booking->getId(),$arPrices['total_price'],$arPrices['total_promo']);

            $arPrices['total_price'] *= $qty;
            $arPrices['total_promo'] *= $qty;
            $arPrices['booking_id'] = $booking->getId();
            $arPrices['booking_time'] = $booking->getBookingTime();
            $arPrices['check_in'] = isset($arPrices['check_in']) ? $arPrices['check_in'] : $checkIn;
            $arPrices['check_out'] = isset($arPrices['check_out']) ? $arPrices['check_out'] : $checkOut;
            $arPrices['check_in_two'] = isset($arPrices['check_in_two']) ? $arPrices['check_in_two'] : $checkInTwo;
            $arPrices['check_out_two'] = isset($arPrices['check_out_two']) ? $arPrices['check_out_two'] : $checkOutTwo;
        }
        return $arPrices;
    }

    function getBookingRequest()
    {

        $booking = $this->getBkProduct();
        $checkIn = '';
        $checkOut = '';
        $checkInTwo = '';
        $checkOutTwo = '';
        $qty = 1;
        $fromTimeH = 0;
        $fromTimeM = 0;
        $fromTimeT = 0;
        $toTimeH = 0;
        $toTimeM = 0;
        $toTimeT = 0;
        $tempCheckIn = '';
        $tempCheckOut = '';
        $tempCheckInTwo = '';
        $tempCheckOutTwo = '';
        $formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
        //get id item when edit cart
        $params = $this->getRequest()->getParams();
        $itemId = 0;
        $action = $this->getRequest()->getActionName();
        $module = $this->getRequest()->getModuleName();
        // if edit cart
        if($action == 'configure' && $module == 'checkout')
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
            if(isset($params['check-in-two']) && isset($params['check-in-two']))
            {
                //if page search
                $checkInTwo = date($formatDate,strtotime($params['check-in-two']));
                $checkOutTwo = date($formatDate,strtotime($params['check-out-two']));
                $tempCheckInTwo = $params['check-in-two'];
                $tempCheckOutTwo = $params['check-out-two'];
            }
            $request = array(
                'check_in'=>$checkIn,
                'check_out'=>$checkOut,
                'check_in_two'=>$checkInTwo,
                'check_out_two'=>$checkOutTwo,
                'temp_check_in'=>$tempCheckIn,
                'temp_check_out'=>$tempCheckOut,
                'temp_check_in_two'=>$tempCheckInTwo,
                'temp_check_out_two'=>$tempCheckOutTwo,
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
        $resultWishlist = $this->loadDataIfComeFromEditWishlist($module,$formatDate);
        if( count($resultWishlist) > 0 ){
            $request = array_merge($request,$resultWishlist);
        }
        return $request;
    }

    function getCurrnetBkCalendars($bookingId,$bookingType = 'per_day')
    {
        $arrayseletct = array('calendar_startdate','calendar_enddate','calendar_qty','calendar_default_value','calendar_status');
        $conditions = array('calendar_booking_type'=>$bookingType,'calendar_number' => \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_DEFAULT);
        $calendars = $this->_calendars->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions,'calendar_default_value');
        return $calendars;
    }

    function getCurrnetBkCalendarsTwo($bookingId,$bookingType = 'per_day')
    {
        $arrayseletct = array('calendar_startdate','calendar_enddate','calendar_qty','calendar_default_value','calendar_status');
        $conditions = array('calendar_booking_type'=>$bookingType,'calendar_number' => \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_SECOND);
        $calendars = $this->_calendars->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions,'calendar_default_value');
        return $calendars;
    }

    function loadDataIfComeFromEditWishlist($module,$formatDate){
        $result = [];

        if( $module == 'wishlist' ){
            $result['exist_wishlist'] = true;
            $formatDate = 'Y-m-d';
            $optionsWishlist = $this->_coreRegistry->registry('wishlist_item');
            $optionsList = $optionsWishlist->getOptions();
            $dataWishlist = [];
            foreach ($optionsList as $key => $item){
                if($item['code'] != 'info_buyRequest'){
                    $dataWishlist[] = [
                        'code' => $item['code'],
                        'value' => $item['value']
                    ];

                    $splitOption = explode('_',$item['code']);
                    if( is_numeric($splitOption[1]) ){
                        $idAddons = $splitOption[1];
                        $valueConverted = $this->separateValues($item['value']);
                        $result['addons'][$idAddons] = $valueConverted;
                    }else{
                        if($item['code'] == 'optionsbooking_goingroundtrip'){
//                            if( $item['value'] == 1 ){
//                                $result['going'] = ' checked';
//                                $result['roundtrip'] = '';
//                            }
//                            if( $item['value'] == 2 ){
//                                $result['going'] = ' ';
//                                $result['roundtrip'] = ' checked';
//                            }
                            $result['goingroundtrip'] = $item['value'];
                        }
                        if($item['code'] == 'optionsbooking_check_in'){
                            $result['check_in'] = $item['value'];
                            $date = str_replace('/', '-', $item['value']);
                            $result['temp_check_in'] = date($formatDate,strtotime($date));
                        }
                        if($item['code'] == 'optionsbooking_check_out'){
                            $result['check_out'] = $item['value'];
                            $date = str_replace('/', '-', $item['value']);
                            $result['temp_check_out'] = date($formatDate,strtotime($date));
                        }
                        if($item['code'] == 'optionsbooking_goingroundtrip'){
                            $result['goingroundtrip'] = $item['value'];
                        }
                        if($item['code'] == 'optionsbooking_check_in_two'){
                            $result['check_in_two'] = $item['value'];
                            $date = str_replace('/', '-', $item['value']);
                            $result['temp_check_in_two'] = date($formatDate,strtotime($date));
                        }
                        if($item['code'] == 'optionsbooking_check_out_two'){
                            $result['check_out_two'] = $item['value'];
                            $date = str_replace('/', '-', $item['value']);
                            $result['temp_check_out_two'] = date($formatDate,strtotime($date));
                        }
                    }
                }
            }

        }

        return $result;
    }

    private function separateValues($value){
        $separeValues = [];
        if( preg_match('/,/',$value) ){
            $separeValues = explode(',',$value);
        }else{
            return $value;
        }

        return $separeValues;
    }

    /**
     * Check if the product is Last Minute
     *
     * @return bool
     */
    public function isLastMinute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $product = $this->_productRepository->getById($params['product']);
            return $this->_lastMinuteProduct->isLastMinute($product, $params);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }

    public function convertJson($array){
        return $this->json->serialize($array);
    }

}