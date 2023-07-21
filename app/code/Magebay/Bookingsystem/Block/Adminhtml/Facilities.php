<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Widget\Grid\Container;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\RoomtypesFactory;
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;
use Magebay\Bookingsystem\Model\RoomsFactory;

class Facilities extends Container
{
	/**
     * @param \Magebay\Bookingsystem\Model\BookingsFactory
     * 
     */
	protected $_bookingsFactory;
	/**
     * @param \Magebay\Bookingsystem\Model\RoomtypesFactory
     * 
     */
	protected $_roomtypesFactory;
	 /**
     * @param \Magebay\Bookingsystem\Helper\BkText
     * 
     */
	protected $_roomsFactory;
	 /**
     * @param \Magebay\Bookingsystem\Helper\BkText
     * 
     */
	protected $_bkHelperText;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		BookingsFactory $bookingsFactory,
		RoomtypesFactory $roomtypesFactory,
		RoomsFactory $roomsFactory,
		BkHelperText $bkHelperText,
		array $data = []
	)
	{
		$this->_bookingsFactory = $bookingsFactory;
		$this->_roomtypesFactory = $roomtypesFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_bkHelperText = $bkHelperText;
		parent::__construct($context, $data);
	}
   /**
     * Constructor
     *
     * @return void
     */
   protected function _construct()
    {
        $this->_controller = 'adminhtml_facilities';
        $this->_blockGroup = 'Magebay_Bookingsystem';
        $this->_headerText = __('Manage Facilities');
        $this->_addButtonLabel = __('Add News');
        parent::_construct();
    }
	function getBookingsMultiSelect()
	{
		$arrayAttributeSelect = array('*');
		$arAttributeConditions = array();
		$condition = '';
		$bookingType = $this->getBookingType();
		if($bookingType == 'room')
		{
			$bookingType = 'hotel';
		}
		$condition = "booking_type = '{$bookingType}'";
		$model = $this->_bookingsFactory->create();
		$arAttributeConditions['status'] = 1;
		$bookings = $model->getBookings($arrayAttributeSelect,$arAttributeConditions,$condition);
		return $bookings; 
	} 
	function getBkRoomTypes()
	{
		$storeId = $this->getBkStoreId();
		$model = $this->_roomtypesFactory->create();
		$roomtTypes = $model->getCollection()
				->addFieldToFilter('roomtype_status',1);
		$arData = array();
		if(count($roomtTypes))
		{
			foreach($roomtTypes as $roomtType)
			{
				$title = $this->getTextTranslate($roomtType->getRoomtypeTitle(),$roomtType->getRoomtypeTitleTransalte(),$storeId);
				$arData[$roomtType->getId()] = $title;
			}
		}			
		return $arData;
	}
	function getTextTranslate($text,$textTrans,$storeId)
	{
		return $this->_bkHelperText->showTranslateText($text,$textTrans,$storeId);
	}
	/**
	* get room in hotel
	**/
	function getBkRooms($hoteId)
	{
		$model = $this->_roomsFactory->create();
		return $model->getBkRoomsById($hoteId);
	}
	function getBkRequest()
	{
		return $this->_request;
	}
}