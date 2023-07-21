<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class Facilities extends Action
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
		$storeId = $this->_request->getParam('store_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$htmlFacilities = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\Booking')->setData(array('booking_type'=>$bookingType,'booking_id'=>$bookingId,'bk_store_id'=>$storeId))->setTemplate('Magebay_Bookingsystem::marketplace/facilities.phtml')->toHtml();
		$response = array('html_facilities'=> $htmlFacilities);
		return $resultJson->setData($response);
	}
}