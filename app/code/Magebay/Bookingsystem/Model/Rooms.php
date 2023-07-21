<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magebay\Bookingsystem\Helper\BkText as BkHelperText;
use Magebay\Bookingsystem\Model\Facilities;
use Magebay\Bookingsystem\Model\Roomtypes;
use Magebay\Bookingsystem\Model\Options as BkOptions;
use Magebay\Bookingsystem\Model\Discounts as BkDiscounts;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;

class Rooms extends AbstractModel
{
	protected $_bkHelperText;
	protected $_facilities;
	protected $_bkOptions;
	protected $_roomtypes;
	protected $_bkDiscounts;
	public function __construct(
		BkHelperText $bkHelperText,
		Facilities $facilities,
		BkOptions $bkOptions,
		Roomtypes $roomtypes,
		BkDiscounts $bkDiscounts,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
		$this->_bkHelperText = $bkHelperText;
		$this->_facilities = $facilities;
		$this->_bkOptions = $bkOptions;
		$this->_roomtypes = $roomtypes;
		$this->_bkDiscounts = $bkDiscounts;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Rooms');
    }
	/**
	* get All room 
	* @return array $items
	**/
	function getBkRooms($arrayseletct = array('*'),$conditions = array(),$orderBy = 'room_position',$sortOrder = 'ASC',$limit = 0,$curPage = 1)
	{
		$collection = $this->getCollection();
		$collection->addFieldToSelect($arrayseletct);
		if(count($conditions))
		{
			foreach($conditions as $key => $condition)
			{
				$collection->addFieldToFilter($key,$condition);
			}
		}
		if($limit > 0)
		{
			$collection->setPageSize($limit);
		}
		$collection->setCurPage($curPage);
		$collection->setOrder($orderBy,$sortOrder);
		return $collection;
	}
	/**
	* get room 
	* @return $item
	**/
	function getBkRoom($id)
	{
		$room = $this->load($id);
		if($room->getId())
		{
			return $room;
		}
		return null;
	}
	/**
	* get All room by bookingId
	* @return array $items
	**/
	function getBkRoomsById($bookingId,$arrayseletct = array('*'),$conditions = array(),$orderBy = 'room_position',$sortOrder = 'ASC',$limit = 0,$curPage = 1)
	{
		$collection = $this->getBkRooms($arrayseletct,$conditions,$orderBy,$sortOrder,$limit,$curPage);
		$collection->addFieldToFilter('room_booking_id',$bookingId);
		return $collection;
	}
	/**
	* save room
	* @return $this
	**/
	function saveBkRoom($params)
	{
		$description = isset($params['room_description']) ? $params['room_description'] : '';
		$storeId = isset($params['store_id']) ? $params['store_id'] : 0;
		$isNew = true;
		$arCurrentDes = array();
		$useDefault = 1;
		$defaultDes = '';
		$tempRoomId = isset($params['room_id']) ? $params['room_id'] : 0;
		$params['room_max_adults'] = (int)$params['room_max_adults'];
		$params['room_max_children'] = (int)$params['room_max_children'];
		$params['room_minimum_day'] = (int)$params['room_minimum_day'];
		$params['room_maximum_day'] = (int)$params['room_maximum_day'];
		$params['room_position'] = (int)$params['room_position'];
		$params['room_status'] = (int)$params['room_status'] > 0 ? $params['room_status'] : 1;
		if($params['room_id'] == 0)
		{
			unset($params['room_id']);
			
		}
		else
		{
			$roomModel = $this->load($params['room_id']);
			if($roomModel->getId())
			{
				$isNew = false;
				$defaultDes = $roomModel->getRoomDescription();
				if($roomModel->getRoomDesTranslate() != '')
				{
					$arCurrentDes = $this->_bkHelperText->getBkJsonDecode($roomModel->getRoomDesTranslate());
				}
			}
		}
		if($storeId > 0)
		{
			unset($params['room_description']);
			if(isset($params['des_use_default']))
			{
				
			}
			else
			{
				$useDefault = 0;
			}
		}
		$arDesTrans = $this->_bkHelperText->getTextTranslate($description,$defaultDes,$storeId,$isNew,$arCurrentDes,$useDefault);
		$desTras = $this->_bkHelperText->getBkJsonEncode($arDesTrans);
		$params['room_des_translate'] = $desTras; 
		$roomTypes = $this->_roomtypes->getCollection()
				->addFieldToFilter('roomtype_status',1);
		$okRoomType = false;
		foreach($roomTypes as $roomType)
		{
			if($roomType->getId() == $params['room_type'])
			{
				$okRoomType = true;
				break;
			}
		}
		if(!$okRoomType)
		{
			throw new \Exception(__('Room type is not collect.'));
			return false;
		}
		try {
				$this->setData($params)->save();
				//save facilities
				$facilitiesParams = isset($params['facilities']) ? $params['facilities'] : array();
				$newRoomId = $this->getId();
				//save bk rooms
				$paramsOptions = isset($params['options']) ? $params['options'] : array(); 
				$this->_bkOptions->saveBkOptions($paramsOptions,$newRoomId,'hotel');
				$paramsDiscount = isset($params['discounts']) ? $params['discounts'] : array(); 
				$this->_bkDiscounts->saveBkDiscounts($paramsDiscount,$newRoomId,'hotel');
				$this->_facilities->saveBkFacilities($facilitiesParams,$newRoomId,'room');
				return $newRoomId;
			} catch (\Exception $e) {
				throw new \Exception($e);
				return false;
		}
	}
	/**
	* @delete $room
	* @param array $roomIds
	* @return $this;
	**/
	function deleteBkRooms($bookingId)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('room_booking_id',$bookingId);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}
	}
}