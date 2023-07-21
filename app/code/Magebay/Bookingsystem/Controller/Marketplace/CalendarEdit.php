<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session;

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
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
        Session $session
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_customerSession = $session;
	}
	public function execute()
	{
	    $this->_customerSession->setIsUpdateCaledar(1);
		$bookingId = $this->_request->getParam('booking_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$resultJson = $this->_resultJsonFactory->create();
		$dataSend = array(
			'booking_id'=>$bookingId,
			'booking_type'=>$bookingType
		);
		$htmlCalendarForm = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\Calendars')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::marketplace/calendars/form.phtml')->toHtml();
		$response = array('html_calendar_form'=> $htmlCalendarForm);
		return $resultJson->setData($response);
	}
}