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
use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magebay\Bookingsystem\Helper\IntervalsPrice;
use Magebay\Bookingsystem\Model\CalendarsFactory;

class Intervals extends \Magebay\Bookingsystem\Block\Booking
{
	
	 protected $_intervalhoursFactory;
	 /**
     * @var \Magebay\Bookingsystem\Helper\IntervalsPrice;
     */
	protected $_intervalsPrice;
	protected $_calendarFactory;
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
		IntervalhoursFactory $intervalhoursFactory,
		IntervalsPrice $intervalsPrice,
		CalendarsFactory $calendarsFactory,
		array $data = []
	) 
	{
		$this->_intervalhoursFactory = $intervalhoursFactory;
		$this->_intervalsPrice = $intervalsPrice;
		$this->_calendarFactory = $calendarsFactory;
        parent::__construct(
				$context,
				$coreRegistry,
				$priceHelper,
				$timezone,
				$currency,
				$summaryFactory,
				$bookingFactory,
				$optionsFactory,
				$optionsdropdownFactory,
				$discountsFactory,
				$facilitiesFactory,
				$bkHelperDate,
				$rentPrice,
				$bkOrderHelper,
				$bkSimplePriceHelper,
				$bkText,
				$imageModel,
                $calendars,
				$data
			);
	}
	function getBookingRequest()
	{
		$checkIn = '';
		$tempCheckIn = '';
		$intervals = array();
		$qty = 1;
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$booking = $this->_coreRegistry->registry('bk_booking_data');
		//get id item when edit cart
		$params = $this->getRequest()->getParams();
		$itemId = 0;
		$action = $this->getRequest()->getActionName();
		$intervals = array();
		$qty = 1;
		
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
			$checkIn = '';
			$tempCheckIn = '';
			if(isset($params['check-in']))
			{
				//if page search
				$checkIn = date($formatDate,strtotime($params['check-in']));
				$tempCheckIn = $params['check-in'];
			}
			$request = array(
				'check_in'=>$checkIn,
				'temp_check_in'=>$tempCheckIn,
				'qty'=>$qty,
				'intervals_hours'=>$intervals
			);
		}
		$request['item_id'] = $itemId;
		$request['action'] = $action;
		return $request;
	}
	function getBookingResult()
	{
		//get new object search
		$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		$timeCurrent = $this->getBkTmpTime();
		$checkIn = '';
		$checkOut = '';
		$params = $this->getRequest()->getParams();
		$bookingId = $params['booking_id'];
		$bookingModel = $this->_bookingFactory->create();
		$booking = $bookingModel->getBooking($bookingId);
		$itemId = 0;
		$intervalsHours = array();
		$qty = 1;
		$arrayUpdateParam = array(
					'format_date'=>$formatDate,
					'booking_id'=>$bookingId,
					'product_price'=>0,
					'product_special_price'=>0,
					'quantity'=>$qty
				);
		if(count($params))
		{
			if(isset($params['itemId']))
			{
				$itemId = (int)$params['itemId'];
			}
			if(isset($params['check_in']) && trim($params['check_in']) != '')
			{
				if($this->_bkHelperDate->validateBkDate($params['check_in'],$formatDate))
				{
					$checkIn = $this->_bkHelperDate->convertFormatDate($params['check_in']);
				}
			}
			$checkOut = $checkIn;
			if(isset($params['intervals_hours']))
			{
				$intervalsHours = $params['intervals_hours'];
			}
			$qty = (int)$params['qty'] > 1 ? $params['qty'] : 1;
		}
		$arPrices = array();
		//get qty of intervals 
		if($checkIn == '' || !count($intervalsHours))
		{
			$arPrices['str_error'] = __('Hour interval is not available, Please check again');
		}
		else
		{
			
			$paramAddons = isset($params['addons']) ? $params['addons'] : array();
			$arPrices = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$itemId,$paramAddons);
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
		}
		return $arPrices;
	}
	/*
	* get List interverval
	*/
	function getListIntervals()
	{
		$bookingId = $this->getRequest()->getParam('booking_id',0);
		$strDay = $this->getRequest()->getParam('str_day',0);
		$itemId = $this->getRequest()->getParam('itemId',0);
		//get interval of day
		$intervalModel = $this->_intervalhoursFactory->create();
        $intervals = array();
        $priceOfDay = $this->_rentPrice->getPriceOfDay($bookingId,$strDay,'per_day');
        if(isset($priceOfDay['price']) && $priceOfDay['price'] > 0)
        {
            $intervals = $intervalModel->getIntervals($bookingId,$strDay);
        }
        $typeTimeModel = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/time_mode');
		//get Item in cart
		$cartItem =  $this->_bkOrderHelper->getCurrentIntervalItemIncart($bookingId,$itemId);
		$arrayData = array();
		$status = false;
		//It is update quantity again
		$showTimeFinish = false;
		$showQtyAvaliable = false;
		$bookingModel = $this->_bookingFactory->create();
		$booking = $bookingModel->getBooking($bookingId);
		$strError = __('Item not found');
		$disableDays = trim($booking->getDisableDays()); 
		$numberDayOfWeek = date('w',strtotime($strDay));
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
		if(in_array($numberDayOfWeek,$disableDays))
		{
			$intervals = array();
			$strError = __('Dates are not available. System is closed %1. ',$strDisableTextDays);
		}
		if($booking && $booking->getId())
		{
			if($booking->getBookingShowFinish() == 1)
			{
				$showTimeFinish = true;
			}
			if($booking->getBookingShowQty() == 1)
			{
				$showQtyAvaliable = true;
			}
		}
		if(count($intervals))
		{
			$status = true;
			$i = 0;
			//get time from core
			$intBkTmpTimne = $this->getBkTmpTime();
			$currtime = date('H:i:s',$intBkTmpTimne);
			$dateCurrent = date('Y-m-d',$intBkTmpTimne);
			$intCurrtime = strtotime($currtime);
			//check if default
            $calendarModel = $this->_calendarFactory->create();
            $calendarCollection = $calendarModel->getCollection()
                    ->addFieldToFilter('calendar_startdate',array('lteq'=>$strDay))
                    ->addFieldToFilter('calendar_enddate',array('gteq'=>$strDay));
            $okDefault = true;
            if(count($calendarCollection))
            {
                $firstItem = $calendarCollection->getFirstItem();
                if($firstItem && $firstItem->getId())
                {
                    $okDefault = false;
                }
            }
          /*  echo '<pre>';
                print_r($intervals);
            echo '</pre>';*/
			foreach($intervals as $interval)
			{
			    $enableDays = $interval['intervalhours_days'];
			    if(strpos($enableDays,$numberDayOfWeek) === false)
                {
                    continue;
                }
				$interQty = $interval['intervalhours_quantity'];
                $sessionLabel = $interval['intervalhours_price'] == 0 ? __('Free') : '';
 				$sessionLabel = $interval['intervalhours_label'] != '' ? $interval['intervalhours_label'] : $sessionLabel;
				$intervalsHours = $interval['intervalhours_id'];
				if($okDefault)
                {
                    if($interval['intervalhours_check_in'] != '')
                    {
                        continue;
                    }
                }
				//get quantity from order
				$interOrdertotal = $this->_bkOrderHelper->getOrderIntervalsTotal($bookingId,$strDay,$intervalsHours);
				//get total qty in $cart
				$totalQtyInCart = $this->_bkOrderHelper->getTotalInterItemInCart($bookingId,$strDay,$intervalsHours,$itemId);
				$interQty = $interQty - ($interOrdertotal + $totalQtyInCart);
				$arIntervals = explode('_',$interval['intervalhours_booking_time']);
				$textType1 = __('AM');
				$textType2 = __('AM');
				$tempIntHoursStart = strtotime("{$arIntervals[0]}:{$arIntervals[1]}:00");
				if($strDay == $dateCurrent && $tempIntHoursStart < $intCurrtime)
				{
					continue;
				}
				if($typeTimeModel == 1)
                {
                    if($arIntervals[0] >= 12)
                    {
                        $arIntervals[0] = $arIntervals[0] > 12 ? $arIntervals[0] - 12 : $arIntervals[0];
                        $textType1 = __('PM');
                    }
                    if($arIntervals[2] >= 12)
                    {
                        $arIntervals[2] = $arIntervals[2] > 12 ? $arIntervals[2] - 12 : $arIntervals[2];
                        $textType2 = __('PM');
                    }
                }
				$arrayData[$i]['quantity'] = $interQty;
				$arrayData[$i]['interval_id'] = $interval['intervalhours_id'];
				$arrayData[$i]['time_key'] = $interval['intervalhours_booking_time'];
				if($typeTimeModel == 1)
                {
                    $arrayData[$i]['time_text'] = $arIntervals[0]. ':'.$arIntervals[1]. ' '.$textType1;
                }
				else
                {
                    $arrayData[$i]['time_text'] = $arIntervals[0]. ':'.$arIntervals[1];
                }

				if($showTimeFinish)
				{
				    if($typeTimeModel == 1)
                    {
                        $arrayData[$i]['time_text'] = $arIntervals[0]. ':'.$arIntervals[1]. ' '.$textType1.' - '.$arIntervals[2]. ':'.$arIntervals[3]. ' '.$textType2;
                    }
                    else
                    {
                        $arrayData[$i]['time_text'] = $arIntervals[0]. ':'.$arIntervals[1]. ' - '.$arIntervals[2]. ':'.$arIntervals[3];
                    }

				}
				$arrayData[$i]['class'] = 'item-interval-block';
                $arrayData[$i]['time_text'] .= $sessionLabel != '' ? ' '.$sessionLabel : '';
				if($interQty > 0)
				{
					$arrayData[$i]['class'] = 'item-interval-available';
					if($showQtyAvaliable)
					{
						$arrayData[$i]['time_text'] .= ' | '. $interQty. ' '. __('Available');
					}
				}  else {
					$arrayData[$i]['time_text'] .= ' | '. __('Booked');
					
				}
				$arrayData[$i]['inter_checked'] = '';
				if(count($cartItem))
				{
					if($cartItem['check_in'] == $strDay && in_array($interval['intervalhours_id'],$cartItem['hour_intervals']))
					{
						$arrayData[$i]['inter_checked'] = 'checked="checked"';
						$arrayData[$i]['class'] .= ' item-interval-active';
					}
				}
				
				$i++;
			}
		}
		return array('data'=>$arrayData, 'error'=>$strError);
	}
	/**
	* get Ajax Url
	**/
	function getBkUrlAjax($bookingId,$itemId = 0)
	{
		$urlCalendar = $this->getUrl('bookingsystem/booking/calendarIntervals/booking_id/'.$bookingId);
		$urlBooking =  $this->getUrl('bookingsystem/booking/bookingIntervals/booking_id/'.$bookingId);
		$urlIntervals = $this->getUrl('bookingsystem/booking/intervals/booking_id/'.$bookingId);
		if($itemId > 0)
		{
			$urlCalendar .= 'itemId/'.$itemId;
			$urlBooking .= 'itemId/'.$itemId;
			$urlIntervals .= 'itemId/'.$itemId;
		}
		
		$urlCalendar = $this->getBkHelperDate()->formatUrlPro($urlCalendar);
		$urlBooking = $this->getBkHelperDate()->formatUrlPro($urlBooking);
		$urlIntervals = $this->getBkHelperDate()->formatUrlPro($urlIntervals);
		$arrayUrl = array(
			'url_calendar'=>$urlCalendar,
			'url_booking'=>$urlBooking,
			'url_intervals'=>$urlIntervals,
			);
		return $arrayUrl;
	}
	function getBookingItenrUrl($bookingId,$itemId = 0)
	{
		$urlBooking =  $this->getUrl('bookingsystem/booking/bookingIntervals/booking_id/'.$bookingId);
		if($itemId > 0)
		{
			$urlBooking .= 'itemId/'.$itemId;
		}
		$urlBooking = $this->getBkHelperDate()->formatUrlPro($urlBooking);
		return $urlBooking;
	}
	function getBookedItemInCart($bookingId)
    {
        $itemId = 0;
        $action = $this->getRequest()->getActionName();
        // if edit cart
        if($action == 'configure')
        {
            $itemId = isset($params['id']) ? $params['id'] : 0;
        }
        return $this->_bkOrderHelper->getArrayIntervalItemIncart($bookingId,$itemId);
    }
    /*
     * get current available day
     * */
    function getCurrnetBkCalendars($bookingId,$bookingType = 'per_day')
    {
        $arrayseletct = array('calendar_startdate','calendar_enddate','calendar_qty','calendar_default_value','calendar_status');
        $conditions = array('calendar_booking_type'=>$bookingType);
        $calendars = $this->_calendars->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions);
        $arCalendars = array();
        $intervalsModel = $this->_intervalhoursFactory->create();
        if(count($calendars))
        {
            foreach ($calendars as $key => $calendar)
            {
                $arCalendars[$key] = $calendar->getData();
                $intervals = $intervalsModel->getIntervals($bookingId);
                if(count($intervals))
                {
                    $tmpQty = 0;
                    foreach ($intervals as $interval)
                    {
                        $tmpQty += $interval['intervalhours_quantity'];
                    }
                    $arCalendars[$key]['calendar_qty'] = $tmpQty;
                }
            }
        }
        return $arCalendars;
    }
    function  getAllUnavailableDays($bookingId, $roomId = 0)
    {
        $itemId = 0;
        $action = $this->getRequest()->getActionName();
        if($action == 'configure')
        {
            $itemId = $this->getRequest()->getParam('id',0);
        }
        $arOrders = $this->_bkOrderHelper->getArrayIntervalsInOrders($bookingId);
        $cartItems = $this->_bkOrderHelper->getArrayIntervalItemIncart($bookingId,$itemId);
        $newArCartItems = array();
        $j = 0;
        foreach($cartItems as $cartItem)
        {
            $tempQty = 0;
            if(count($cartItem['hour_intervals']))
            {
                foreach($cartItem['hour_intervals'] as $tempInter)
                {
                    $tempQty += $cartItem['qty'];
                }
            }
            $newArCartItems[$j]['check_in'] = $cartItem['check_in'];
            $newArCartItems[$j]['check_out'] =  $cartItem['check_in'];
            $newArCartItems[$j]['qty'] = $tempQty;
            $j++;
        }
        $arOrders = array_merge($arOrders,$newArCartItems);
        return $arOrders;
    }
}