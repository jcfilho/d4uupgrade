<?php
 
namespace Magebay\Bookingsystem\Controller\Marketplace;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Model\BookingimagesFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;

class DeleteRoom extends Action
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
	protected $_facilitiesFactory;
	protected $_optionsFactory;
	protected $_discountsFactory;
	protected $_calendarsFactory;
	protected $_bookingimagesFactory;
	protected $_bookingordersFactory;
	function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		RoomsFactory $roomsFactory,
		FacilitiesFactory $facilitiesFactory,
		OptionsFactory $optionsFactory,
		DiscountsFactory $discountsFactory,
		CalendarsFactory $calendarsFactory,
		BookingimagesFactory $bookingimagesFactory,
		BookingordersFactory $bookingordersFactory
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
        $this->_optionsFactory = $optionsFactory;
        $this->_discountsFactory = $discountsFactory;
        $this->_calendarsFactory = $calendarsFactory;
        $this->_bookingimagesFactory = $bookingimagesFactory;
        $this->_bookingordersFactory = $bookingordersFactory;
	}
	public function execute()
	{
		$status = false;
		$messageStatus = '';
		$roomBookingId  = $this->_request->getParam('room_booking_id',0);
		$roomId = $this->_request->getParam('room_id',0);
		$storeId = $this->_request->getParam('store_id',0);
		$resultJson = $this->_resultJsonFactory->create();
		$params = $this->_request->getParams();
		if($roomId > 0)
		{
			try {
				$model = $this->_roomsFactory->create();
				$facilitiesModel = $this->_facilitiesFactory->create();
				$optionsModel = $this->_optionsFactory->create();
				$discountsModel = $this->_discountsFactory->create();
				$calendarsModel = $this->_calendarsFactory->create();
				$bookingimagesModel = $this->_bookingimagesFactory->create();
				$bookingordersModel = $this->_bookingordersFactory->create();
				$facilitiesModel->deleteBookingFromFacilities($roomId,'room');
				//delete options
				$optionsModel->deleteAddonOptions($roomId,'hotel');
				//delete discounts
				$discountsModel->deleteDiscounts($roomId,'hotel');
				//delete calendars
				$calendarsModel->deleteCalendars($roomId,'hotel');
				$bookingimagesModel->deleteBkImages($roomId,'room');
				$bookingordersModel->deleteBkOrders($roomId,1);
				$model->setId($roomId)->delete();
				$status = true;
				$messageStatus = __('Room have been deleted sucssess!');
			} catch (\Exception $e) {
				$messageStatus = $e->getMessage();
			}
		}
		$htmlRooms = $this->_view->getLayout()->createBlock('Magebay\Bookingsystem\Block\Marketplace\RoomsPopup')->setData(array('booking_id'=>$roomBookingId,'bk_store_id'=>$storeId))->setTemplate('Magebay_Bookingsystem::marketplace/rooms.phtml')->toHtml();
		$response = array('html_rooms'=>$htmlRooms,'status'=>$status);
		return $resultJson->setData($response);
	}
}