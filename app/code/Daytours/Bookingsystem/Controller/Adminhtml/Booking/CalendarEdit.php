<?php
 
namespace Daytours\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class CalendarEdit extends Action
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
     * @var \Magento\Backend\Model\Session;
     */
    protected $_backendSession;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Session $session
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
        $this->_backendSession = $session;
	}
	public function execute()
	{
        $this->_backendSession->setIsUpdateCaledar(1);
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$resultJson = $this->_resultJsonFactory->create();
        $calendarNumber = $this->_request->getParam('calendar_number','1');
		$dataSend = array(
			'booking_id'=>$bookingId,
			'booking_type'=>$bookingType
		);
		if( $calendarNumber == \Daytours\Bookingsystem\Block\Adminhtml\Calendars::CALENDAR_NUMBER_BY_DEFAULT ){
            $dataSend['calendar_number'] = $calendarNumber;
            $htmlCalendarForm = $this->_view->getLayout()->createBlock('Daytours\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Daytours_Bookingsystem::catalog/product/calendars/form.phtml')->toHtml();
        }else if( $calendarNumber == \Daytours\Bookingsystem\Block\Adminhtml\Calendars::CALENDAR_NUMBER_BY_SECOND ){
            $dataSend['calendar_number'] = $calendarNumber;
            $htmlCalendarForm = $this->_view->getLayout()->createBlock('Daytours\Bookingsystem\Block\Adminhtml\Calendars')->setData($dataSend)->setTemplate('Daytours_Bookingsystem::catalog/product/calendars/form_two.phtml')->toHtml();
        }

		$response = array('html_calendar_form'=> $htmlCalendarForm);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}