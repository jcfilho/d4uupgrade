<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magebay\Bookingsystem\Model\Bookings;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
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
     * Result page factory
     *
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory;
     */
	 protected $_calendarsFactory;
    protected  $_intervalhoursFactory;
    protected $_bkOrderHelper;
    protected  $_bookings;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
        \Magebay\Bookingsystem\Helper\BkOrderHelper $bkOrderHelper,
        \Magebay\Bookingsystem\Model\Bookings $bookings,
        \Magebay\Bookingsystem\Model\IntervalhoursFactory $intervalhoursFactory,
        CalendarsFactory $calendarsFactory


	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_calendarsFactory = $calendarsFactory;
        $this->_intervalhoursFactory = $intervalhoursFactory;
        $this->_bkOrderHelper = $bkOrderHelper;
        $this->_bookings = $bookings;
	}
	public function execute()
	{
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$conditions = array('calendar_booking_type'=>$bookingType);
		$model = $this->_calendarsFactory->create();
		$calendars = $model->getBkCurrentCalendarsById($bookingId,array('*'),$conditions);
		$dataCalendar = array();
        $okInterval = false;
        $intervalModel = $this->_intervalhoursFactory->create();
        $arTimeSlotsSelect = array('intervalhours_quantity','intervalhours_check_in','intervalhours_check_out','intervalhours_days');
        $bookingTime = 1;
        $bookingTourType = 1;
        $arCondition = array();
        $arSelect = array('booking_time','booking_tour_type');
        $booking = $this->_bookings->getBooking($bookingId,$arCondition,$arSelect);
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
                if(isset($dataCalendar[$key]['extract_persons']))
                {
                    unset($dataCalendar[$key]['extract_persons']);
                }
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
                $intervals = $intervalModel->getBaseTimeSlots($bookingId,$value['start_date'],$value['end_date'],$arTimeSlotsSelect);
                if(count($intervals))
                {
                    $dataCalendar[$mKey]['inter_value'] = $intervals->getData();
                    $okInterval = true;
                }
            }
		}
        $arOrder = array();
        if($bookingType == 'per_day')
        {
            if($okInterval)
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
}