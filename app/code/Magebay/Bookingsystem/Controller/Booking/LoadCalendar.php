<?php
 
namespace Magebay\Bookingsystem\Controller\Booking;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkOrderHelper;
use Magebay\Bookingsystem\Model\BookingordersFactory;

class LoadCalendar extends \Magento\Framework\App\Action\Action
{
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
	/**
     *
     * @var Magento\Directory\Model\Currency
    */
	protected $_currency;
	/**
     *
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
    */
	protected $_timeZone;
	/**
     *
     * @var Magento\Framework\Pricing\Helper\Data 
    */
	protected $_priceHelper;
	/**
     *
     * @var \Magento\Sales\Model\Order
    */
	protected $_bkCoreOrders;
	 /**
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory;
     */
	 protected $_calendarsFactory;
	 /**
     * @var \Magebay\Bookingsystem\Helper\BkOrderHelper;
     */
	 protected $_bkOrderHelper;
	/**
     * @var \Magebay\Bookingsystem\Model\BookingordersFactory;
     */
	 protected $_bookingordersFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Currency $currency,
		Timezone $timezone,
		PriceHelper $priceHelper,
		BkCoreOrders $bkCoreOrders,
		CalendarsFactory $calendarsFactory,
		BkOrderHelper $bkOrderHelper,
		BookingordersFactory $bookingordersFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_currency = $currency;
		$this->_timeZone = $timezone;
		$this->_priceHelper = $priceHelper;
		$this->_bkCoreOrders = $bkCoreOrders;
		$this->_calendarsFactory = $calendarsFactory;
		$this->_bkOrderHelper = $bkOrderHelper;
		$this->_bookingordersFactory = $bookingordersFactory;
    }
    public function execute()
    {
      // $bookingId  = $productId if simple and $roomId if hotel
		$bookingId = $this->_request->getParam('booking_id');
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$bookingTime = $this->getRequest()->getParam('booking_time',1);
		$defaultTourType = $this->getRequest()->getParam('default_tour_type',1);
		$itemId = $this->_request->getParam('itemId',0);
		$arCalendars = array();
		$arrayseletct = array('*');
		$conditions = array('calendar_booking_type'=>$bookingType);
		$calendarModel = $this->_calendarsFactory->create();
		$calendars = $calendarModel->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions);
		//get symbol
		$symbol = $this->_currency->getCurrencySymbol();
		$intCurrentTime = $this->_timeZone->scopeTimeStamp();
		$strCurrentDate = date('Y-m-d',$intCurrentTime);
		$intCurrentDate = strtotime($strCurrentDate);
		$dataCalendar = array();
		if(count($calendars))
		{
			// $arCalendars = $calendars->getData();
			$dataCalendar = array();
			if(count($calendars))
			{
				foreach($calendars as $key => $calendar)
				{
				    if($bookingTime == 5 && $defaultTourType == 2)
                    {
                        if(strtotime($calendar->getCalendarStartdate()) <= $intCurrentDate)
                        {
                            continue;
                        }
                    }
					$arCalendars[$key]['start_date'] = $calendar->getCalendarStartdate();
					$arCalendars[$key]['end_date'] = $calendar->getCalendarEnddate();
					$arCalendars[$key]['status'] = $calendar->getCalendarStatus();
					$arCalendars[$key]['price'] = $calendar->getCalendarPrice();
					$arCalendars[$key]['promo'] = $calendar->getCalendarPromo();
					$arCalendars[$key]['qty'] = $calendar->getCalendarQty();
					$arCalendars[$key]['default_value'] = $calendar->getCalendarDefaultValue();
				}
			}
			$arDefaultCalendar = array();
			//move default item to button
			foreach($arCalendars as $key => $arCalendar)
			{
				if($arCalendar['default_value'] == 1)
				{
					$arDefaultCalendar = $arCalendar;
					unset($arCalendars[$key]);
					break;
				}
			}
			if(count($arDefaultCalendar))
			{
				$arCalendars[] = $arDefaultCalendar; 
			}
			//reset array
			$dataCalendar = array_values($arCalendars);
			//change format price
			foreach($dataCalendar as $mKey => $value)
			{
				if($value['price'] > 0)
				{
					$dataCalendar[$mKey]['text_price'] = $this->_priceHelper->currency($value['price'],false,false);
				}
				if($value['promo'] > 0)
				{
					$dataCalendar[$mKey]['text_promo'] = $this->_priceHelper->currency($value['promo'],false,false);
				}
			}
		}
		$arOrder = array();
		$cartItems = array();
		$bkOrderModel = $this->_bookingordersFactory->create();
		if($bookingType == 'per_day')
		{
			$cartItems = $this->_bkOrderHelper->getArrayItemIncart($bookingId,$itemId);
			if($bookingTime == 5)
            {
                foreach ($cartItems  as $carKey => $valueCart)
                {
                    $tempQty = $valueCart['qty'];
                    $persons = isset($valueCart['number_persons']) ? $valueCart['number_persons'] : array();
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
                    $cartItems[$carKey]['qty'] = $tempQty;
                }
            }
			$arraySelect = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id');
			//get booking order in cart
			$orders = $bkOrderModel->getCollection()
					->addFieldToSelect($arraySelect)
					->addFieldToFilter('bkorder_booking_id',$bookingId)
					->addFieldToFilter('bkorder_room_id',0);
		}
		else
		{
			$roomId = $bookingId;
			$arraySelect = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id');
			//get booking order in cart
			$orders = $bkOrderModel->getCollection()
					->addFieldToSelect($arraySelect)
					->addFieldToFilter('bkorder_booking_id',$bookingId)
					->addFieldToFilter('bkorder_room_id',1);
			//get $productId to get value in cart
			$productId = $this->getRequest()->getParam('hotel-id',0);
			//get Items if edit item in cart
			$itemId = $this->getRequest()->getParam('itemId',0);
			$cartItems = $this->_bkOrderHelper->getRoomArrayItemIncart($productId,$roomId,$itemId);
		}
		$coreOrderModel = $this->_bkCoreOrders;
		if(count($orders))
		{
			$i = 0;
			foreach($orders as $order)
			{
				$defaultOrder = $coreOrderModel->load($order->getBkorderOrderId());
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
				$arOrder[$i]['check_in'] = $order->getBkorderCheckIn();
				$arOrder[$i]['check_out'] = $order->getBkorderCheckOut();
				$arOrder[$i]['qty'] = $order->getBkorderQty();
				$i++;
			}
		}
		$arOrder = array_merge($arOrder,$cartItems);
		$arrayData = array('data_calendar'=>$dataCalendar,'data_order'=>$arOrder);
		$resultJson = $this->_resultJsonFactory->create();
		return $resultJson->setData($arrayData);
    }
}
 