<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;
 
class Facilities extends AbstractModel
{
	public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Facilities');
    }
	/**
	* get facilities
	**/
	function getBkFacilities($arrayseletct = array('*'),$conditions = array(),$orderBy = 'facility_position',$sortOrder = 'DESC',$limit = 0,$curPage = 1)
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
	* get facilities by type
	**/
	function getBkFacilitiesById($bookingId,$arrayseletct = array('*'),$conditions = array(),$orderBy = 'facility_position',$sortOrder = 'DESC',$limit = 0,$curPage = 1)
	{
		$collection = $this->getBkFacilities($arrayseletct,$conditions,$orderBy,$sortOrder,$limit,$curPage);
		$collection->addFieldToFilter('facility_booking_ids',array('finset'=>$bookingId));
		return $collection;
	}
	function getBkFacility($facilityId)
	{
		if($facilityId > 0)
		{
			$facility = $this->load($facilityId);
			if($facility->getId())
			{
				return $facility;
			}
		}
		return null;
	}
	function saveBkFacilities($params,$bookingId,$bookingType = 'per_day')
	{
		$conditions = array('facility_booking_type'=>$bookingType);
		$facilities = $this->getBkFacilitiesById($bookingId,array('facility_booking_ids'),$conditions);
		$facilityIds = array();
		foreach($facilities as $facility)
		{
			$facilityIds[$facility->getId()] = $facility->getId();
		}
		if(count($params))
		{
			foreach($params as $key => $value)
			{
				if(array_key_exists($value,$facilityIds))
				{
					unset($facilityIds[$value]);
				}
				//add new
				else
				{
					$newFacility = $this->getBkFacility($value);
					if($newFacility)
					{
						$strBookingIds = $newFacility->getFacilityBookingIds();
						if($strBookingIds != '')
						{
							$strBookingIds .= ','.$bookingId;
						}
						else
						{
							$strBookingIds = $bookingId;
						}
						$this->setFacilityBookingIds($strBookingIds)->setId($value)->save();
					}
				}
			}
		}
		//delete product id from facilities
		 if(count($facilityIds))
		{
			foreach($facilities as $facility1)
			{
				if(array_key_exists($facility1->getId(),$facilityIds))
				{
					$strBookingIds = $facility1->getBookingIds();
					$arBookingIds = explode(',',$strBookingIds);
					//unset product id from service
					$arBookingIds = array_diff($arBookingIds,array($bookingId));
					$strBookingIds = implode(',',$arBookingIds);
					$this->setFacilityBookingIds($strBookingIds)->setId($facility1->getId())->save();
				}
			}
		} 
	}
	/**
	* delete booking from service by bookingId
	* @param int $bookingId
	* return $this
	**/
	function deleteBookingFromFacilities($bookingId,$bookingType = 'per_day')
	{
		$collection = $this->getCollection();
		$collection->addFieldToFilter('facility_booking_ids',array('finset'=>$bookingId));
		$collection->addFieldToFilter('facility_booking_type',$bookingType);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$bookingIds = $collect->getFacilityBookingIds();
				if($bookingIds != '')
				{
					$bookingIds = explode(',',$bookingIds);
					$bookingIds = array_diff($bookingIds,array($bookingId));
					$bookingIds = implode(',',$bookingIds);
					$this->setFacilityBookingIds($bookingIds)->setId($collect->getId())->save();
				}
			}
		}
	}
}