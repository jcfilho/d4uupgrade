<?php

namespace Daytours\Bookingsystem\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Directory\Model\Currency;

class Calendars extends \Magebay\Bookingsystem\Block\Adminhtml\Calendars
{
    const CALENDAR_NUMBER_BY_DEFAULT = 1;
    const CALENDAR_NUMBER_BY_SECOND = 2;
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
     * @var \Daytours\Bookingsystem\Model\Intervalhours;
     */
	protected $_intervalhours;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $json;
    /**
     * @var \Magento\Backend\Block\Widget\Context
     */
    private $context;

    /**
     * Calendars constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param PriceHelper $priceHelper
     * @param Timezone $timezone
     * @param Currency $currency
     * @param BkHelperDate $bkHelperDate
     * @param CalendarsFactory $calendarsFactory
     * @param \Magebay\Bookingsystem\Model\Bookings $bookings
     * @param \Magebay\Bookingsystem\Model\Rooms $rooms
     * @param \Magebay\Bookingsystem\Model\Intervalhours $intervalhours
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
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
        \Magento\Framework\Serialize\Serializer\Json $json,
		array $data = []
	)
	{
		parent::__construct($context,$priceHelper,$timezone,$currency,$bkHelperDate,$calendarsFactory,$bookings,$rooms,$intervalhours, $data);

		$this->_priceHelper = $priceHelper;
        $this->_bkHelperDate = $bkHelperDate;
        $this->_calendarsFactory = $calendarsFactory;
		$this->_timeZone = $timezone;
		$this->_currency = $currency;
		$this->_bookings = $bookings;
		$this->_rooms = $rooms;
		$this->_intervalhours = $intervalhours;

        $this->json = $json;
        $this->context = $context;

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
		$calendars = $model->getBkCalendarsById($bookingId,array('*'),array(
		    'calendar_booking_type' =>  $bookingType,
            'calendar_number'       =>  self::CALENDAR_NUMBER_BY_DEFAULT)
        );
		return $calendars;
	}
    /*
    * get items for second calendars, for example to product transfer
    * @param int $bookingId, string $bookingType = per_day
    * @return array $items
    **/
    function getBkItemsCalendarTwo()
    {
        $arData = array();
        $bookingId = $this->getBookingId();
        $bookingType = $this->getBookingType();
        $calendars = array();
        $model = $this->_calendarsFactory->create();
        $calendars = $model->getBkCalendarsById($bookingId,array('*'),array(
                'calendar_booking_type' =>  $bookingType,
                'calendar_number'       =>  self::CALENDAR_NUMBER_BY_SECOND)
        );
        return $calendars;
    }

    /*
     * return number calendar by default
     **/
    function getNumberCalendarByDefault(){
        return self::CALENDAR_NUMBER_BY_DEFAULT;
    }

    /*
     * return number calendar from second calendar
     **/
    function getNumberCalendarTwo(){
        return self::CALENDAR_NUMBER_BY_SECOND;
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
        $calendars = $model->getBkCalendarsById($bookingId,array('*'),array(
            'calendar_booking_type' => $bookingType,
            'calendar_number'       =>  self::CALENDAR_NUMBER_BY_DEFAULT));
        return $calendars;
    }

    /*
	* get items for calendars to validate data
	* @param int $bookingId, string $bookingType
	* @return array $items
	**/
    function getBkCalendarsTwo()
    {
        $bookingId = $this->getBookingId();
        $bookingType = $this->getBookingType();
        $model = $this->_calendarsFactory->create();
        $calendars = $model->getBkCalendarsById($bookingId,array('*'),array(
            'calendar_booking_type' => $bookingType,
            'calendar_number'       =>  self::CALENDAR_NUMBER_BY_SECOND));
        return $calendars;
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
        $calendarNumber = $this->getRequest()->getParam('calendar_number',1);
        $intervals = array();
        if($calendarId > 0)
        {
            $intervals = $this->_intervalhours->getBaseTimeSlots($bookingId,$checkIn,$checkOut,array('*'),$calendarNumber);
        }
        if(count($intervals))
        {
            $intervals = $intervals->getData();
        }
        return $intervals;
    }

    public function getDataToLockedDates($calendarNumber){

        $bkHelperDate = $this->getBkHelperDate();

        $data = [
            'urlToLoadLockedDates'      => $bkHelperDate->getBkAdminAjaxUrl('bookinglocked/locked/listinfo'),
            'urlToSaveLockedDates'      => $bkHelperDate->getBkAdminAjaxUrl('bookinglocked/locked/save'),
            'urlToDeleteLockedDates'    => $bkHelperDate->getBkAdminAjaxUrl('bookinglocked/locked/delete'),
            'productId'                 => $this->getBookingId(),
            'currentDate'               => $this->getBkCurrentDate(),
            'formatDate'                => $this->getBkHelperDate()->getFormatDateLabel(false),
            'calendarNumber'            => $calendarNumber
        ];

        return $this->json->serialize($data);
    }

}