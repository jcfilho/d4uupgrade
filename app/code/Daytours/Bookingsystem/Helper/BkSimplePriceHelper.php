<?php
 
namespace Daytours\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magento\Backend\Model\Auth\Session as BkBackendSession;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkOrderHelper;


class BkSimplePriceHelper extends \Magebay\Bookingsystem\Helper\BkSimplePriceHelper
{
	/**
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
    */
	protected $_timezone;
	/**
     *
     * @var \Magento\Sales\Model\Order
    */
	protected $_bkCoreOrders;
	/**
     *
     * @var \Magento\Backend\Model\Auth\Session
    */
	protected $_bkbackendSession;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     *  Model
     *
     * @var \Magebay\Bookingsystem\Model\DiscountsFactory
    */
	protected $_discountsFactory;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\Bookingorders
    */
	protected $_bookingorders;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\OptionsFactory
    */
	protected $_optionsFactory;
	/**
	* @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
     */
	protected $_dropdownFactory;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory
    */
	protected $_calendarsFactory;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\BkOrderHelper
    */
	protected $_bkOrderHelper;
    /**
     * @var \Daytours\Wishlist\Helper\Data
     */
    private $helperWishList;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $http;

    /**
     * BkSimplePriceHelper constructor.
     * @param Context $context
     * @param Timezone $timezone
     * @param BkCoreOrders $bkCoreOrders
     * @param BkBackendSession $bkbackendSession
     * @param BkHelperDate $bkHelperDate
     * @param DiscountsFactory $discountsFactory
     * @param BookingordersFactory $bookingorders
     * @param OptionsFactory $optionsFactory
     * @param OptionsdropdownFactory $optionsdropdownFactory
     * @param CalendarsFactory $calendarsFactory
     * @param BkOrderHelper $bkOrderHelper
     * @param \Daytours\Wishlist\Helper\Data $helperWishList
     * @param \Magento\Framework\App\Request\Http $http
     */
    public function __construct(
       Context $context,
	   Timezone $timezone,
	   BkCoreOrders $bkCoreOrders,
	   BkBackendSession $bkbackendSession,
	   BkHelperDate $bkHelperDate,
	   DiscountsFactory $discountsFactory,
	   BookingordersFactory $bookingorders,
	   OptionsFactory $optionsFactory,
	   OptionsdropdownFactory $optionsdropdownFactory,
	   CalendarsFactory $calendarsFactory,
	   BkOrderHelper $bkOrderHelper,
       \Daytours\Wishlist\Helper\Data $helperWishList,
       \Magento\Framework\App\Request\Http $http
    ) 
	{
       parent::__construct(
           $context,
           $timezone,
           $bkCoreOrders,
           $bkbackendSession,
           $bkHelperDate,
           $discountsFactory,
           $bookingorders,
           $optionsFactory,
           $optionsdropdownFactory,
           $calendarsFactory,
           $bkOrderHelper
       );
	   $this->_timezone = $timezone;
	   $this->_bkCoreOrders = $bkCoreOrders;
	   $this->_bkbackendSession = $bkbackendSession;
	   $this->_bkHelperDate = $bkHelperDate;
	   $this->_discountsFactory = $discountsFactory;
	   $this->_optionsFactory = $optionsFactory;
	   $this->_dropdownFactory = $optionsdropdownFactory;
	   $this->_bookingorders = $bookingorders;
	   $this->_calendarsFactory = $calendarsFactory;
	   $this->_bkOrderHelper = $bkOrderHelper;
        $this->helperWishList = $helperWishList;
        $this->http = $http;
    }

    function getOrderTotalQuantityCatendarTwo($strDay,$bookingId,$bookingTime = 1,$oldOrderItemId = 0)
    {
        // $arraySelect = array('check_in','check_out','qty');
        $arSelectOrder = array();
        $arAttributeConditions = array();
        $condition = 'booking_id = '.$bookingId;
        $arrayBooking = array('bkorder_check_in_two','bkorder_check_out_two','bkorder_qty_two','bkorder_order_id','bkorder_qt_item_id');
        $totalOrder = 0;
        $orderModel = $this->_bookingorders->create();
        $orders = $orderModel->getCollection()
            ->addFieldToSelect($arrayBooking)
            ->addFieldToFilter('bkorder_booking_id',$bookingId)
            ->addFieldToFilter('bkorder_room_id',0);
        if(count($orders))
        {
            foreach($orders as $order)
            {
                $bkCoreModel = $this->_bkCoreOrders;
                $defaultOrderCollection = $bkCoreModel->getCollection()
                    ->addFieldToFilter('entity_id',$order->getBkorderOrderId());
                // $defaultOrder = $bkCoreModel->load($order->getBkorderOrderId());
                $defaultOrder = $defaultOrderCollection->getFirstItem();
                if($defaultOrder->getId())
                {
                    if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
                    {
                        continue;
                    }
                }
                else
                {
                    continue;
                }
                if($oldOrderItemId > 0)
                {
                    if($order->getBkorderQtItemId() == $oldOrderItemId)
                    {
                        continue;
                    }
                }
                if($bookingTime == 1)
                {
                    if(strtotime($order->getBkorderCheckInTwo()) <= strtotime($strDay) && strtotime($order->getBkorderCheckOutTwo()) > strtotime($strDay))
                    {
                        $totalOrder += $order->getBkorderQtyTwo();
                    }
                }
                else
                {
                    if(strtotime($order->getBkorderCheckInTwo()) <= strtotime($strDay) && strtotime($order->getBkorderCheckOutTwo()) >= strtotime($strDay))
                    {
                        $totalOrder += $order->getBkorderQtyTwo();
                    }
                }

            }
        }
        return $totalOrder;
    }

	function getPriceBetweenDaysTwoCalendars($booking,$checkIn,$checkOut,$checkInTwo,$checkOutTwo,$qty = 1,$itemId = 0,$paramAddons = array(),$oldOrderItemId = 0,$isRoundTrip = null)
	{

        $params = $this->_getRequest()->getParams();

        //if comes from checkout
        if( !is_null($isRoundTrip) ){
            $params['isRoundTrip'] = $isRoundTrip;
        }
        //----

        $actionName = "";
        try {
            $action = $this->http;
            if($action) {
                $actionName = $this->http->getFullActionName();
            }
        } catch(\Exception $e) {
            $actionName = "";
        }
        if ($actionName == 'wishlist_index_cart' || $actionName == 'wishlist_index_allcart')
        {
            $params = $this->http->getParams();
            if ($actionName == 'wishlist_index_cart' || $actionName == 'wishlist_index_allcart'){
                $paramsRegistryFromWishlist = $this->helperWishList->getParamsFromWishListRegistry($actionName);
                if( $paramsRegistryFromWishlist  ){
                    $params = $paramsRegistryFromWishlist;
                }else{
                    return;
                }
            }
        }

        $existcCalendarTwo = true;
		$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		//convert date to int
		$intCheckIn = strtotime($checkIn);
        $intCheckInTwo = strtotime($checkInTwo);
		$tempCheckIn = $intCheckIn;
        $tempCheckInTwo = $intCheckInTwo;
		$intCheckOut = strtotime($checkOut);
        $intCheckOutTwo = strtotime($checkOutTwo);
		$oneDay = 24 * 60 * 60;
		if($intCheckIn == $intCheckOut && $booking->getBookingTime() == 1)
		{
			$intCheckOut += $oneDay;
            $intCheckOutTwo += $oneDay;
		}
		// minimum day, maximum day
		$minimum = $booking->getBookingMinDays();
		$maximum = $booking->getBookingMaxDays();
		$totalPrice = 0;
		$totalPromo = 0;
		$checkPromo = false;
		
		$strError = '';
		$strNote = '';
		$intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
		$today = date('Y-m-d',$intCoreCurrentTime);
		$intToday = strtotime($today);
		$totalSaving = 0;
		//temp total price for discount type last minute
		$tempPriceDiscount = 0;
		$tempPromoDiscount = 0;
		//get data for discount
		$priceAnount1 = 0; // price for last minute amount
		$promoAnount1 = 0; // price last minute
		$priceAnount2 = 0; // price for first moment
		$promoAnount2 = 0; // price last first moment
		$priceAnount3 = 0;
		$promoAnount3 = 0;
		//max item for kind of type
		$tempMaxItem1 = 0;
		$tempMaxItem2 = 0;
		$tempMaxItem3 = 0;
		$msgDiscount = '';
		//number days
		$numberDays = ($intCheckOut - $intCheckIn) / $oneDay;
		$minimum = $booking->getBookingTime() == 4 ? $minimum - 1 : $minimum;
		if($minimum > 0)
		{
			//$strNote = __('Note : Minimum days are %1, You must pay for %2 Days',$minimum,$minimum);
			//update check out to calculator price
			$intCheckOut = $intCheckIn + ($oneDay * $minimum);
            $intCheckOutTwo = $intCheckInTwo + ($oneDay * $minimum);
		}
		if($intCheckIn < $intToday)
		{
			$strError = __('You can not book previous day!');
		}
        if( $params['isRoundTrip'] ){
            if($intCheckInTwo < $intToday)
            {
                $strError = __('You can not book previous day!');
            }
        }

		/*elseif($numberDays > $maximum && $maximum > 0)
		{
			$strError = __('Maximum days are %1, please check again',$maximum);
		}*/
		$totalDays = ($intCheckOut - $intCheckIn) / $oneDay;
		if($intCheckOut < $intCheckIn)
		{
			$strError = __('Dates are not available. Please check again');
		}
        if($params['isRoundTrip'])
        {
            if($intCheckOutTwo < $intCheckInTwo)
            {
                $strError = __('Dates are not available. Please check again');
            }
        }

		$dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
		$discountModel = $this->_discountsFactory->create();
		$arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
		$arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
		$arDiscount3 = $discountModel->getLengthDiscount($booking->getId(),'per_day',$totalDays);
		$newCheckIn = date('Y-m-d',$intCheckIn);
		$newCheckOut = '';
        $newCheckInTwo = date('Y-m-d',$intCheckInTwo);
        $newCheckOutTwo = '';
		if($strError == '')
		{
			$loop1 = 0; // for last minute
			$loop2 = 0; // for first moment
			$disableDays = trim($booking->getDisableDays());
			$arDisableTextDays = array(
				0=>__('Sunday'),
				1=>__('Monday'),
				2=>__('Tuesday'),
				3=>__('Wednesday'),
				4=>__('Thursday'),
				5=>__('Friday'),
				6=>__('Saturday')
			);
			$strDisableTextDays = '';
			if($disableDays != '')
			{
				$disableDays = explode(',',$disableDays);
				foreach($disableDays as $disableDay)
				{
					if($strDisableTextDays == '')
					{
						$strDisableTextDays = $arDisableTextDays[$disableDay];
					}
					else
					{
						$strDisableTextDays .= ', '.$arDisableTextDays[$disableDay];
					}
					
				}
			}
			else
			{
				$disableDays = array();
			}
			$tempIntCheckOut2 = $booking->getBookingTime() == 4 ? ($intCheckOut + $oneDay) : $intCheckOut;
            $totalDays  = $booking->getBookingTime() == 4 ? ($totalDays + 1) : $totalDays;

            $strDay = date('Y-m-d',$intCheckIn);
            $calendarsModel = $this->_calendarsFactory->create();
            $calendarOne = \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_DEFAULT;
            $calendar = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType(),$calendarOne);
            if($calendar->getId())
            {
                //total order quantity
                $totalOrder = $this->getOrderTotalQuantity($strDay,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
                //get total booking Item in cart
                $cartQty = $this->getTotalItemInCartTwo($booking->getId(),$booking->getBookingTime(),$strDay,$itemId,$calendarOne);
                $avaliableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty);
                if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block') || $avaliableQty < 0)
                {
                    $strError = __('Dates are not available. Please check again');
                }
            }
            $price = ($calendar->getCalendarPrice()) ? $calendar->getCalendarPrice() : 0;
            $promo = ($calendar->getCalendarPromo()) ? $calendar->getCalendarPromo() : 0;
            $totalPrice += $price;

            /* Calendar Two*/
            if($params['isRoundTrip'])
            {
                $strDayTwo = date('Y-m-d',$intCheckInTwo);
                $calendarTwoCalendarNumber = \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_SECOND;
                $calendarTwo = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDayTwo,$booking->getBookingType(),$calendarTwoCalendarNumber);
                if($calendarTwo->getId())
                {
                    //total order quantity
                    $totalOrderTwo = $this->getOrderTotalQuantityCatendarTwo($strDayTwo,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
                    //get total booking Item in cart
                    $cartQtyTwo = $this->getTotalItemInCartTwo($booking->getId(),$booking->getBookingTime(),$strDayTwo,$itemId,$calendarTwoCalendarNumber);
                    $avaliableQty = $calendarTwo->getCalendarQty() -  ($qty + $totalOrderTwo + $cartQtyTwo);
                    if(($calendarTwo->getCalendarStatus() == 'unavailable' || $calendarTwo->getCalendarStatus() == 'block') || $avaliableQty < 0)
                    {
                        $strError = __('Dates are not available. Please check again');
                    }
                }
                $priceTwo = $calendarTwo->getCalendarPrice();
                $pricePromoCalendarTwo = $calendarTwo->getCalendarPromo();
                if( $pricePromoCalendarTwo && $pricePromoCalendarTwo > 0 ){
                    if( $promo > 0 ){
                        //exist promo on the first calendar
                        $promo += $pricePromoCalendarTwo;
                    }else{
                        // doesn't exist promo on the first calendar, so, we have to equal $promo to $price (first calendar)
                        $promo = $price;
                        $promo += $pricePromoCalendarTwo;
                    }
                }else if($promo > 0){
                    //if exist promo on first calendar only
                    $promo += $priceTwo;
                }
                $totalPrice += $priceTwo;
            }

				//last minute
				if(count($arDiscount1) )
				{
					$priceAnount1 += $price;
				}
				//first moment
				if(count($arDiscount2) )
				{
					$priceAnount2 += $price;
				}
				//length if reservations
				if(count($arDiscount3) )
				{
					$priceAnount3 += $price;
				}
				if($promo > 0)
				{
					$totalPromo += $promo;
					$checkPromo = true;
					//last minute
					if(count($arDiscount1) )
					{
						$promoAnount1 += $promo;
					}
					//first moment
					if(count($arDiscount2) )
					{
						$promoAnount2 += $promo;
					}
					//length if reservations
					if(count($arDiscount3) )
					{
						$promoAnount3 += $promo;
					}
				}
				else
				{
					$totalPromo += $price;
					//last minute
					if(count($arDiscount1) )
					{
						$promoAnount1 += $price;
					}
					//first moment
					if(count($arDiscount2) )
					{
						$promoAnount2 += $price;
					}
					//length if reservations
					if(count($arDiscount3) )
					{
						$promoAnount3 += $price;
					}
				}
				$totalDays++;
				$intCheckIn += $oneDay;
			//	$loop1++;
			//}
			$newCheckOut = date('Y-m-d',$intCheckOut);
            $newCheckOutTwo = date('Y-m-d',$intCheckOutTwo);
			if(count($arDiscount1) && $arDiscount1['discount_max_items'] == 0)
			{
				$priceAnount1 = $totalPrice;
				$promoAnount1 = $totalPromo;
			}
			if(count($arDiscount2) && $arDiscount2['discount_max_items'] == 0)
			{
				$priceAnount2 = $totalPrice;
				$promoAnount2 = $totalPromo;
			}
			if(count($arDiscount3) && $arDiscount3['discount_max_items'] == 0)
			{
				$priceAnount3 = $totalPrice;
				$promoAnount3 = $totalPromo;
			}
		}
		$tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $totalDays) ? $arDiscount1['discount_max_items'] : $totalDays;
		$tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $totalDays) ? $arDiscount2['discount_max_items'] : $totalDays;
		$tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $totalDays) ? $arDiscount3['discount_max_items'] : $totalDays;
		if(count($paramAddons))
		{
			foreach($paramAddons as $keyAdd => $paramAddon)
			{
				$tempparamAddon = array($keyAdd=>$paramAddon);
				$arAddonPrice = $this->getAddonsPrice($tempparamAddon,$booking->getId());
				if(count($arAddonPrice))
				{
					if($arAddonPrice['error'] == '')
					{
						if($arAddonPrice['price_type'] == 1)
						{
							$totalPrice += $arAddonPrice['price'] * $totalDays;
						}
						else
						{
							$totalPrice += $arAddonPrice['price'];
						}
						if(count($arDiscount1))
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$priceAnount1 += $arAddonPrice['price'] * $tempMaxItem1;
								if($checkPromo)
									$promoAnount1 += $arAddonPrice['price'] * $tempMaxItem1;
							}
							else
							{
								$priceAnount1 += $arAddonPrice['price'];
								if($checkPromo)
									$promoAnount1 += $arAddonPrice['price'];
							}
							
						}
						if(count($arDiscount2))
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$priceAnount2 += $arAddonPrice['price'] * $tempMaxItem2;
								if($checkPromo)
									$promoAnount2 += $arAddonPrice['price'] * $tempMaxItem2;
							}
							else
							{
								$priceAnount2 += $arAddonPrice['price'];
								if($checkPromo)
									$promoAnount2 += $arAddonPrice['price'];
							}
							
						}
						if(count($arDiscount3))
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$priceAnount3 += $arAddonPrice['price'] * $tempMaxItem3;
								if($checkPromo)
									$promoAnount3 += $arAddonPrice['price'] * $tempMaxItem3;
							}
							else
							{
								$priceAnount3 += $arAddonPrice['price'];
								if($checkPromo)
									$promoAnount3 += $arAddonPrice['price'];
							}
							
						}
						if($checkPromo && $totalPromo > 0)
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$totalPromo += $arAddonPrice['price'] * $totalDays;
							}
							else
							{
								$totalPromo += $arAddonPrice['price'];
							}
							
						}
					}
					else
					{
						$strError = $arAddonPrice['error'];
						break;
					}
				}
				
			}
		}
		$salePrice = 0;
		$salePromo = 0;
		//discount sale. new request from customer.
		if(count($arDiscount1) || count($arDiscount2) || count($arDiscount3))
		{
			$oklastMinute = false;
			$okFirstMoment = false;
			$maxPeriod = 0;
			$maxPeriod2 = 0;
			//for last minute
			if(count($arDiscount1))
			{
				$maxPeriod = $intToday + ($oneDay * $arDiscount1['discount_period']);
				if(($priceAnount1 > 0 && $tempCheckIn < $maxPeriod))
				{
					$oklastMinute = true;
				}
			}
			if(count($arDiscount2))
			{
				$maxPeriod2 = $intToday + ($oneDay * $arDiscount2['discount_period'])  - $oneDay;
				if(($priceAnount2 > 0 && $tempCheckIn > $maxPeriod2))
				{
					$okFirstMoment = true;	
				}
			}
			if($okFirstMoment && $oklastMinute)
			{
				if($arDiscount1['discount_priority'] > $arDiscount2['discount_priority'])
				{
					$okFirstMoment = true;
					$oklastMinute = false;
				}
				else
				{
					$okFirstMoment = false;
					$oklastMinute = true;
				}
			}
			if($oklastMinute)
			{
				$salePrice += $discountModel->getPriceDiscounts($arDiscount1['discount_max_items'],$arDiscount1['discount_amount'],$arDiscount1['discount_amount_type'],$totalPrice,$priceAnount1,$tempMaxItem1);
				$salePromo += $discountModel->getPriceDiscounts($arDiscount1['discount_max_items'],$arDiscount1['discount_amount'],$arDiscount1['discount_amount_type'],$totalPromo,$promoAnount1,$tempMaxItem1);
				$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;
			}
			elseif($okFirstMoment)
			{
				$salePrice += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPrice,$priceAnount2,$tempMaxItem2);
				$salePromo += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPromo,$promoAnount2,$tempMaxItem2);
				$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;	
			}
			if(count($arDiscount3) && $totalDays >= $arDiscount3['discount_period'])
			{
				// $tempTotalPrice = $totalPrice - $salePrice;
				$salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPrice,$priceAnount3,$tempMaxItem3);
				if($checkPromo && $totalPromo > 0)
				{
					// $tempTotalPromo = $totalPromo - $salePromo;
					$salePromo += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPromo,$promoAnount3,$tempMaxItem3);
				}
				$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;

			}
		}
		if($salePrice > 0)
		{
			if($checkPromo)
			{
				$totalPromo = $totalPromo - $salePromo;
			}
			else
			{
				$salePromo  = $salePrice;
				$totalPromo = $totalPrice - $salePrice;
			}
		}
		else
		{
			if(!$checkPromo)
			{
				$totalPromo = 0;
			}
			else
			{
				$totalSaving = $totalPrice - $totalPromo;
			}
		}
		return array(
			'total_price'=>$totalPrice,
			'total_promo'=>$totalPromo,
			'total_saving'=>$totalSaving,
			'total_days'=>$totalDays,
			'str_error'=>$strError,
			'str_note'=>$strNote,
			'msg_discount'=>$msgDiscount,
            'check_in'=>$newCheckIn,
            'check_out'=>$newCheckOut,
            'check_in_two'=>$newCheckInTwo,
            'check_out_two'=>$newCheckOutTwo
		);
	}

    /**
     * get total booking item in cart on calendar two
     * @param $booking item, string $strDay has format is Y-m-d,$itemId
     * @return int $total
     **/
    function getTotalItemInCartTwo($bookingId,$bookingTime,$strDay,$itemId = 0,$calendarNumber = 1)
    {
        $total = 0;
        $isBackend = false;
        if($this->_bkbackendSession->isLoggedIn())
        {
            $isBackend = true;
        }
        $arrayItem = $this->_bkOrderHelper->getArrayItemIncartTwo($bookingId,$itemId,$isBackend);
        //convert time to int
        $timeDay = strtotime($strDay);
        foreach($arrayItem as $item)
        {
            $intCheckIn = strtotime($item['check_in']);
            $intCheckOut = strtotime($item['check_out']);// Checkout is the same day
            if( $calendarNumber == \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_SECOND ){
                if( isset($item['check_in_two']) ){
                    if( empty($item['check_in_two']) ){
                        return 0;
                    }
                }else{
                    return 0;
                }
                $intCheckIn = strtotime($item['check_in_two']);
                $intCheckOut = strtotime($item['check_out_two']);// Checkout is the same day
            }

            //if booking time is daily , I do not get total of final day.
            if($bookingTime == 1)
            {
                $intCheckOut -= 60*60*24;
            }
            if($bookingTime == 5)
            {
                $tempQty = $item['qty'];
                $persons = isset($item['number_persons']) ? $item['number_persons'] : array();
                if(count($persons))
                {
                    $tempQty = 0;
                    foreach ($persons as $person)
                    {
                        if((int)$person > 0)
                        {
                            $tempQty += $person;
                        }
                    }
                }
                $total += $tempQty;
            }
            else
            {
                if($intCheckIn <= $timeDay && $timeDay <= $intCheckOut)
                {
                    $total += $item['qty'];
                }
            }

        }
        return $total;
    }
}
 