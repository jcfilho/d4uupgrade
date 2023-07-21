<?php

namespace Magebay\Bookingsystem\Block\Adminhtml;
 
use Magento\Backend\Block\Template;
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\RoomtypesFactory;
use Magento\Framework\View\Element\FormKey;
use Magebay\Bookingsystem\Model\BookingimagesFactory;
use Magebay\Bookingsystem\Model\Image as ImageModel;

class RoomsPopup extends Template
{
	/**
     * @param \Magebay\Bookingsystem\Helper\BkText
     * 
     */
	protected $_bkHelperText;
	/**
     * @param \Magebay\Bookingsystem\Model\Rooms
     * 
     */
	 protected $_roomsFactory;
	 /**
     * @param \Magebay\Bookingsystem\Model\RoomtypesFactory
     * 
     */
	 protected $_roomtypesFactory;
	 /**
     * Image images
     *
     * @var  Magebay\Bookingsystem\Model\Bookingimages;
     */
	protected $_bookingimages;
	/**
     * Image model
     *
     * @var  Magebay\Bookingsystem\Model\Image;
     */
	protected $_imageModel;
	 protected $_bkFormKey;
	function __construct(
		\Magento\Backend\Block\Widget\Context $context,
		BkHelperText $bkHelperText,
		RoomsFactory $roomsFactory,
		RoomtypesFactory $roomtypesFactory,
		FormKey $bkFormKey,
		BookingimagesFactory $bookingimages,
		ImageModel $ImageModel,
		array $data = []
	)
	{
		$this->_bkHelperText = $bkHelperText;
		$this->_roomsFactory = $roomsFactory;
		$this->_roomtypesFactory = $roomtypesFactory;
		$this->_bkFormKey = $bkFormKey;
		$this->_bookingimages = $bookingimages;
		$this->_imageModel = $ImageModel;
		parent::__construct($context, $data);
	}
	function getBkArTextByStoreId($text,$textTrans,$storeId = 0)
	{
		$storeId = $this->getBkStoreId();
		return $this->_bkHelperText->getBkArTextByStore($text,$textTrans,$storeId);
	}
	/**
	* get text translate by storeId
	* @param string $text, json $textTrans
	* @return string $text
	**/
	function getTextTranslate($text,$textTrans,$storeId = 0)
	{
		return $this->_bkHelperText->showTranslateText($text,$textTrans,$storeId);
	}
	/**
	* get rooms by $bookingId
	* @return $items
	**/
	function getBkRooms()
	{
		$bookingId = $this->getBookingId(); //productId
		$model = $this->_roomsFactory->create();
		$collection = $model->getBkRoomsById($bookingId);
		return $collection;
	}
	/**
	* get room by $roomId
	* @return $item
	**/
	function getBkRoom()
	{
		$roomId = $this->getBkRoomId(); //roomId
		$model = $this->_roomsFactory->create();
		$room = $model->load($roomId);
		return $room;
	}
	/**
	* get all roomtypes
	* @return array $data
	**/
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
	/**
	* get all room in hotel without current room
	* @return array $roomtType of hotel
	**/
	function getRoomTypeOfHotel($bookingId,$roomId)
	{
		$model = $this->_roomsFactory->create();
		$collection = $model->getBkRoomsById($bookingId);
		$arrayData = array();
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				if($collect->getId() == $roomId)
				{
					continue;
				}
				$arrayData[] = $collect->getRoomType();
			}
		}
		return $arrayData;
	}
	/**
	* get image for room
	**/
	function getBkRoomImages()
	{
		$modelImages = $this->_bookingimages->create();
		$dataId = $this->getBkDataId();
		$dataType = $this->getBkDataType();
		return $modelImages->getBkImages($dataId,$dataType);
	}
	function getBkBaseUrl()
	{
		return $this->_imageModel->getBaseUrl();
	}
	/**
	* get url for ajax
	**/
	function getArrayAjaxUrl()
	{
		$urlSaveRoom = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/rooms/SaveRoom');
		$urlEditRoom = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/rooms/setupRoom');
		$urlDellRoom = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/rooms/deleteRoom');
		$newOptionUrl = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/booking/newOption');
		$newDiscountUrl = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/booking/NewDiscount');
		$urlUploadImage = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/rooms/uploadImage');
		$urlDeleteImage = $this->_bkHelperText->getBkAdminAjaxUrl('bookingsystem/rooms/deleteImage');
		return array(
			'url_save_room' => $urlSaveRoom,
			'url_edit_room' => $urlEditRoom,
			'url_dell_room' => $urlDellRoom,
			'url_new_option'=>$newOptionUrl,
			'new_discount_url'=>$newDiscountUrl,
			'url_upload_image'=>$urlUploadImage,
			'url_delete_mage'=>$urlDeleteImage,
		);
	}
	function getBkFormKey()
	{
		return $this->_bkFormKey->getFormKey();
	}
}