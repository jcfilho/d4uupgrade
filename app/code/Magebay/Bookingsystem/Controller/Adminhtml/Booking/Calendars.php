<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Booking;

use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;

class Calendars extends Action
{
	 /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	protected $_resultJsonFactory;
    /**
     * @var \Magebay\Bookingsystem\Model\Bookings;
     */
    protected  $_bookings;
	 /**
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory;
     */
	 protected $_calendarsFactory;
    /**
     * @var \Magebay\Bookingsystem\Model\Intervalhours;
     */
	 protected  $_intervalhoursFactory;
    /**
     * @var \Magebay\Bookingsystem\Helper\BkOrderHelper;
     */
	 protected $_bkOrderHelper;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
        \Magebay\Bookingsystem\Model\Bookings $bookings,
		CalendarsFactory $calendarsFactory,
        \Magebay\Bookingsystem\Model\IntervalhoursFactory $intervalhoursFactory,
        \Magebay\Bookingsystem\Helper\BkOrderHelper $bkOrderHelper
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_bookings = $bookings;
		$this->_calendarsFactory = $calendarsFactory;
		$this->_intervalhoursFactory = $intervalhoursFactory;
		$this->_bkOrderHelper = $bkOrderHelper;
	}
	public function execute()
	{
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$conditions = array('calendar_booking_type'=>$bookingType);
		$model = $this->_calendarsFactory->create();
		$calendars = $model->getBkCurrentCalendarsById($bookingId,array('*'),$conditions);
		$dataCalendar = array();
        $intervalModel = $this->_intervalhoursFactory->create();
        $okInterval = false;
        $bookingTime = 1;
        $bookingTourType = 1;
        $arCondition = array();
        $arSelect = array('booking_time','booking_tour_type');
        $booking = $this->_bookings->getBooking($bookingId,$arCondition,$arSelect);
        $arTimeSlotsSelect = array('intervalhours_quantity','intervalhours_check_in','intervalhours_check_out','intervalhours_days');
        if($booking && $booking->getId())
        {
            $bookingTime = $booking->getBookingTime();
            $bookingTourType = $booking->getBookingTourType();
        }
		if(count($calendars))
		{
			foreach($calendars as $key => $calendar)
			{
			    if(($bookingTime == 5 && $bookingTourType == 1 && $calendar->getCalendarDefaultValue() == 2) || ($bookingTime == 5 && $bookingTourType == 2 && $calendar->getCalendarDefaultValue() == 1))
                {
                    continue;
                }
				$dataCalendar[$key]['start_date'] = $calendar->getCalendarStartdate();
				$dataCalendar[$key]['end_date'] = $calendar->getCalendarEnddate();
				$dataCalendar[$key]['status'] = $calendar->getCalendarStatus();
				$dataCalendar[$key]['price'] = $calendar->getCalendarPrice();
				$dataCalendar[$key]['promo'] = $calendar->getCalendarPromo();
				$dataCalendar[$key]['qty'] = $calendar->getCalendarQty();
				$dataCalendar[$key]['default_value'] = $calendar->getCalendarDefaultValue();
			}
		}
		if(count($dataCalendar))
		{
			$arCalendars = $dataCalendar;
			$arDefaultCalendar = array();
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
            }
		}
        $arOrder = array();
        if($bookingType == 'per_day')
        {
            if($bookingTime == 3)
            {
                $arOrder = $this->_bkOrderHelper->getArrayIntervalsInOrders($bookingId);
            }
            else
            {
                $arOrder = $this->_bkOrderHelper->getArrayItemsInOrder($bookingId,0);
            }

        }
        elseif($bookingType == 'hotel')
        {
            $arOrder = $this->_bkOrderHelper->getArrayItemsInOrder($bookingId,1);
        }
        $arrayData = array('data_calendar'=>$dataCalendar,'data_order'=>$arOrder);
		$resultJson = $this->_resultJsonFactory->create();
		return $resultJson->setData($arrayData);
		
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}