<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Template;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Backend\Helper\Data as BackendHelper;
use Magento\Sales\Model\Order as BkCoreOrder;
use Magento\Sales\Model\Order\ItemFactory as BkCoreOrderItem;
use Magento\Sales\Model\Order\Address as BkOrderAddess;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Directory\Model\Config\Source\Country;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\RoomtypesFactory;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\Intervalhours;
use Magebay\Bookingsystem\Helper\BkText;


class Dashboard extends Template
{
	/**
     *
     * @var @Magento\Framework\Stdlib\DateTime\Timezone
    */
	protected $_timezone;
	/**
     *
     * @var PriceHelper
    */
	protected $_priceHelper;
	/**
     *
     * @var BackendHelper
    */
	protected $_backendHelper;
	/**
     *
     * @var @Magento\Sales\Model\Order
    */
	protected $_bkCoreOrder;
	/**
     *
     * @var BkCoreOrderItem
    */
	protected $_bkCoreOrderItem;
	/**
     *
     * @var BkOrderAddess
    */
	protected $_bkOrderAddess;
	/**
     *
     * @var @Magento\Framework\Locale\CurrencyInterface
    */
	protected $_currencyInterface;
	/**
     * @param \Magento\Directory\Model\Config\Source\CountryFactory
     * 
     */
	protected $_countryFactory;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\BookingordersFactory
    */
	protected $_bookingordersFactory;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\RoomsFactory
    */
	protected $_roomsFactory;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\RoomtypesFactory
    */
	protected $_roomtypesFactory;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\BookingsFactory
    */
	protected $_bookingsFactory;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\FacilitiesFactory
    */
	protected $_facilitiesFactory;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\Intervalhours
    */
	protected $_intervalhours;
	/**
     *
     * @var @Magebay\Bookingsystem\Helper\BkText
    */
	protected $_bkText;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		Timezone $timezone,
		BackendHelper $backendHelper,
		PriceHelper $priceHelper,
		BkCoreOrder $bkCoreOrder,
		BkCoreOrderItem $bkCoreOrderItem,
		BkOrderAddess $bkOrderAddess,
		CurrencyInterface $currencyInterface,
		Country $countryFactory,
		BookingordersFactory $bookingordersFactory,
		RoomsFactory $roomsFactory,
		RoomtypesFactory $roomtypesFactory,
		BookingsFactory $bookingsFactory,
		FacilitiesFactory $facilitiesFactory,
		Intervalhours $intervalhours,
		BkText $bkText,
		array $data = []
	)
	{
		$this->_timezone = $timezone;
		$this->_priceHelper = $priceHelper;
		$this->_backendHelper = $backendHelper;
		$this->_bkCoreOrder = $bkCoreOrder;
		$this->_bkCoreOrderItem = $bkCoreOrderItem;
		$this->_bkOrderAddess = $bkOrderAddess;
		$this->_currencyInterface = $currencyInterface;
		$this->_countryFactory = $countryFactory;
		$this->_bookingordersFactory = $bookingordersFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_roomtypesFactory = $roomtypesFactory;
		$this->_bookingsFactory = $bookingsFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
		$this->_intervalhours = $intervalhours;
		$this->_bkText = $bkText;
		parent::__construct($context, $data);
	}
	function getMaiuoc()
	{
		$intTmpTime = $this->getBkTmpTime();
		$day = date('w',$intTmpTime);
		$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
		$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));
	}
	function getWeeklyBestellers()
	{
		$storeId = $this->getRequest()->getParam('store',0);
		$intTmpTime = $this->getBkTmpTime();
		$day = date('w',$intTmpTime);
		$week_start = date('Y-m-d', strtotime('-'.$day.' days'));
		$week_end = date('Y-m-d', strtotime('+'.(6-$day).' days'));
		
		$bkOrderModel = $this->_bookingordersFactory->create();
		$collection = $bkOrderModel->getCollection()
				->addFieldToFilter('bkorder_check_out',array('gteq'=>$week_start))
				->addFieldToFilter('bkorder_check_in',array('lteq'=>$week_end));
		$intSatart = strtotime($week_start);
		$intEnd = strtotime($week_start);
		$oneDay = 60*60*24;
		$arData = array();
		$quoteModel = $this->_bkCoreOrderItem->create();
		$arData = array();
		foreach($collection as $collect)
		{
			$quoteId = $collect->getBkorderQtItemId();
			$quoteItem = $quoteModel->load($quoteId);
			$key = $quoteItem->getProductId();
			$title = '';
			if($collect->getBkorderRoomId() == 1)
			{
				$key = $quoteItem->getProductId().'_'.$collect->getBkorderBookingId();
				$roomModel = $this->_roomsFactory->create();
				$room = $roomModel->load($collect->getBkorderBookingId());
				if($room)
				{
					$roomTypesModel = $this->_roomtypesFactory->create();
					$roomType = $roomTypesModel->load($room->getRoomType());
					if($roomType)
					{
						$title = $this->_bkText->showTranslateText($roomType->getRoomtypeTitle(),$roomType->getRoomtypeTitleTransalte(),$storeId);
					}
				}
				
			}
			if(!array_key_exists($key,$arData))
			{
				$arData[$key]['title'] = $quoteItem->getName();
				if($title != '')
				{
					$arData[$key]['title'] = __('%1 of %2',$title,$quoteItem->getName());
				}
				$arData[$key]['price'] = $quoteItem->getBasePrice();
				$arData[$key]['qty'] = $collect->getBkorderQty();
			}
			else
			{
				$arData[$key]['price'] += $quoteItem->getBasePrice();
				$arData[$key]['qty'] += $collect->getBkorderQty();
			}
		}
		return $arData;
	}
	/**
	* get data for calendars
	**/
	function getBookingReport()
	{
		$storeId = $this->getRequest()->getParam('store',0);
		$config = $this->getBookingConfig();
		$formatDate = $this->_bkText->getFieldSetting('bookingsystem/setting/format_date');
		$bkOrderModel = $this->_bookingordersFactory->create();
		$orders = $bkOrderModel->getCollection();
		$arData = array();
		$i = 0;
		$txtBookingId = 0;
		$txtStatus = '';
		$params = $this->getRequest()->getParams();
		$bookingType = 'per_day';
		if(count($params))
		{
			$txtBookingId = isset($params['select_booking_report']) ? $params['select_booking_report'] : 0;
			$txtStatus = isset($params['txt_order_status']) ? trim($params['txt_order_status']) : '';
            $bookingType = isset($params['bk_type']) ? trim($params['bk_type']) : '';
		}
		$roomModel = $this->_roomsFactory->create();
		$roomTypesModel = $this->_roomtypesFactory->create();
		$bookingModel = $this->_bookingsFactory->create();
		foreach($orders as $order)
		{
			$bookingId = $order->getBkorderBookingId();
			$title = '';
			if($txtBookingId > 0)
            {
                if(($order->getBkorderRoomId() == 1 && $bookingType == 'per_day') || ($order->getBkorderRoomId() == 0 && $bookingType == 'hotel'))
                {
                    continue;
                }
            }
			if($order->getBkorderRoomId() == 1)
			{
				if($txtBookingId > 0)
				{
					if($bookingId != $txtBookingId)
						continue;
				}
				$room = $roomModel->load($bookingId);
				if($room)
				{
					if($room->getId())
					{
						$roomType = $roomTypesModel->load($room->getRoomType());
						if($roomType)
						{
							$title = $this->_bkText->showTranslateText($roomType->getRoomtypeTitle(),$roomType->getRoomtypeTitleTransalte(),$storeId);
						}
						$bookingId = $room->getRoomBookingId();
                        $bookingType = 'hotel';
					}
				}
				else
				{
					continue;
				}
				
			}
			else
			{
				if($txtBookingId > 0)
				{
					if($bookingId != $txtBookingId)
						continue;
				}
			}
			$booking = $bookingModel->getBooking($bookingId);
			if($booking && $booking->getId())
			{
			    if($txtBookingId > 0 && $bookingType != $booking->getBookingType())
                {
                    continue;
                }
				if($order->getBkorderRoomId() == 1)
				{
					$title = __('%1 of %2 hotel',$title,$booking->getName());
				}
				else
				{
					$title = $booking->getName();
				}
				//get status for booking order
				$objOrder = $this->_bkCoreOrder->load($order->getBkorderOrderId());
				if($txtStatus != '' && $objOrder->getStatus() != $txtStatus)
					continue;
				$className = 'booking-item-order-'.$objOrder->getStatus();
				if($booking->getBookingType() == 'per_day' && $booking->getBookingTime() == 2)
				{
					 // echo 'ok';
					$serviceStart = $order->getBkorderServiceStart();
                    $serviceEnd = $order->getBkorderServiceEnd();
                    if($serviceStart == '' || $serviceEnd == '')
                    {
                        continue;
                    }
					/*$arStart = explode(':',$serviceStart);
					//$hStart = $arStart[2] == 2 ? ($arStart[0] + 12) : $arStart[0];

					$arEnd = explode(',',$serviceEnd);
					//$hEnd = $arEnd[2] == 2 ? ($arEnd[0] + 12) : $arEnd[0];
					for($j = $hStart; $j <= $hEnd; $j++)
					{
						$textTime = $j < 10 ? '0'.$j : $j;
						$textTime = 'T'.$textTime.':';
						if($j == $hStart)
						{
							$minute = $arStart[1] < 10 ? '0'.$arStart[1] : $arStart[1];
							$textTime .= $minute.':00';
						}
						elseif($j == $hEnd)
						{
							$minute = $arEnd[1] < 10 ? '0'.$arEnd[1] : $arEnd[1];
							$textTime .= $minute.':00';
						}
						else
						{
							$textTime .= '00:00';
						}
						$arData[$i]['start'] = $order->getBkorderCheckIn().$textTime;
						$arData[$i]['title'] = $title;
						$arData[$i]['className'] = $className;
						$arData[$i]['order_id'] = $objOrder->getId();
						$arData[$i]['qt_item_id'] = $order->getBkorderQtItemId();
						$i++;
					}*/
                    $it = $i+1;
                    $arData[$i]['start'] = $order->getBkorderCheckIn().' '.$serviceStart.':00';
                    $arData[$i]['title'] = $title;
                    $arData[$i]['className'] = $className;
                    $arData[$i]['order_id'] = $objOrder->getId();
                    $arData[$i]['qt_item_id'] = $order->getBkorderQtItemId();
                    $arData[$it]['start'] = $order->getBkorderCheckIn().' '.$serviceEnd.':00';
                    $arData[$it]['title'] = $title;
                    $arData[$it]['className'] = $className;
                    $arData[$it]['order_id'] = $objOrder->getId();
                    $arData[$it]['qt_item_id'] = $order->getBkorderQtItemId();
                    $it++;
                    $i = $it;
				}
				elseif($booking->getBookingType() == 'per_day'  && $booking->getBookingTime() == 3)
				{
					$tempIntervals = $order->getBkorderIntervalTime();
					$arTempIntervals = array();
					if($tempIntervals != '')
					{
						$arTempIntervals = explode(',',$tempIntervals);
						foreach($arTempIntervals as $intervalId)
						{
                            $interval = $this->_intervalhours->load($intervalId);
                            if(!$interval || !$interval->getId())
                            {
                                continue;
                            }
                            $tempInterval = $interval->getIntervalhoursBookingTime();
							$arTempIntervals = explode('_',$tempInterval);
							if($arTempIntervals[2] > $arTempIntervals[0])
							{
								for($tempM = $arTempIntervals[0]; $tempM < $arTempIntervals[2]; $tempM++)
								{
									$temptext = 'T';
									$temptext2 = 'T';
									if($tempM == $arTempIntervals[0])
									{
										$temptext .= $arTempIntervals[0].':'.$arTempIntervals[1];
										$temptext2 .= $arTempIntervals[2].':'.$arTempIntervals[3];
									}
									else
									{
										$temptext .= $arTempIntervals[0].':00';
										$temptext2 .= $arTempIntervals[0].':00';
									}
									$arData[$i]['start'] = $order->getBkorderCheckIn().$temptext;
									$arData[$i]['end'] = $order->getBkorderCheckIn().$temptext2;
									$arData[$i]['title'] = $title;
									$arData[$i]['className'] = $className;
									$arData[$i]['order_id'] = $objOrder->getId();
									$arData[$i]['qt_item_id'] = $order->getBkorderQtItemId();
									$i++;
								}
							}
							else
							{
									$arData[$i]['start'] = $order->getBkorderCheckIn().'T'.$arTempIntervals[0].':'.$arTempIntervals[1];
									$arData[$i]['end'] = $order->getBkorderCheckIn().'T'.$arTempIntervals[2].':'.$arTempIntervals[3];
									$arData[$i]['title'] = $title;
									$arData[$i]['className'] = $className;
									$arData[$i]['order_id'] = $objOrder->getId();
									$arData[$i]['qt_item_id'] = $order->getBkorderQtItemId();
									$i++;
							}
						}
					}
				}
				else
				{
					$arData[$i]['start'] = $order->getBkorderCheckIn();
					$arData[$i]['end'] = $order->getBkorderCheckOut();
					$arData[$i]['title'] = $title;
					$arData[$i]['className'] = $className;
					$arData[$i]['order_id'] = $objOrder->getId();
					$arData[$i]['qt_item_id'] = $order->getBkorderQtItemId();
					$i++;
				}
			}
		}
		$jsonReports = $this->_bkText->getBkJsonEncode($arData);
		return $jsonReports;
	}
	function getTotalItems()
	{
		$bookingModel = $this->_bookingsFactory->create();
		$arrayAttributeSelect = array('entity_id');
		$arAttributeConditions = array('status'=>1);
		$bookings = $bookingModel->getBookings($arrayAttributeSelect,$arAttributeConditions);
		$count = $bookings->getSize();
		$facilitiesModel = $this->_facilitiesFactory->create();
		$facilities = $facilitiesModel->getBkFacilities(array('facility_id'),array('facility_status'=>1));
		$facilitiesTotal = $facilities->getSize();
		//get orderId from bk order
		$bkOrderModel = $this->_bookingordersFactory->create();
		$collection = $bkOrderModel->getCollection()
				->addFieldToSelect('bkorder_order_id');
		$collection->getSelect()->group('bkorder_order_id');
		$orderIds = array();
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$orderIds[] = $collect->getBkorderOrderId();
			}
		}
		$bkCoreModel = $this->_bkCoreOrder;
		$orders = $bkCoreModel->getCollection()
				->addFieldToSelect('entity_id')
				->addFieldToSelect('customer_id')
				->addFieldToSelect('customer_email')
				->addFieldToFilter(
					array('status','status'),
					array(
						array('eq'=>'success'),
						array('eq'=>'pending')
					)
				)
				->addFieldToFilter('entity_id',array('in'=>$orderIds));
		$numberCustomer = 0;
		$totalOrder = $orders->getSize();
		$arCustomerIds = array();
		$arCustomerEmail = array();
		foreach($orders as $order)
		{
			if($order->getCustomerId() >  0)
			{
				if(in_array($order->getCustomerId(),$arCustomerIds))
				{
					continue;
				}
				else
				{
					$arCustomerIds[] = $order->getCustomerId();
				}
			}
			else
			{
				if(in_array($order->getCustomerEmail(),$arCustomerEmail))
				{
					continue;
				}
				else
				{
					$arCustomerEmail[] = $order->getCustomerEmail();
				}
			}
			$numberCustomer++;
		}
		return array(
			'total_item'=>$count,
			'total_order'=>$totalOrder,
			'total_customer'=>$numberCustomer,
			'total_facility'=>$facilitiesTotal,
		);
	}
	/**
	* get booking for selet
	**/
	function getListBookingItems()
	{
		$bookingModel = $this->_bookingsFactory->create();
		$arrayAttributeSelect = array('*');
		$arAttributeConditions = array('status'=>1);
		$condition = '';
		$arrayBooking = array('booking_type');
		$bookings = $bookingModel->getBookings($arrayAttributeSelect,$arAttributeConditions,$condition,$arrayBooking);
		return $bookings; 
	}
	/**
	* get rooms of hotel
	* @param int $hotelId
	* @return array $rooms
	**/
	function getRoomOfHotel($hotelId)
	{
		$storeId = $this->getRequest()->getParam('store',0);
		$roomModel = $this->_roomsFactory->create();
		$arrayseletct = array('room_id','room_type');
		$rooms = $roomModel->getBkRoomsById($hotelId,$arrayseletct);
		
		$roomTypesModel = $this->_roomtypesFactory->create();
		$arRoom = array();
		if(count($rooms))
		{
			$i = 0;
			foreach($rooms as $room)
			{
				$roomType = $roomTypesModel->load($room->getRoomType());
				if($roomType)
				{
					$title = $this->_bkText->showTranslateText($roomType->getRoomtypeTitle(),$roomType->getRoomtypeTitleTransalte(),$storeId);
					$arRoom[$i]['room_id'] = $room->getId();
					$arRoom[$i]['room_title'] = $title;
					$i++;
				}
			}
		}
		return $arRoom;
	}
	/**
	* get detail order
	* @param int $orderId
	* @return data 
	**/
	function getOrderDetail()
	{
		$params = $this->getRequest()->getParams();
		$orderId = $this->getRequest()->getParam('order_id',0);
		$qtItemId = $this->getRequest()->getParam('qt_item_id',0);;
		$bkCoreModel = $this->_bkCoreOrder;
		$order = $bkCoreModel->load($orderId);
		$arrayData = array();
		$items = $order->getAllVisibleItems();
		$formatDate = $this->_bkText->getFieldSetting('bookingsystem/setting/format_date');
		$bkOrderModel = $this->_bookingordersFactory->create();
		$addressModel = $this->_bkOrderAddess;
		$bookingModel = $this->_bookingsFactory->create();
		if(count($items))
		{
			foreach($items as $item)
			{
				//get data from booking order
				$storeId = $this->getRequest()->getParam('store',0);
				$collecion = $bkOrderModel->getCollection();
				$collecion->addFieldToFilter('bkorder_order_id',$item->getOrderId());
						$collecion->addFieldToFilter('bkorder_qt_item_id',$item->getItemId());
				$bkOrder = $collecion->getFirstItem();
				$nameRoom = '';
				if(!$bkOrder->getId())
					continue;
				$bookingId = $bkOrder->getBkorderBookingId();
				echo $bookingId;
				$booking = $bookingModel->getBooking($bookingId);
				$bookingTime = 1;
				if($booking && $booking->getId() && $booking->getBookingType() == 'per_day')
                {
                    //echo  "Booking Id".$booking->getId();
                    $bookingTime = $booking->getBookingTime();
                }
				if($bkOrder->getBkorderRoomId() == 1)
				{
					$roomId = $bkOrder->getBkorderBookingId();
					$roomModel = $this->_roomsFactory->create();
					$room = $roomModel->load($roomId);
					if($room && $room->getId())
					{
						$roomTypesModel = $this->_roomtypesFactory->create();
						$roomType = $roomTypesModel->load($room->getRoomType());
						if($roomType && $roomType->getId())
						{
							$nameRoom = $this->_bkText->showTranslateText($roomType->getRoomtypeTitle(),$roomType->getRoomtypeTitleTransalte(),$storeId);
						}
					}
					
				}
				$serviceStart = '';
				$serviceEnd = '';
                if($bkOrder->getBkorderServiceStart() != '' && $bkOrder->getBkorderServiceStart() != '')
				{
					/*$arServiceStart = explode(',',$bkOrder->getBkorderServiceStart());
					$serviceStart = $arServiceStart[0] < 10 ? '0'.$arServiceStart[0] : $arServiceStart[0]; 
					$serviceStart .= ': ';
					$serviceStart .= $arServiceStart[1] < 10 ? '0'.$arServiceStart[1] : $arServiceStart[1]; 
					$serviceStart .= ': ';
					$serviceStart .= $arServiceStart[2] == 1 ? __('AM') : __('PM'); 
					$arServiceEnd = explode(',',$bkOrder->getBkorderServiceEnd());
					$serviceEnd = $arServiceEnd[0] < 10 ? '0'.$arServiceEnd[0] : $arServiceEnd[0]; 
					$serviceEnd .= ': ';
					$serviceEnd .= $arServiceEnd[1] < 10 ? '0'.$arServiceEnd[1] : $arServiceEnd[1]; 
					$serviceEnd .= ': ';
					$serviceEnd .= $arServiceEnd[2] == 1 ? __('AM') : __('PM'); */
                    $serviceStart = $bkOrder->getBkorderServiceStart();
                    $serviceEnd = $bkOrder->getBkorderServiceEnd();
                    $timeModel = $this->getBkConfiguration('bookingsystem/setting/time_mode');
                    if($timeModel == 1)
                    {
                        $arServiceStart = explode(':',$serviceStart);
                        $arServiceEnd = explode(':',$serviceEnd);
                        if(count($arServiceStart) == 2 && count($arServiceEnd) == 2)
                        {
                            $textType1 = $arServiceStart[0] >= 12 ? __('PM') : __('AM');
                            $textType2 = $arServiceEnd[0] >= 12 ? __('PM') : __('AM');
                            $arServiceStart[0] = $arServiceStart[0] > 12 ? $arServiceStart[0] - 12 : $arServiceStart[0];
                            $arServiceEnd[0] = $arServiceEnd[0] > 12 ? $arServiceEnd[0] - 12 : $arServiceEnd[0];
                            $serviceStart = $arServiceStart[0]. ':'.$arServiceStart[1].' '.$textType1;
                            $serviceEnd = $arServiceEnd[0]. ':'.$arServiceEnd[1].' '.$textType2;

                        }
                    }
				}
				//active class for item
				$classActive = '';
				if($qtItemId == $bkOrder->getBkorderQtItemId())
				{
					//$classActive = 'bk-item-active';
				}
				else
				{
					continue;
				}
				//get customer_address
				$customerAddress = null;
				if($order->getShippingAddress())
				{
					$shippingId = $order->getShippingAddress()->getId();
					$customerAddress = $addressModel->load($shippingId);
				}
				elseif($order->getBillingAddress())
				{
					$billingId = $order->getBillingAddress()->getId();
					$customerAddress = $addressModel->load($billingId);
				}
				$arrayData['name'] = $item->getName();
				if($nameRoom != '')
				{
					$arrayData['name'] = __('%1 of ',$nameRoom). $item->getName();
				}
				$arrayData['sku'] = $item->getSku();
				$arrayData['price'] = $item->getPrice();
				$arrayData['row_total'] = $item->getRowTotal();
				$arrayData['base_row_total'] = $item->getBaseRowTotal();
				$arrayData['global_currency_code'] = $order->getGlobalCurrencyCode();
				$arrayData['order_currency_code'] = $order->getOrderCurrencyCode();
				$arrayData['check_in'] = date($formatDate,strtotime($bkOrder->getBkorderCheckIn()));
				$arrayData['check_out'] =  date($formatDate,strtotime($bkOrder->getBkorderCheckOut()));
				$arrayData['service_start'] = $serviceStart;
				$arrayData['service_end'] = $serviceEnd;
				$arrayData['total_days'] = $bkOrder->getBkorderTotalDays();
				$arrayData['total_hours'] = $bkOrder->getBkorderTotalHours();
				$arrayData['qty'] = $bkOrder->getBkorderQty();
				$arrayData['created_at'] =  date($formatDate.' H:i:s',strtotime($item->getCreatedAt()));
				$arrayData['class_active'] =  $classActive;
				$arrayData['customer_address'] =  $customerAddress;
				$arrayData['booking_time'] = $bookingTime;
				$arrayData['interval_hours'] =  $bkOrder->getBkorderIntervalTime();
			}
		}
		
		return $arrayData;
	}
	function getBkCurrencyInterface()
	{
		return $this->_currencyInterface;
	}
	/* get all country */
	function getBkCountriesOptions()
	{
		return $this->_countryFactory->toOptionArray();
	}
	function getBkAjaxUrl()
	{
		$urlSearch = $this->_bkText->getBkAdminAjaxUrl('bookingsystem/dashboard/search');
		$urlView = $this->_bkText->getBkAdminAjaxUrl('bookingsystem/dashboard/view');
		return array(
			'url_search' => $urlSearch,
			'url_view' => $urlView
		);
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
	* get Core Helper Price
	**/
	function getBkPriceHelper()
	{
		return $this->_priceHelper;
	}
	function getBkAdminUrl()
	{
		$urlItems = $this->_backendHelper->getUrl('bookingsystem/bookings/index');
		$urlFacility = $this->_backendHelper->getUrl('bookingsystem/facilities/index');
		$urlOrders = $this->_backendHelper->getUrl('bookingsystem/bookingorders/index');
		return array(
			'url_items'=>$urlItems,
			'url_facility'=>$urlFacility,
			'url_orders'=>$urlOrders,
		);
	}
	/**
	* load interval
     */
	function  getBkIntervalById($id)
    {
        return $this->_intervalhours->load($id);
    }
    /*
     * get Booking configuration
     * @param(string) $path
     * @return string
     * */
    function  getBkConfiguration($path)
    {
        return $this->_bkText->getFieldSetting($path);
    }
}