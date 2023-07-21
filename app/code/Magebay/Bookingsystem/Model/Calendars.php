<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magebay\Bookingsystem\Helper\BkHelperDate;
 
class Calendars extends AbstractModel
{
	/**
     * @param \Magebay\Bookingsystem\Helper\BkHelperDate
     * 
     */
	protected $_bkHelperDate;
	/**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime
     * 
     */
	protected $_date;
	protected $_timeZone;
	public function __construct(
		DateTime $date,
		Timezone $timezone,
		BkHelperDate $bkHelperDate,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
		$this->_date = $date;
		$this->_timeZone = $timezone;
		$this->_bkHelperDate = $bkHelperDate;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Calendars');
    }
	function getBkCalendars($arrayseletct = array('*'),$conditions = array(),$orderBy = 'calendar_startdate',$sortOrder = 'ASC',$limit = 0,$curPage = 1)
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
	/* get currents calendars by product id */
	function getBkCurrentCalendarsById($bookingId,$arrayseletct = array('*'),$conditions = array(),$orderBy = 'calendar_startdate',$sortOrder = 'ASC',$limit = 0,$curPage = 1)
	{
		//$currDate = $this->_date->gmtDate('Y-m-d');
		$intCurrentTime = $this->_timeZone->scopeTimeStamp();
		$currDate = date('Y-m-d',$intCurrentTime);
		$collection = $this->getBkCalendars($arrayseletct,$conditions,$orderBy,$sortOrder,$limit,$curPage);
		$collection->addFieldToFilter('calendar_booking_id',$bookingId)
			->addFieldToFilter(
					array('calendar_enddate','calendar_default_value'),
					array(
						array('gteq'=>$currDate),
						array('eq'=>1)
						)
				);
		return $collection;
	}
	/* get all calendars by product id */
	function getBkCalendarsById($bookingId,$arrayseletct = array('*'),$conditions = array(),$orderBy = 'calendar_startdate',$sortOrder = 'ASC',$limit = 0,$curPage = 1)
	{
		$collection = $this->getBkCalendars($arrayseletct,$conditions,$orderBy,$sortOrder,$limit,$curPage);
		$collection->addFieldToFilter('calendar_booking_id',$bookingId);
		return $collection;
	}
	function getBkCalendar($id)
	{
		$calendar = $this->load($id);
		if($calendar->getId())
		{
			return $calendar;
		}
		return null;
	}
	function saveBkCalendars($params,$bkStore = true)
	{
	    $newId = 0;
		$checkIn = '';
		$checkOut = '';
		$bkHelperDate = $this->_bkHelperDate;
		$formatDate = $bkHelperDate->getFieldSetting('bookingsystem/setting/format_date',$bkStore);
		if(isset($params['item_day_start_date']) && $bkHelperDate->validateBkDate($params['item_day_start_date'],$formatDate))
		{
			$checkIn = $bkHelperDate->convertFormatDate($params['item_day_start_date'],$bkStore);
		}
		if(isset($params['item_day_end_date']) && $bkHelperDate->validateBkDate($params['item_day_start_date'],$formatDate))
		{
			$checkOut = $bkHelperDate->convertFormatDate($params['item_day_end_date'],$bkStore);
		}
		$defaultValue = isset($params['item_day_default_value']) ? 1 : 2;
		$okCalendar = isset($params['ok_calendar']) ? $params['ok_calendar'] : 0;
		$params['item_day_price'] = (isset($params['item_day_price']) &&  $params['item_day_price'] != 0) ? (float)$params['item_day_price'] : NULL;
		$params['item_day_qty'] = (isset($params['item_day_qty']) && $params['item_day_qty'] > 0) ? $params['item_day_qty'] : 1;
		$params['item_day_promo'] = (isset($params['item_day_promo']) && $params['item_day_promo'] != '') ? (float)$params['item_day_promo'] : NULL;
		$extractPerson = isset($params['extract_person']) ? $params['extract_person'] : array();
		$txtExteactPeson = '';
		$customQty = 0;
		if(count($extractPerson))
        {
            $txtExteactPeson = json_encode($extractPerson);
        }
		$dataSave = array(
				'calendar_id'=>$params['calendar_id'],
				'calendar_startdate'=>$checkIn,
				'calendar_enddate'=>$checkOut,
				'calendar_qty'=>$params['item_day_qty'],
				'calendar_status'=>$params['item_day_status'],
				'calendar_price'=>$params['item_day_price'],
				'calendar_promo'=>$params['item_day_promo'],
				'calendar_booking_id'=>$params['booking_id'],
				'description'=>$params['item_day_description'],
				'calendar_default_value'=>$defaultValue,
				'calendar_booking_type'=>$params['booking_type'],
				'extract_persons'=>$txtExteactPeson,
		);
		if($okCalendar == 1)
		{
			//get current data by id
			if($checkIn == '' || $checkOut == '')
			{
				throw new \Exception(__('Your data are not collect, Please try again'));
				return;
			}
			if($params['calendar_id'] > 0)
			{
				$itemCalendar = $this->load($params['calendar_id']);
				if($itemCalendar->getId())
				{
					$dataBefore = array();
					$dataAfter = array();
					$currentCheckIn = $itemCalendar->getCalendarStartdate();
					$currentCheckOut = $itemCalendar->getCalendarEnddate();
					if(strtotime($currentCheckIn) < strtotime($checkIn))
					{
						unset($dataSave['calendar_id']);
						$dataBefore = $itemCalendar->getData();
						$tmpTimeCheckOut = strtotime($checkIn) - (24 * 60 * 60);
						$dataBefore['calendar_enddate'] = date('Y-m-d',$tmpTimeCheckOut);
					}
					if(strtotime($currentCheckOut) > strtotime($checkOut))
					{
						if(isset($dataSave['calendar_id']))
						{
							unset($dataSave['calendar_id']);
						}
						$dataAfter = $itemCalendar->getData();
						$tmpTimeCheckInt = strtotime($checkOut);
						$tmpTimeCheckInt += 24 * 60 * 60;
						$dataAfter['calendar_startdate'] = date('Y-m-d',$tmpTimeCheckInt);
						if(count($dataBefore))
						{
							unset($dataAfter['calendar_id']);
						}
					}
					if(count($dataBefore))
					{
						$this->setData($dataBefore)->save();
					}
					if(count($dataAfter))
					{
						$this->setData($dataAfter)->save();
					}
				}
			}
			if(isset($dataSave['calendar_id']) && $dataSave['calendar_id'] == 0)
			{
				unset($dataSave['calendar_id']);
			}
            $this->setData($dataSave)->save();
            $newId = $this->getId();
		}
		else
		{
			if($dataSave['calendar_id'] == 0)
			{
				unset($dataSave['calendar_id']);
			}
			if($defaultValue == 2 && ($checkIn == '' || $checkOut == ''))
			{
				throw new \Exception(__('Your data are not collect, Please try again'));
			}
			else
			{
                $this->setData($dataSave)->save();
                $newId = $this->getId();
			}
		}
		return $newId;
	}
	/* 
	* get item between days
	* @param int $bookingId, string $strDay
	* return $item
	*/
	function getCalendarBetweenDays($bookingId,$strDay,$bookingType = 'per_day')
	{
		$arrayseletct = array('*');
		$conditions = array();
		$orderBy = 'calendar_default_value';
		$sortOrder = 'DESC';
		$collection = $this->getBkCalendars($arrayseletct,$conditions,$orderBy,$sortOrder);
		$collection->addFieldToFilter('calendar_booking_id',$bookingId);
		$collection->addFieldToFilter('calendar_booking_type',$bookingType);
		$collection->addFieldToFilter(array('calendar_enddate','calendar_default_value'),
											array(
												array('gteq'=>$strDay),
												array('eq'=>1)
											)
										);
		$collection->addFieldToFilter(array('calendar_startdate','calendar_default_value'),
											array(
												array('lteq'=>$strDay),
												array('eq'=>1)
											)
										);
		return $collection->getFirstItem();
	}
	function deleteCalendars($bookingId,$bookingType)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('calendar_booking_id',$bookingId)
			->addFieldToFilter('calendar_booking_type',$bookingType);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}
	}
    function getBlockCalendars($bookingId)
    {
        $intCurrentTime = $this->_timeZone->scopeTimeStamp();
        $currDate = date('Y-m-d',$intCurrentTime);
        $collection = $this->getCollection()
            ->addFieldToSelect(array('calendar_startdate','calendar_enddate'))
            ->addFieldToFilter('calendar_status','block')
            ->addFieldToFilter('calendar_booking_id',$bookingId)
            ->addFieldToFilter('calendar_enddate',array('gteq'=>$currDate));
        return $collection;
    }
}