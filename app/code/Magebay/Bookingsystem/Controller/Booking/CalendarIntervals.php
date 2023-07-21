<?php
 
namespace Magebay\Bookingsystem\Controller\Booking;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkOrderHelper;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;

class CalendarIntervals extends \Magento\Framework\App\Action\Action
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
     * @var Magento\Framework\Pricing\Helper\Data 
    */
	protected $_priceHelper;
	/**
     *
     * @var Magento\Sales\Model\Order
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
     * @var \Magebay\Bookingsystem\Model\IntervalhoursFactory;
     */
	 protected $_intervalhoursFactory;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Currency $currency,
		PriceHelper $priceHelper,
		BkCoreOrders $bkCoreOrders,
		CalendarsFactory $calendarsFactory,
		BkOrderHelper $bkOrderHelper,
		BookingordersFactory $bookingordersFactory,
		IntervalhoursFactory $intervalhoursFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_currency = $currency;
		$this->_priceHelper = $priceHelper;
		$this->_bkCoreOrders = $bkCoreOrders;
		$this->_calendarsFactory = $calendarsFactory;
		$this->_bkOrderHelper = $bkOrderHelper;
		$this->_bookingordersFactory = $bookingordersFactory;
		$this->_intervalhoursFactory = $intervalhoursFactory;
    }
    public function execute()
    {
		$bookingId = $this->getRequest()->getParam('booking_id',0);
		$bookingType = $this->getRequest()->getParam('booking_type','per_day');
		$itemId = $this->getRequest()->getParam('itemId',0);
		$arCalendars = array();
		$arrayseletct = array('*');
		$conditions = array('calendar_booking_type'=>$bookingType);
		$calendarModel = $this->_calendarsFactory->create();
		$calendars = $calendarModel->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions);
		//get symbol
		$symbol = $this->_currency->getCurrencySymbol();
		$dataCalendar = array();
		$intervals = array();
        $intervalModel = $this->_intervalhoursFactory->create();
		$arTimeSlotsSelect = array('intervalhours_quantity','intervalhours_check_in','intervalhours_check_out','intervalhours_days');
		if(count($calendars))
		{
			if(count($calendars))
			{
				foreach($calendars as $key => $calendar)
				{
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
			$dataCalendar = array_values($arCalendars);
			foreach($dataCalendar as $mKey => $value)
			{
				//get count hours intervals in day
				$strDay = $value['start_date'];
				if($value['default_value'] == '1')
				{
					$strDay = '';
				}
				$intervals = $intervalModel->getBaseTimeSlots($bookingId,$value['start_date'],$value['end_date'],$arTimeSlotsSelect);
				$tempQty = 0;
				if(count($intervals))
                {
                    $dataCalendar[$mKey]['inter_value'] = $intervals->getData();
                }
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

		//get array booking in cart
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
		$bkOrderModel = $this->_bookingordersFactory->create();
		$arSelectOrder = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_quantity_interval','bkorder_interval_time','bkorder_order_id');
		$orders = $bkOrderModel->getCollection()
				->addFieldToSelect($arSelectOrder)
				->addFieldToFilter('bkorder_booking_id',$bookingId)
				->addFieldToFilter('bkorder_room_id',0);
		$arOrder = array();
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
				$tempQty = 0;
				$tempInters = explode(',',$order->getBkorderIntervalTime());
				if(count($tempInters))
				{
					foreach($tempInters as $tempInter)
					{
						$tempQty += $order->getBkorderQuantityInterval();
					}
				}
				$arOrder[$i]['check_in'] = $order->getBkorderCheckIn();
				$arOrder[$i]['check_out'] = $order->getBkorderCheckIn();
				$arOrder[$i]['qty'] = $tempQty ;
				$i++;
			}
		}
		$finalArOrder = array_merge($arOrder,$newArCartItems);
		$arrayData = array('data_calendar'=>$dataCalendar,'data_order'=>$finalArOrder);
		$resultJson = $this->_resultJsonFactory->create();
		return $resultJson->setData($arrayData);
    }
}
 