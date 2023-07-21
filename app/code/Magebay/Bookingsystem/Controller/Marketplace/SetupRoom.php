<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class SetupRoom extends Action
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
		$roomId = $this->_request->getParam('bk_room_id',0);
		$storeId = $this->_request->getParam('store_id',0);
		$roomBookingId = $this->_request->getParam('room_booking_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$htmlRoom = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\RoomsPopup')->setData(array('bk_room_id'=>$roomId,'bk_store_id'=>$storeId,'room_booking_id'=>$roomBookingId))->setTemplate('Magebay_Bookingsystem::marketplace/popup_rooms.phtml')->toHtml();
		$response = array('html_room'=> $htmlRoom);
		return $resultJson->setData($response);
	}
}