<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Order\Create;

class Booking extends \Magebay\Bookingsystem\Block\Adminhtml\Order\Create
{
	
	protected $_summaryFactory;
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
		\Magento\Quote\Model\Quote\Item\OptionFactory$itemOptionFactory,
		\Magebay\Bookingsystem\Helper\RentPrice $rentPrice,
		\Magento\Backend\Model\Session\Quote $quoteSession,
		\Magebay\Bookingsystem\Model\DiscountsFactory $discountsFactory,
		\Magebay\Bookingsystem\Model\FacilitiesFactory $facilitiesFactory,
		\Magebay\Bookingsystem\Model\Image $imageModel,
		array $data = []
	)
	{
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
	/* get ajax Url For Booking simple */
	function getBkUrlAjax($bookingId,$itemId = 0,$oldOrderItemId = 0)
	{
		$urlCalendar = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/loadCalendar/booking_id/',array('booking_id'=>$bookingId,'itemId'=>$itemId,'oldOrderItemId'=>$oldOrderItemId));
		$urlBooking = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/booking/booking_id/',array('booking_id'=>$bookingId,'itemId'=>$itemId));
		return array(
			'url_calendar'=>$urlCalendar,
			'url_booking'=>$urlBooking
		);
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
		$bookingFactory = $this->_bookingsFactory->create();
		$booking = $bookingFactory->getBooking($bookingId);
		$itemId = 0;
		$quoteSession = $this->getBkQuoteSession();
		$items = $quoteSession->getQuote()->getAllItems();
		$oldOrderItemId = 0;
		if(count($params))
		{
			if(isset($params['itemId']))
			{
				$itemId = (int)$params['itemId'];
			}
			$oldOrderItemId  = isset($params['bk_order_item_id']) ? $params['bk_order_item_id'] : 0;
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
			$arPrices['str_error'] = __('Check in or check out are not available, Please check again');
		}
		elseif($booking && $booking->getId())
		{
			$qty = (int)$params['qty'] > 1 ? $params['qty'] : 1;
			$paramAddons = isset($params['addons']) ? $params['addons'] : array();
			if($booking->getBookingTime() == '1')
			{
				$arPrices = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$qty,$itemId,$paramAddons,$oldOrderItemId);
			}
			else
			{
				//get time
				$fromHour = $params['from_time_t'] == 1 ? $params['from_time_h'] : ($params['from_time_h'] + 12);
				$toHour = $params['to_time_t'] == 1 ? $params['to_time_h'] : ($params['to_time_h'] + 12);
				$arPrices = $this->_bkSimplePriceHelper->getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$params['from_time_m'],$params['to_time_m'],$qty,$itemId,$paramAddons,$oldOrderItemId);
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
		}
		return $arPrices;
	}
	function getBkSimpleRequests($booking)
	{
		$request = array();
		if($booking && $booking->getId() && $booking->getBookingTime() == 2)
		{
				
				$fromTimeH = '';
				$fromTimeM = '';
				$fromTimeT = '';
				$toTimeH = '';
				$toTimeM = '';
				$toTimeT = '';
				if($booking->getBookingServiceStart() != '' && $booking->getBookingServiceEnd() != '')
				{
					$serviceStart = explode(',',$booking->getBookingServiceStart());
					$serviceEnd = explode(',',$booking->getBookingServiceEnd());
					$fromTimeH = $serviceStart[0];
					$fromTimeM = $serviceStart[1];
					$fromTimeT = $serviceStart[2];
					$toTimeH = $serviceEnd[0];
					$toTimeM = $serviceEnd[1];
					$toTimeT = $serviceEnd[2];
				}
				//echo $hourStart;
				$request = array(
					'from_time_h'=>$fromTimeH,
					'from_time_m'=>$fromTimeM,
					'from_time_t'=>$fromTimeT,
					'to_time_h'=>$toTimeH,
					'to_time_m'=>$toTimeM,
					'to_time_t'=>$toTimeT,
				);
		}
		return $request;
	}
}