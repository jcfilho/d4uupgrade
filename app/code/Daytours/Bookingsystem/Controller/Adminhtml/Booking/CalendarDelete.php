<?php
 
namespace Daytours\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;

class CalendarDelete extends Action
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

	 protected  $_intervalhours;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		CalendarsFactory $calendarsFactory,
        \Magebay\Bookingsystem\Model\Intervalhours $intervalhours
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_calendarsFactory = $calendarsFactory;
		$this->_intervalhours = $intervalhours;
	}
	public function execute()
	{
		$status = false;
		$messageStatus = __('You can not delete item, Please check again!');
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$calendarId = $this->_request->getParam('calendar_id',0);
        $calendarNumber = $this->_request->getParam('calendar_number','1');

		$resultJson = $this->_resultJsonFactory->create();
		$dataSend = array(
			'booking_id'=>$bookingId,
			'booking_type'=>$bookingType
		);
		$params = $this->_request->getParams();
		$model = $this->_calendarsFactory->create();
		//get check in check out by calendar id
        $checkIn = '';
		if($calendarId > 0)
		{
			try {
			    $calendar = $model->load($calendarId);
			    if($calendar && $calendar->getId())
                {
                    $checkIn  = $calendar->getCalendarStartdate();
                }
				$model->setId($calendarId)->delete();
				$status = true;
				$messageStatus = __('Item have been delete success');
			} catch (\Exception $e) {
				$messageStatus = $e->getMessage();
			}
		}
		//delete intervals if exit
        $inervals = $this->_intervalhours->getIntervals($bookingId,$checkIn,1,$calendarNumber);
		if(count($inervals))
        {
            foreach ($inervals as $inerval)
            {
                $this->_intervalhours->setId($inerval['intervalhours_id'])->delete();
            }
        }

        $dataSend['calendar_number'] = $calendarNumber;

        if( $calendarNumber == \Daytours\Bookingsystem\Block\Adminhtml\Calendars::CALENDAR_NUMBER_BY_DEFAULT ){

            $htmlCalendarItems = $this->_view->getLayout()->createBlock('Daytours\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Daytours_Bookingsystem::catalog/product/calendars/items.phtml')->toHtml();

        }else if( $calendarNumber == \Daytours\Bookingsystem\Block\Adminhtml\Calendars::CALENDAR_NUMBER_BY_SECOND ){

            $htmlCalendarItems = $this->_view->getLayout()->createBlock('Daytours\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Daytours_Bookingsystem::catalog/product/calendars/items_calendar_two.phtml')->toHtml();

        }
		$response = array('html_calendar_items'=> $htmlCalendarItems,'status'=>$status,'message'=>$messageStatus);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}