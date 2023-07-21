<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Facilities;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class LoadBookings extends Action
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
     * @var Magento\Backend\Model\Auth\Session;
     */
	protected $_backendSession;
	/**
     * 
     * @var Magebay\Bookingsystem\Model\OptionsFactory;
     */
	 protected $_optionsFactory;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
	}
	public function execute()
	{
		$storeId = $this->_request->getParam('store_id',0);
		$bookingType = $this->_request->getParam('booking_type','per_day');
		$bookingIds = $this->_request->getParam('booking_ids','');
		$dataSend = array(
			'booking_type'=>$bookingType,
			'bk_store_id'=>$storeId,
			'bk_booking_ids'=>$bookingIds
		);
		$htmlBookings = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\Facilities')->setData($dataSend)->setTemplate('Magebay_Bookingsystem::facility/form/booking-items.phtml')->toHtml();
		$response = array('html_bookings'=> $htmlBookings );
		$resultJson = $this->_resultJsonFactory->create();
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::add_facility');
    }
}