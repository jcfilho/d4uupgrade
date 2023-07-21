<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Order\Create;

class LoadGrid extends \Magebay\Bookingsystem\Block\Adminhtml\Order\Create
{
	/**
     * @var \Magebay\Bookingsystem\Helper\IntervalsPrice;
     */
	protected $_intervalsPrice; 
	protected $_roomsFactory; 
	protected $_roomPrice; 
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
		\Magebay\Bookingsystem\Helper\IntervalsPrice $intervalsPrice,
		\Magebay\Bookingsystem\Model\DiscountsFactory $discountsFactory,
		\Magebay\Bookingsystem\Model\FacilitiesFactory $facilitiesFactory,
		\Magebay\Bookingsystem\Model\Image $imageModel,
		\Magebay\Bookingsystem\Model\RoomsFactory $roomsFactory,
		\Magebay\Bookingsystem\Helper\RoomPrice $roomPrice,
		array $data = []
	)
	{
		$this->_intervalsPrice = $intervalsPrice;
		$this->_roomsFactory = $roomsFactory;
		$this->_roomPrice = $roomPrice;
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
	function updateBkPriceItems()
	{
		$quoteSession = $this->getBkQuoteSession();
		$items = $quoteSession->getQuote()->getAllItems();
		$arItemData = array();
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$checkIn = '';
		$checkOut = '';
		$itemId = 0;
		$qty = 1;
		if(count($items))
		{
			foreach($items as $item)
			{
				$_product = $item->getProduct();
				if($_product->getTypeId() != 'booking')
				{
					continue;
				}
				$params = $item->getBuyRequest()->getData();
				$itemId = $item->getItemId();
				$bookingId = $_product->getId();
				$bookingFactory = $this->_bookingsFactory->create();
				$booking = $bookingFactory->getBooking($bookingId);
				$arPrices = array();
				$oldOrderItemId  = isset($params['bk_order_item_id']) ? $params['bk_order_item_id'] : 0;
				if($booking->getBookingType() == 'per_day')
				{
					if($booking->getBookingTime() == 3)
					{
						$intervalsHours = array();
						if(count($params))
						{
							if(isset($params['check_in']) && trim($params['check_in']) != '')
							{
								if($this->getBkHelperDate()->validateBkDate($params['check_in'],$formatDate))
								{
									$checkIn = $this->getBkHelperDate()->convertFormatDate($params['check_in']);
								}
							}
							$checkOut = $checkIn;
							if(isset($params['intervals_hours']))
							{
								$intervalsHours = $params['intervals_hours'];
							}
						}
						//get qty of intervals 
						if($checkIn == '' || !count($intervalsHours))
						{
							$arPrices['str_error'] = __('Hour interval is not available, Please check again');
						}
						else
						{
							$paramAddons = isset($params['addons']) ? $params['addons'] : array();
							$arPrices = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$itemId,$paramAddons,$oldOrderItemId);
						}
					}
					else
					{
						if(count($params))
						{
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
						if($checkIn == '' || $checkOut == '' || ($checkIn != '' && $checkOut != '' && strtotime($checkOut) < strtotime($checkIn)))
						{
							$arPrices['str_error'] = __('Check in or check out are not available, Please check again');
						}
						elseif($booking && $booking->getId())
						{
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
							
						}
					}
				}
				else
				{
					if($this->getBkHelperDate()->validateBkDate($params['room_check_in'],$formatDate))
					{
						$checkIn = $this->getBkHelperDate()->convertFormatDate($params['room_check_in'],$formatDate);
					}
					if($this->getBkHelperDate()->validateBkDate($params['room_check_out'],$formatDate))
					{
						$checkOut = $this->getBkHelperDate()->convertFormatDate($params['room_check_out'],$formatDate);
					}
					$roomId = (int)$params['room_id'];
					if($roomId > 0 && $checkIn != '' && $checkOut != '')
					{
						$roomModel = $this->_roomsFactory->create();
						$room = $roomModel->load($roomId);
						$bookingId = $room->getRoomBookingId();
						$paramAddons = isset($params['addons']) ? $params['addons'] : array();
						$arPrices = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,$itemId,$paramAddons,$oldOrderItemId);
					}
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
							$arPrices['total_promo'] = $arPrices['total_price'] + $booking->getSpecialPrice();
							$arPrices['total_price'] += $booking->getPrice();
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
				$finalPrice = $arPrices['total_promo'] > 0 ? $arPrices['total_promo'] : $arPrices['total_price'];
				$arItemData[$itemId] = $this->getBkPriceHelper()->currency($finalPrice,false,false);
			}
		}
		return $arItemData;
	}
}