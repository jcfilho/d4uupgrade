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
use Magebay\Bookingsystem\Model\RoomtypesFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\BookingimagesFactory;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Model\Image as ImageModel;
use Magebay\Bookingsystem\Helper\RoomPrice;

class Rooms extends \Magebay\Bookingsystem\Block\Booking
{
	protected $_roomTypes;
	protected $_bkText;
	protected $_roomsFactory;
	protected $_bookingimagesFactory;
	protected $_imageModel;
	protected $_roomPrice;
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
		RoomtypesFactory $roomtypesFactory,
		RoomsFactory $roomsFactory,
		BookingimagesFactory $bookingimagesFactory,
		BkText $bkText,
		ImageModel $imageModel,
        \Magebay\Bookingsystem\Model\Calendars $calendars,
		RoomPrice $roomPrice,
		array $data = []
	) 
	{
		$this->_roomTypes = $roomtypesFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_bookingimagesFactory = $bookingimagesFactory;
		$this->_roomPrice = $roomPrice;
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
		$bookingModel = $this->_bookingFactory->create();
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
		$rooms = array();
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
			
			
			if(count($collection))
			{
				foreach($collection as $key => $collect)
				{
					$arPrice = $this->_roomPrice->getPriceBetweenDays($collect,$checkIn,$checkOut,1,$itemId,array());
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
	/*
	 * Get all room in hotel
	 * */
	function  getAllRooms($hotelId)
    {
        $roomModel = $this->_roomsFactory->create();
        $collection = $roomModel->getBkRoomsById($hotelId)
            ->addFieldToFilter('room_status',1);
        return $collection;
    }
	/**
	* get request @params
	**/
	function getBookingRoomRequest()
	{
		$formatDate = $this->getBkHelperDate()->getFieldSetting('bookingsystem/setting/format_date');
		$params = $this->getRequest()->getParams();
		$bookingAction = 'book';
		$itemId  = 0;
		$booking = $this->getBkBookingItem();
		$action = $this->getRequest()->getActionName();
		$bkTmpTime = $this->getBkTmpTime();
		if($action == 'configure')
		{
			$itemId = isset($params['id']) ? $params['id'] : 0;
			if($itemId > 0)
			{
				$request = $this->_bkOrderHelper->getBkRequestItemOption($itemId,$booking->getId());
			}
		}
		else
		{
			$params['check_in'] = date($formatDate,$bkTmpTime);
			$params['check_out'] = date($formatDate,($bkTmpTime + 24 * 60 * 60));
			if(isset($params['check-in']) && trim($params['check-in']) != '')
			{
				if($this->getBkHelperDate()->validateBkDate($params['check-in'],'Y-m-d'))
				{
					$checkIn = date($formatDate,strtotime($params['check-in']));
					$params['check_in'] = $checkIn;
				}
			}
			if(isset($params['check-out']) && trim($params['check-out']) != '')
			{
				if($this->getBkHelperDate()->validateBkDate($params['check-out'],'Y-m-d'))
				{
					$checkOut = date($formatDate,strtotime($params['check-out']));
					$params['check_out'] = $checkOut;
				}
			}
			$roomType = isset($params['room-type']) ? $params['room-type'] : 0;
			$request  = array(
				'check_in'=>$params['check_in'],
				'check_out'=>$params['check_out'],
				'room_type'=>$roomType,
				'max_adults'=>0,
				'max_children'=>0
			);
		}
		if(!isset($request['check_in']))
		{
			$request['check_in'] = date($formatDate,$bkTmpTime);
			$bkTmpTime += 24 * 60 * 60;
			$request['check_out'] = date($formatDate,$bkTmpTime);
		}
		$request['action'] = $action;
		$request['item_id'] = $itemId;
		$request['bk_hotel_id'] = $booking->getId();
		return $request;
	}
	/**
	* get room request options when search
	**/
	function getBkRoomRequestOptions($itemId,$productId)
	{
		$requestOptions = $this->_bkOrderHelper->getBkRequestItemOption($itemId,$productId);
		return $requestOptions;
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
			$qty = $params['qty'];
			$roomModel = $this->_roomsFactory->create();
			$room = $roomModel->load($roomId);
			$bookingId = $room->getRoomBookingId();
			$paramAddons = isset($params['addons']) ? $params['addons'] : array();
			$arPrices = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,$itemId,$paramAddons);
			$arPrices['room_check_out'] = date($formatDate,strtotime($checkOut));
			$arPrices['temp_room_check_out'] = $checkOut;
			$bookingModel = $this->_bookingFactory->create();
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
	function getRoomAjaxUrl($hotelId,$roomId = 0,$itemId = 0)
	{
		$urlSearch = $this->getUrl('bookingsystem/booking/searchRooms/hotel_id/'.$hotelId);
		$urlRoomDetail = $this->getUrl('bookingsystem/booking/viewRoom/hotel_id/'.$hotelId);
		$urlLoadCalendar = $this->getUrl('bookingsystem/booking/loadCalendar/booking_id/'.$roomId.'/hotel-id/'.$hotelId);
		$urlBookRoom = $this->getUrl('bookingsystem/booking/bookingRoom/room_id/'.$roomId);
		if($itemId > 0)
		{
			$urlSearch .= 'itemId/'.$itemId;
			$urlRoomDetail .= 'itemId/'.$itemId;
			$urlLoadCalendar .= 'itemId/'.$itemId;
			$urlBookRoom .= 'itemId/'.$itemId;
		}
		$urlRoomDetail = $this->getBkHelperDate()->formatUrlPro($urlRoomDetail);
		$urlSearch = $this->getBkHelperDate()->formatUrlPro($urlSearch);
		$urlLoadCalendar = $this->getBkHelperDate()->formatUrlPro($urlLoadCalendar);
		$urlBookRoom = $this->getBkHelperDate()->formatUrlPro($urlBookRoom);
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