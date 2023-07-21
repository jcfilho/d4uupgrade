<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Order\Create;

class Intervals extends \Magebay\Bookingsystem\Block\Adminhtml\Order\Create
{
	
	 protected $_intervalhoursFactory;
	 /**
     * @var \Magebay\Bookingsystem\Helper\IntervalsPrice;
     */
	protected $_intervalsPrice; 
	/**
     * @var Magebay\Bookingsystem\Helper\BkOrderHelper;
     */
	protected $_bkOrderHelper;
	function __construct(
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Backend\Block\Widget\Context $context,
		\Magebay\Bookingsystem\Model\BookingsFactory $BookingsFactory,
		\Magebay\Bookingsystem\Helper\BkHelperDate $bkHelperDate,
		\Magebay\Bookingsystem\Model\OptionsFactory $optionsFactory,
		\Magento\Framework\Stdlib\DateTime\Timezone $timezone,
		\Magebay\Bookingsystem\Helper\BkSimplePriceHelper $bkSimplePriceHelper,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Directory\Model\Currency $currency,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
		\Magento\Backend\Model\Auth\Session $bkSession,
		\Magebay\Bookingsystem\Helper\BkText $bkText,
		\Magebay\Bookingsystem\Model\OptionsdropdownFactory $optionsdropdownFactory,
		\Magento\Quote\Model\Quote\Item $quoteItem,
		\Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
		\Magebay\Bookingsystem\Helper\RentPrice $rentPrice,
		\Magento\Backend\Model\Session\Quote $quoteSession,
		\Magebay\Bookingsystem\Model\IntervalhoursFactory $intervalhoursFactory,
		\Magebay\Bookingsystem\Helper\IntervalsPrice $intervalsPrice,
		\Magebay\Bookingsystem\Helper\BkOrderHelper $bkOrderHelper,
		\Magebay\Bookingsystem\Model\DiscountsFactory $discountsFactory,
		\Magebay\Bookingsystem\Model\FacilitiesFactory $facilitiesFactory,
		\Magebay\Bookingsystem\Model\Image $imageModel,
		array $data = []
	)
	{
		$this->_intervalhoursFactory = $intervalhoursFactory;
		$this->_intervalsPrice = $intervalsPrice;
		$this->_bkOrderHelper = $bkOrderHelper;
		parent::__construct(
			$coreRegistry,
			$context, 
			$BookingsFactory,
			$bkHelperDate,
			$optionsFactory,
			$timezone,
			$bkSimplePriceHelper,
			$priceHelper,
			$currency,
			$summaryFactory,
			$bkSession,
			$bkText,
			$optionsdropdownFactory,
			$quoteItem,
			$itemOptionFactory,
			$rentPrice,
			$quoteSession,
			$discountsFactory,
			$facilitiesFactory,
			$imageModel,
			$data
			);
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
		$bookingModel = $this->_bookingsFactory->create();
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
			$oldOrderItemId  = isset($params['bk_order_item_id']) ? $params['bk_order_item_id'] : 0;
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
			$arPrices = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$itemId,$paramAddons,$oldOrderItemId);
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
		$oldOrderItemId = $this->getRequest()->getParam('bk_order_item_id',0);
		//get interval of day
		$intervalModel = $this->_intervalhoursFactory->create();
		$intervals = $intervalModel->getIntervals($bookingId,$strDay);
		$isbackend = false;
		if($this->getBkSession()->isLoggedIn())
		{
			$isbackend = true;
		}
		//get Item in cart
		// $cartItem =  $this->_bkOrderHelper->getCurrentIntervalItemIncart($bookingId,$itemId,$isbackend);
		$currentIntervals = $this->getRequest()->getParam('str_current_intervals');
		$currentCheckIn = $this->getRequest()->getParam('current_check_in');
		$tempQuoteItems = array();
		if(trim($currentIntervals) != '')
		{
			$tempQuoteItems = explode(',',$currentIntervals);
		}
		$arrayData = array();
		$status = false;
		//It is update quantity again
		$showTimeFinish = false;
		$showQtyAvaliable = false;
		$bookingModel = $this->_bookingsFactory->create();
		$booking = $bookingModel->getBooking($bookingId);
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
			foreach($intervals as $interval)
			{
				$interQty = $interval['intervalhours_quantity'];
				$intervalsHours = $interval['intervalhours_booking_time'];
				//get quantity from order
				$interOrdertotal = $this->_bkOrderHelper->getOrderIntervalsTotal($bookingId,$strDay,$intervalsHours,$oldOrderItemId);
				//get total qty in $cart
				$totalQtyInCart = $this->_bkOrderHelper->getTotalInterItemInCart($bookingId,$strDay,$intervalsHours,$itemId,$isbackend);
				$interQty = $interQty - ($interOrdertotal + $totalQtyInCart);
				$arIntervals = explode('_',$interval['intervalhours_booking_time']);
				$textType1 = __('AM');
				$textType2 = __('AM');
				$tempIntHoursStart = strtotime("{$arIntervals[0]}:{$arIntervals[1]}:00");
				if($strDay == $dateCurrent && $tempIntHoursStart < $intCurrtime)
				{
					continue;
				}
				if($arIntervals[0] > 12)
				{
					$arIntervals[0] = $arIntervals[0] - 12;
					$textType1 = __('PM');
				}
				if($arIntervals[2] > 12)
				{
					$arIntervals[2] = $arIntervals[2] - 12;
					$textType2 = __('PM');
				}
				$arrayData[$i]['quantity'] = $interQty;
				$arrayData[$i]['time_key'] = $interval['intervalhours_booking_time'];
				$arrayData[$i]['time_text'] = $arIntervals[0]. ':'.$arIntervals[1]. ' '.$textType1;
				if($showTimeFinish)
				{
					$arrayData[$i]['time_text'] = $arIntervals[0]. ':'.$arIntervals[1]. ' '.$textType1.' - '.$arIntervals[2]. ':'.$arIntervals[3]. ' '.$textType2;
				}
				$arrayData[$i]['class'] = 'item-interval-block';
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
				if(count($tempQuoteItems))
				{
					if($currentCheckIn == $strDay && in_array($interval['intervalhours_booking_time'],$tempQuoteItems))
					{
						$arrayData[$i]['inter_checked'] = 'checked="checked"';
						$arrayData[$i]['class'] .= ' item-interval-active';
					}
				}
				
				$i++;
			}
		}
		return $arrayData;
	}
	/**
	* get Ajax Url
	**/
	function getBkUrlAjax($bookingId,$itemId = 0,$oldOrderItemId = 0)
	{
		$urlCalendar = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/calendarIntervals/booking_id/',array('booking_id'=>$bookingId));
		$urlBooking = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/bookingIntervals/booking_id/',array('booking_id'=>$bookingId));
		$urlIntervals = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/intervals/booking_id/',array('booking_id'=>$bookingId));
		if($itemId > 0)
		{
			$urlCalendar = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/calendarIntervals/booking_id/',array('booking_id'=>$bookingId,'itemId'=>$itemId,'oldOrderItemId'=>$oldOrderItemId));
			$urlBooking = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/bookingIntervals/booking_id/',array('booking_id'=>$bookingId,'itemId'=>$itemId));
			$urlIntervals = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/intervals/booking_id/',array('booking_id'=>$bookingId,'itemId'=>$itemId));
		}
		$arrayUrl = array(
			'url_calendar'=>$urlCalendar,
			'url_booking'=>$urlBooking,
			'url_intervals'=>$urlIntervals,
			);
		return $arrayUrl;
	}
	function getBookingItenrUrl($bookingId,$itemId = 0)
	{
		$urlBooking =  $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/bookingIntervals/booking_id/',array('booking_id'=>$bookingId));
		if($itemId > 0)
		{
			$urlBooking =  $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/bookingIntervals/booking_id/',array('booking_id'=>$bookingId,'itemId'=>$itemId));
		}
		$urlBooking = $this->getBkHelperDate()->formatUrlPro($urlBooking);
		return $urlBooking;
	}
	function getBkItalvalsRequestOption()
	{
		$itemId = $this->getRequest()->getParam('itemId',0);
		$arRequestOptions = array();
		if($itemId > 0)
		{
			$itemOptionModel = $this->_itemOptionFactory->create();
			$collection = $itemOptionModel->getCollection()
				->addFieldToFilter('item_id',$itemId)
				->addFieldToFilter('code','info_buyRequest');
			
			if(count($collection))
			{
				$itemoption = $collection->getFirstItem();
				$arRequestOptions = unserialize($itemoption->getValue());
			}
		}
		return $arRequestOptions;
	}
}