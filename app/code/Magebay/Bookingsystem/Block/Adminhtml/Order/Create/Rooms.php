<?php

namespace Magebay\Bookingsystem\Block\Adminhtml\Order\Create;

class Rooms extends \Magebay\Bookingsystem\Block\Adminhtml\Order\Create
{
	protected $_roomTypes;
	protected $_roomsFactory;
	protected $_bookingimagesFactory;
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
		\Magebay\Bookingsystem\Model\DiscountsFactory $discountsFactory,
		\Magebay\Bookingsystem\Model\FacilitiesFactory $facilitiesFactory,
		\Magebay\Bookingsystem\Model\RoomtypesFactory $roomTypes,
		\Magebay\Bookingsystem\Model\RoomsFactory $roomsFactory,
		\Magebay\Bookingsystem\Model\BookingimagesFactory $bookingimagesFactory,
		\Magebay\Bookingsystem\Model\Image $imageModel,
		\Magebay\Bookingsystem\Helper\RoomPrice $roomPrice,
		array $data = []
	)
	{
		$this->_roomTypes = $roomTypes;
		$this->_roomsFactory = $roomsFactory;
		$this->_bookingimagesFactory = $bookingimagesFactory;
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
	/**
	* get room for popup
	**/
	function getBkRoom()
	{
		$roomId = $this->getRequest()->getParam('room_id',0);
		$roomModel = $this->_roomsFactory->create();
		return $roomModel->load($roomId);
	}
	/**
	* get product to popup
	**/
	function getRoomBkProduct($productId)
	{
		$bookingModel = $this->_bookingsFactory->create();
		return $bookingModel->getBooking($productId);
	}
	/**
	* get List rooms for search
	**/
	function getBkRooms($hotelId = 0)
	{
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$timeCurrent = $this->getBkTmpTime();
		$params = $this->getRequest()->getParams();
		$checkIn = date('Y-m-d',$timeCurrent);
		$checkOut = date('Y-m-d',($timeCurrent + 24 * 60 * 60));
		if($hotelId == 0)
		{
			$hotelId = isset($params['bk_hotel_id']) ? $params['bk_hotel_id'] : 0;
		}
		$roomType = isset($params['room_type']) ? $params['room_type'] : 0;
		$maxAdult = isset($params['max_adults']) ? $params['max_adults'] : 0;
		$maxChild = isset($params['max_children']) ? $params['max_children'] : 0;
		$itemId = isset($params['bk_item_id']) ? $params['bk_item_id'] : 0;
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
		//get rooms
		$arDataRoom = array();
		if($hotelId > 0)
		{
			$roomModel = $this->_roomsFactory->create();
			$collection = $roomModel->getBkRoomsById($hotelId)
					->addFieldToFilter('room_status',1);
			if($roomType > 0)
			{
				$collection->addFieldToFilter('room_type',$roomType);
			}
			if($maxAdult > 0)
			{
				$collection->addFieldToFilter('room_max_adults',$maxAdult);
			}
			if($maxChild > 0)
			{
				$collection->addFieldToFilter('room_max_children',$maxChild);
			}	
			$rooms = array();
			$oldOrderItemId  = isset($params['bk_order_item_id']) ? $params['bk_order_item_id'] : 0;
			if(count($collection))
			{
				foreach($collection as $key => $collect)
				{
					$arPrice = $this->_roomPrice->getPriceBetweenDays($collect,$checkIn,$checkOut,1,$itemId,array(),$oldOrderItemId);
					if($arPrice['str_error'] != '')
					{
						continue;
					}
					//sort order
					if(count($rooms))
					{
						$tempCount = count($rooms);
						$lastItem = $rooms[$tempCount - 1];
						$tempLastItemPrice = $lastItem['room_price']['total_promo'] > 0 ? $lastItem['room_price']['total_promo'] : $lastItem['room_price']['total_price'];
						$tempPrice = $arPrice['total_promo'] > 0 ? $arPrice['total_promo'] : $arPrice['total_price'];
						//move item
						
						if($tempPrice < $tempLastItemPrice)
						{
							$rooms[$tempCount - 1]['room_data'] = $collect;
							$rooms[$tempCount - 1]['room_price'] = $arPrice;
							$rooms[$tempCount] = $lastItem;
						}
						else
						{
							$rooms[$tempCount]['room_data'] = $collect;
							$rooms[$tempCount]['room_price'] = $arPrice;
						}
					}
					else
					{
						$tempFirestRoom['room_data'] = $collect;
						$tempFirestRoom['room_price'] = $arPrice;
						$rooms[] = $tempFirestRoom;
					}
				}
			}
		}
		return $rooms;
	}	
	/**
	* get room request options when search
	**/
	function getBkRoomRequestOptions($itemId)
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
	/**
	* get calculating for room
	**/
	function getBookingRoomResults()
	{
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$params = $this->getRequest()->getParams();
		$roomId = (int)$params['room_id'];
		$checkIn = '';
		$checkOut = '';
		$arPrices = array();
		$itemId = isset($params['itemId']) ? $params['itemId'] : 0;
		$oldOrderItemId  = isset($params['bk_order_item_id']) ? $params['bk_order_item_id'] : 0;
		if($this->getBkHelperDate()->validateBkDate($params['room_check_in'],$formatDate))
		{
			$checkIn = $this->getBkHelperDate()->convertFormatDate($params['room_check_in'],$formatDate);
		}
		if($this->getBkHelperDate()->validateBkDate($params['room_check_out'],$formatDate))
		{
			$checkOut = $this->getBkHelperDate()->convertFormatDate($params['room_check_out'],$formatDate);
		}
		if($roomId > 0 && $checkIn != '' && $checkOut != '')
		{
			$qty = $params['room_qty'];
			$roomModel = $this->_roomsFactory->create();
			$room = $roomModel->load($roomId);
			$bookingId = $room->getRoomBookingId();
			$paramAddons = isset($params['addons']) ? $params['addons'] : array();
			$arPrices = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,$itemId,$paramAddons,$oldOrderItemId);
			$arPrices['room_check_out'] = date($formatDate,strtotime($checkOut));
			$arPrices['temp_room_check_out'] = $checkOut;
			$arPrices['room_id'] = $roomId;
			$arPrices['booking_id'] = $bookingId;
			$bookingModel = $this->_bookingsFactory->create();
			$booking = $bookingModel->getBooking($bookingId);
			$useDefaultPrice = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/default_price');
			if($useDefaultPrice == 1 && $booking && $booking->getId())
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
		}
		else
		{
			$arPrices['str_error'] = __('Date format is not correct. Please check again');
		}
		return $arPrices;
	}
	/**
	* get list ajax url
	**/
	function getRoomAjaxUrl($hotelId,$roomId = 0,$itemId = 0,$oldOrderItemId = 0)
	{
		$urlSearch = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/searchRooms',array('hotel_id'=>$hotelId));
		$urlRoomDetail = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/viewRoom/',array('hotel_id'=>$hotelId));
		$urlLoadCalendar = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/loadCalendar/',array('hotel-id'=>$hotelId,'booking_id'=>$roomId,'oldOrderItemId'=>$oldOrderItemId));
		$urlBookRoom = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/bookingRoom',array('room_id'=>$roomId));
		if($itemId > 0)
		{
			$urlSearch = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/searchRooms',array('hotel_id'=>$hotelId,'itemId'=>$itemId));
			$urlRoomDetail = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/viewRoom/',array('hotel_id'=>$hotelId,'itemId'=>$itemId));
			$urlLoadCalendar = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/loadCalendar/',array('hotel-id'=>$hotelId,'booking_id'=>$roomId,'itemId'=>$itemId,'oldOrderItemId'=>$oldOrderItemId));
			$urlBookRoom = $this->_bkHelperDate->getBkAdminAjaxUrl('bookingsystem/createorder/bookingRoom',array('room_id'=>$roomId,'itemId'=>$itemId));
		}
		return array(
			'url_search_room'=>$urlSearch,
			'url_room_detail'=>$urlRoomDetail,
			'url_calendar'=>$urlLoadCalendar,
			'url_room_booking'=>$urlBookRoom,
		);
	}
	/**
	* get addons Selles 
	* @return array $itens
	**/
	function getRoomAddonsSelles($rooomId)
	{
		$model = $this->_optionsFactory->create();
		$collection = $model->getBkOptions($rooomId,'hotel');
		return $collection;
	}
	function getBkRoomDiscounts($rooomId)
	{
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$model = $this->_discountsFactory->create();
		$intToday = $this->_timezone->scopeTimeStamp();
		$symbol = $this->_currency->getCurrencySymbol();
		$collection = $model->getBkDiscountItems($rooomId,$formatDate,$intToday,$symbol,'hotel');
		return $collection;
	}
	/**
	* get Image For room
	* @params int $roomId
	**/
	function getBkRoomImages($roomId)
	{
		$imageModel = $this->_bookingimagesFactory->create();
		$collection = $imageModel->getCollection()
				->addFieldToFilter('bkimage_type','room')
				->addFieldToFilter('bkimage_data_id',$roomId);
		return $collection;
	}
	/**
	* get room types for options search
	**/
	function getBkRoomTypes()
	{
		$roomTypeModel = $this->_roomTypes->create();
		$collection = $roomTypeModel->getCollection()
				->addFieldToFilter('roomtype_status',1);
		return $collection;
	}
	function getBkRoomTypeTitle()
	{
		$data = array();
		$roomTypes = $this->getBkRoomTypes();
		if(count($roomTypes))
		{
			foreach($roomTypes as $roomType)
			{
				$roomTitle = $this->_bkText->showTranslateText($roomType->getRoomtypeTitle(),$roomType->getRoomtypeTitleTransalte());
				$data[$roomType->getId()] = $roomTitle;
			}
		}
		return $data;
	}
}