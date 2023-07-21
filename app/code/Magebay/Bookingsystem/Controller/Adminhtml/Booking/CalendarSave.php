<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Model\Intervalhours;

class CalendarSave extends Action
{
	 /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	 
	protected $_resultJsonFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory;
     */
	 protected $_calendarsFactory;
	 /**
	 * @var \Magebay\Bookingsystem\Model\Intervalhours;
      */
	 protected $_intervalhours;
    /**
     * @var \Magento\Backend\Model\Session;
     */
    protected $_backendSession;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		\Magento\Backend\Model\Session $session,
		CalendarsFactory $calendarsFactory,
        Intervalhours $intervalhours
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_backendSession = $session;
		$this->_calendarsFactory = $calendarsFactory;
		$this->_intervalhours = $intervalhours;
	}
	public function execute()
	{
		$status = false;
		$messageStatus = '';
		$note = 0;
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$bookingTime = $this->_request->getParam('booking_time',0);
		$calendarId = (int)$this->getRequest()->getParam('calendar_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$dataSend = array(
			'booking_id'=>$bookingId,
			'booking_type'=>$bookingType
		);
		$params = $this->_request->getParams();
		$model = $this->_calendarsFactory->create();
		try {
		        if($bookingTime == 3)
                {
                    $newCheckIn = $this->getRequest()->getParam('item_day_start_date','');
                    $newCheckOut = $this->getRequest()->getParam('item_day_end_date','');
                    $paramsTimSlot = $this->getRequest()->getParam('time_slot',array());
                    if($calendarId > 0 && count($paramsTimSlot))
                    {
                        $timeSlots = $this->_intervalhours->saveTimeSlots($bookingId,$paramsTimSlot,$calendarId,$newCheckIn,$newCheckOut,false);
                        $totalQty = $timeSlots['total_qty'];
                        $minPirce = $timeSlots['min_price'];
                        if($totalQty > 0)
                        {
                            $model->setData(array('calendar_qty'=>$totalQty,'calendar_price'=>$minPirce))->setId($calendarId)->save();
                            $params['item_day_qty'] = $totalQty;
                            $params['item_day_price'] = $minPirce;
                            $status = true;
                            $messageStatus = __('You have saved Data');
                        }
                        else
                        {
                            $status = false;
                            $note = 1;
                            $messageStatus = __('Please check Time Slot data');

                        }
                    }
                    else
                    {
                        $newCalendarId = 0;
                        if($this->_backendSession->getIsUpdateCaledar() && $this->_backendSession->getIsUpdateCaledar() == 1)
                        {
                            $newCalendarId = $model->saveBkCalendars($params,false);
                            $this->_backendSession->setNewCalendarId($newCalendarId);
                        }
                        if($newCalendarId == 0)
                        {
                            $newCalendarId = $this->_backendSession->getNewCalendarId();
                        }
                        if($newCalendarId > 0 && count($paramsTimSlot))
                        {
                            $timeSlots = $this->_intervalhours->saveTimeSlots($bookingId,$paramsTimSlot,$newCalendarId,$newCheckIn,$newCheckOut,false);
                            $totalQty = $timeSlots['total_qty'];
                            $minPirce = $timeSlots['min_price'];
                            if($totalQty > 0)
                            {
                                $model->setData(array('calendar_qty'=>$totalQty,'calendar_price'=>$minPirce))->setId($newCalendarId)->save();
                                $this->_backendSession->setIsUpdateCaledar(1);
                                $status = true;
                                $messageStatus = __('You have saved Data');
                            }
                            else
                            {
                                $status = false;
                                $note = 1;
                                $messageStatus = __('Please check Time Slot data');
                                $this->_backendSession->setIsUpdateCaledar(0);
                            }
                        }
                    }
                }
                else
                {
                    $model->saveBkCalendars($params,false);
                    $status = true;
                    $messageStatus = __('You have saved Data');
                }
			} catch (\Exception $e) {
				$messageStatus = $e->getMessage();
		}
		$htmlCalendarItems = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::catalog/product/calendars/items.phtml')->toHtml();
		$response = array('html_calendar_items'=> $htmlCalendarItems,'status'=>$status,'message'=>$messageStatus,'note'=>$note);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}