<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Createorder;
 
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkOrderHelper;
use Magebay\Bookingsystem\Model\BookingordersFactory;

class LoadCalendar extends Action
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
		BookingordersFactory $bookingordersFactory
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
    }
    public function execute()
    {
      // $bookingId  = $productId if simple and $roomId if hotel
		$bookingId = $this->_request->getParam('booking_id');
		$bookingType = $this->_request->getParam('booking_type');
		$itemId = $this->_request->getParam('itemId',0);
		$arCalendars = array();
		$arrayseletct = array('*');
		$conditions = array('calendar_booking_type'=>$bookingType);
		$calendarModel = $this->_calendarsFactory->create();
		$calendars = $calendarModel->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions);
		//get symbol
		$symbol = $this->_currency->getCurrencySymbol();
		$dataCalendar = array();
		if(count($calendars))
		{
			// $arCalendars = $calendars->getData();
			$dataCalendar = array();
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
		$backendSession = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
		$isBackend  = false;
		if($backendSession->isLoggedIn())
		{
			$isBackend = true;
		}
		if($bookingType == 'per_day')
		{
			$cartItems = $this->_bkOrderHelper->getArrayItemIncart($bookingId,$itemId,$isBackend);
			$arraySelect = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id','bkorder_qt_item_id');
			//get booking order in cart
			$orders = $bkOrderModel->getCollection()
					->addFieldToSelect($arraySelect)
					->addFieldToFilter('bkorder_booking_id',$bookingId)
					->addFieldToFilter('bkorder_room_id',0);
		}
		else
		{
			$roomId = $bookingId;
			$arraySelect = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id','bkorder_qt_item_id');
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
		//fix for order 
		$oldOrderItemId = $this->getRequest()->getParam('oldOrderItemId',0);
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
				if($oldOrderItemId > 0)
				{
					if($order->getBkorderQtItemId() == $oldOrderItemId)
					{
						continue;
					}
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
 