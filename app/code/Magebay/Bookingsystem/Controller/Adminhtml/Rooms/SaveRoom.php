<?php
 
namespace Magebay\Bookingsystem\Controller\Adminhtml\Rooms;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;

class SaveRoom extends Action
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
     * @var \Magebay\Bookingsystem\Model\RoomsFactory;
     */
	 protected $_roomsFactory;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		RoomsFactory $roomsFactory
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_roomsFactory = $roomsFactory;
	}
	public function execute()
	{
		$status = false;
		$messageStatus = '';
		$roomBookingId  = $this->_request->getParam('room_booking_id',0);
		$roomId = $this->_request->getParam('room_id',0);;
		$storeId = $this->_request->getParam('store_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$params = $this->_request->getParams();
		$model = $this->_roomsFactory->create();
		try {
				$arDisableDays = isset($params['disable_days']) ? $params['disable_days'] : array();
				if(count($arDisableDays))
				{
					$params['disable_days'] = implode(',',$arDisableDays);
				}
				else
				{
					$params['disable_days'] = '';
				}
				$roomId = $model->saveBkRoom($params);
				$status = true;
				$messageStatus = __('You have saved Data');
			} catch (\Exception $e) {
				$messageStatus = $e->getMessage();
		}
		$htmlRoom = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\RoomsPopup')->setData(array('bk_room_id'=>$roomId,'bk_store_id'=>$storeId,'room_booking_id'=>$roomBookingId))->setTemplate('Magebay_Bookingsystem::catalog/product/popup_rooms.phtml')->toHtml();
		$htmlRooms = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Adminhtml\RoomsPopup')->setData(array('booking_id'=>$roomBookingId,'bk_store_id'=>$storeId))->setTemplate('Magebay_Bookingsystem::catalog/product/rooms.phtml')->toHtml();
		$response = array('html_room'=> $htmlRoom,'html_rooms'=>$htmlRooms,'status'=>$status);
		return $resultJson->setData($response);
	}
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebay_Bookingsystem::update_booking');
    }
}