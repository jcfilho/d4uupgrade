<?php
 
namespace Daytours\Bookingsystem\Controller\Adminhtml\Booking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class SetupRentPrice extends Action
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
		$bookingId = $this->_request->getParam('booking_id',0);
        $bookingType = $this->_request->getParam('booking_type','per_day');
		$bookingTime = $this->_request->getParam('booking_time',1);
		$resultJson = $this->_resultJsonFactory->create();
		$htmlRentPrice = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\RentPopup')
            ->setData(array(
                'booking_type'  =>  $bookingType,
                'booking_id'    =>  $bookingId,
                'booking_time'  =>  $bookingTime))
            ->setTemplate('Daytours_Bookingsystem::catalog/product/rent_price.phtml')->toHtml();
		$response = array('html_rent_price'=> $htmlRentPrice);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}