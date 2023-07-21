<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Template;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Directory\Model\Currency;

class Calendars extends Template
{
	/**
     * @param \Magebay\Bookingsystem\Helper\BkHelperDate
     * 
     */
	protected $_bkHelperDate;
	/**
     * Result page factory
     *
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory;
     */
	protected $_calendarsFactory;
	/**
     * @var PriceHelper
     */
	protected $_priceHelper;
	/**
     * @var @Magento\Framework\Stdlib\DateTime\Timezone
     */
	protected $_timeZone;
	 /**
     * Result page factory
     *
     * @var \Magento\Directory\Model\Currency;
     */
	protected $_currency; 
	/**
     *
     * @var @Magebay\Bookingsystem\Model\Bookings;
     */
	protected $_bookings;
	/**
     *
     * @var @Magebay\Bookingsystem\Model\Rooms;
     */
	protected $_rooms;
	/**
     *
     * @var \Magebay\Bookingsystem\Model\Intervalhours;
     */
	protected $_intervalhours;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
        PriceHelper $priceHelper,
        Timezone $timezone,
        Currency $currency,
		BkHelperDate $bkHelperDate,
		CalendarsFactory $calendarsFactory,
        \Magebay\Bookingsystem\Model\Bookings $bookings,
		\Magebay\Bookingsystem\Model\Rooms $rooms,
		\Magebay\Bookingsystem\Model\Intervalhours $intervalhours,
		array $data = []
	)
	{
		parent::__construct($context, $data);

		$this->_priceHelper = $priceHelper;
        $this->_bkHelperDate = $bkHelperDate;
        $this->_calendarsFactory = $calendarsFactory;
		$this->_timeZone = $timezone;
		$this->_currency = $currency;
		$this->_bookings = $bookings;
		$this->_rooms = $rooms;
		$this->_intervalhours = $intervalhours;
		
	}
	function getBkAjaxUrl()
	{
		$bkHelperDate = $this->getBkHelperDate();
		$urlcalendar = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/calendars');
		$urlEdit = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/calendarEdit');
		$urlDelete = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/calendarDelete');
		$urlLoadItem = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/calendarItems');
		$urlSaveItem = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/calendarSave');
		$urlEditPriceSession = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/editPriceSession');
		$urlSavePriceSession = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/savePriceSession');
		return array(
			'url_calendars'=>$urlcalendar,
			'edit'=>$urlEdit,
			'dell'=>$urlDelete,
			'load_items'=>$urlLoadItem,
			'save_calendar'=>$urlSaveItem,
			'url_edit_price_session'=>$urlEditPriceSession,
			'url_save_price_session'=>$urlSavePriceSession,
		);
	}
	/*
	 * get invertals Ajax Url
	 * */
	function  getIntervalsAjaxUrl()
    {
        $bkHelperDate = $this->getBkHelperDate();
        $urlEdit = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/editInterval');
        $urlDelete = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/dellInterval');
        $urlSave = $bkHelperDate->getBkAdminAjaxUrl('bookingsystem/booking/saveInterval');
        return array(
            'edit'=>$urlEdit,
            'delete'=>$urlDelete,
            'save'=>$urlSave,
        );
    }
	/*
	* get items for calendars
	* @param int $bookingId, string $bookingType = per_day
	* @return array $items
	**/
	function getBkItems()
	{
		$arData = array();
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$calendars = array();
		$model = $this->_calendarsFactory->create();
		$calendars = $model->getBkCalendarsById($bookingId,array('*'),array('calendar_booking_type'=>$bookingType));
		return $calendars;
	}
	/*
	* get item for from edit 
	* @param int $calendarId
	* @return array $item
	**/
	function getBkItem()
	{
		$calendarId = $this->_request->getParam('calendar_id',0);
		//if get date from calendar
		$checkIn = $this->_request->getParam('check_in','');
		$checkOut = $this->_request->getParam('check_out','');
		$bookingType =  $this->_request->getParam('booking_type','per_day');
		$calendar = null;
		if($calendarId > 0)
		{
			$model = $this->_calendarsFactory->create();
			$calendar = $model->load($calendarId);
		}
		elseif($checkIn != '' && $checkOut != '')
		{
			$model = $this->_calendarsFactory->create();
			$collection = $model->getCollection()
					->addFieldToFilter('calendar_booking_type',$bookingType)
					->addFieldToFilter('calendar_startdate',array('lteq'=>$checkIn))
					->addFieldToFilter('calendar_enddate',array('gteq'=>$checkOut));
			if(count($collection))
			{
				$calendar = $collection->getFirstItem();
			}
		}
		return $calendar;
	}
	/*
	* get items for calendars to validate data
	* @param int $bookingId, string $bookingType
	* @return array $items
	**/
	function getBkCalendars()
	{
		$bookingId = $this->getBookingId();
		$bookingType = $this->getBookingType();
		$model = $this->_calendarsFactory->create();
		$calendars = $model->getBkCalendarsById($bookingId,array('*'),array('calendar_booking_type'=>$bookingType));
		return $calendars;
	}
	/*
	* get Bk helper date
	* @return Magebay\Bookingsystem\Helper\BkHelperDate
	**/
	function getBkHelperDate()
	{
		return $this->_bkHelperDate;
	}
	/*
	* get price helper
	* @return Magento\Framework\Pricing\Helper\Data
	**/
	function getBkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/*
	* get price helper
	* @return curret date
	**/
	function getBkCurrentDate()
	{
		$intCurrentTime = $this->_timeZone->scopeTimeStamp();
		$currDate = date('Y-m-d',$intCurrentTime);
		return $currDate;
	}
	/*
	* get price helper
	* @return Symbol
	**/
	function getBkCurrencySymbol()
	{
		return $this->_currency->getCurrencySymbol();
	}
	/*
	* get price helper
	* @param int $bookingId, string $bookingType
	* @return array $item
	**/
	function getBkInfor($bookingId,$bookingType)
	{
		$item = null;
		if($bookingType == 'per_day')
		{
			$item = $this->_bookings->load($bookingId,'booking_product_id');
		}
		else
		{
			$item = $this->_rooms->load($bookingId,'room_id');
		}
		return $item;
	}
	/*
	 * get interlvals
	 * */
	function  getTimeSlots()
    {
        $checkIn = $this->getRequest()->getParam('check_in','');
        $checkOut = $this->getRequest()->getParam('check_out','');
        $bookingId = $this->getRequest()->getParam('booking_id',0);
        $calendarId = $this->getRequest()->getParam('calendar_id',0);
        $intervals = array();
        if($calendarId > 0)
        {
            $intervals = $this->_intervalhours->getBaseTimeSlots($bookingId,$checkIn,$checkOut);
        }
        if(count($intervals))
        {
            $intervals = $intervals->getData();
        }
        return $intervals;
    }
	function getBkRequest()
	{
		return $this->_request;
	}
    function getBkConfig($field)
    {
        return $this->getBkHelperDate()->getFieldSetting($field,false);
    }
}