<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;

class Intervalhours extends AbstractModel
{
    /**
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory
     * */
	protected $_calendarsFactory;
	protected $_bkHelperDate;
	public function __construct(
		CalendarsFactory $calendarsFactory,
        BkHelperDate $bkHelperDate,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
		$this->_calendarsFactory = $calendarsFactory;
		$this->_bkHelperDate = $bkHelperDate;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
   /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Intervalhours');
    }
	function getIntervals($bookingId,$strDate = '',$status = 0)
	{
        $collection =  $this->getCollection();
		if($strDate != '')
		{
			$collection->addFieldToFilter('intervalhours_booking_id',$bookingId)
                    ->addFieldToFilter('intervalhours_check_in',array('lteq'=>$strDate))
					->addFieldToFilter('intervalhours_check_out',array('gteq'=>$strDate));
            if($status > 0)
            {
                $collection->addFieldToFilter('intervalhours_status',$status);
            }
		}
		else
        {
            $collection = $this->getCollection()
                ->addFieldToFilter('intervalhours_booking_id',$bookingId);
            if($status > 0)
            {
                $collection->addFieldToFilter('intervalhours_status',$status);
            }
        }
		$collection->setOrder('intervalhours_booking_time','ASC');
		$dataIntervals = array();
		if(count($collection))
		{
			$dataIntervals = $collection->getData();
		}
		if(!count($dataIntervals) && $strDate != '')
		{
			$collection = $this->getCollection()
				->addFieldToFilter('intervalhours_booking_id',$bookingId);
            if($status > 0)
            {
                $collection->addFieldToFilter('intervalhours_status',$status);
            }
			$collection->getSelect('intervalhours_check_in IS NULL');
			$collection->setOrder('intervalhours_booking_time','ASC');
				$dataIntervals = $collection->getData();
		}
		return $dataIntervals;
	}
	/*
	 * Get interval
	 *
	 * */
	function getInterval($bookingId,$strTime,$strDate = '')
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('intervalhours_booking_time',$strTime)
            ->addFieldToFilter('intervalhours_booking_id',$bookingId);
            if($strDate != '')
            {
                $collection->addFieldToFilter('intervalhours_check_in',array('lteq'=>$strDate))
                      ->addFieldToFilter('intervalhours_check_out',array('gteq'=>$strDate));
            }
            else
            {
                $collection->addFieldToFilter('intervalhours_check_in', array('null'=>1));
            }
        $interval = null;
        if(count($collection))
        {
            $interval = $collection->getFirstItem();
        }
        return $interval;
    }
    /*
     * get base time slot
     * @param int $bookingId
     * @param string $checkIn
     * @param string $checkout
     * @return array
     * */
    function getBaseTimeSlots($bookingId,$checkIn,$checkOut,$arSelect = array('*'))
    {
        $collection = $this->getCollection()
            ->addFieldToSelect($arSelect)
            ->addFieldToFilter('intervalhours_booking_id',$bookingId);
        if($checkIn == '')
        {
            $collection->getSelect()->where('intervalhours_check_in IS NULL');
        }
        else
        {
            $collection->addFieldToFilter('intervalhours_check_in',$checkIn)
            ->addFieldToFilter('intervalhours_check_out',$checkOut);
        }

        return $collection;
    }
	/*
	* save hours intervals, only for booking_time = 3
	* @param , $bookingId, $params
	* @return $this
	*/
	function saveIntervals($params,$bookingId)
	{
	    $okDefault = isset($params['booking_type_intevals']) ? $params['booking_type_intevals'] : 0;
		if($params['booking_time'] == 3 && $okDefault == 1)
		{
			//check update intervals
			$okUpdate = false;
			$updatePrice = $params['booking_temp_check_price_update'];
			//get data from calendar
			if($updatePrice == 1)
			{
				$okUpdate = true;
			}
			if($okUpdate)
			{
				//delete old data
				$hoursCollection = $this->getCollection()
						->addFieldToFilter('intervalhours_booking_id',$bookingId);
				$hoursInterIds = array();
				if(count($hoursCollection))
				{
					foreach($hoursCollection as $hourCollection)
					{
						// $this->setId($hourCollection->getId())->delete();
						$hoursInterIds[] = $hourCollection->getId();
					}
				}
				//get data from calendar
				$arrayseletct = array('calendar_startdate','calendar_enddate','calendar_qty');
				$conditions = array('calendar_booking_type'=>'per_day');
				$calendarModel = $this->_calendarsFactory->create();
				$bookingCalendars = $calendarModel->getBkCurrentCalendarsById($bookingId,$arrayseletct,$conditions);
				// \Zend_debug::dump($bookingCalendars->getData());
				
				//add new data
				if(count($bookingCalendars))
				{
					foreach($bookingCalendars as $bookingCalendar)
					{
						$checkIn = $bookingCalendar->getCalendarStartdate();
						$checkOut = $bookingCalendar->getCalendarEnddate();
						$quantity = $bookingCalendar->getCalendarQty();
						$arServiceStart = $params['booking_service_start'];
						$arServiceEnd = $params['booking_service_end'];
						$hourStart = $arServiceStart['type'] == 2 ? ($arServiceStart['hour'] + 12) : $arServiceStart['hour'];
						$hourFinish = $arServiceEnd['type'] == 2 ? ($arServiceEnd['hour'] + 12) : $arServiceEnd['hour'];
						$minuteStart = $arServiceStart['minute'];
						$bookingTimeSlot = $params['booking_time_slot'];
						$bookingTimeBuffer = $params['booking_time_buffer'];
						$totalHour = $hourFinish - $hourStart;
						$tempMinute1 = 0;
						$totalMinute = 0;
						if($arServiceStart['minute'] > 0)
						{
							$tempMinute1 = 60 - $arServiceStart['minute'];
						}
						$tempMinute2 =  $arServiceEnd['minute'];
						if($totalHour > 0)
						{
							if($arServiceStart['minute'] > 0)
							{
								$totalHour--;
							}
							$totalMinute = $totalHour  * 60 + $tempMinute1 + $tempMinute2;
						}
						elseif($totalHour == 0)
						{
							$totalMinute = $arServiceEnd['minute'] - $arServiceStart['minute'];
						}
						if($bookingTimeSlot > 0 && $totalMinute > 0)
						{
							$intervalsHours = $this->createIntervals($totalMinute,$hourStart,$arServiceStart['minute'],$bookingTimeSlot,$bookingTimeBuffer);
							
							if(count($intervalsHours))
							{
								foreach($intervalsHours as $intervalsHour)
								{
									$intervalsHour['start_hour'] = $intervalsHour['start_hour'] > 9 ? $intervalsHour['start_hour'] : '0'.$intervalsHour['start_hour'];
									$intervalsHour['start_minute'] = $intervalsHour['start_minute'] > 9 ? $intervalsHour['start_minute'] : '0'.$intervalsHour['start_minute'];
									$intervalsHour['finish_hour'] = $intervalsHour['finish_hour'] > 9 ? $intervalsHour['finish_hour'] : '0'.$intervalsHour['finish_hour'];
									$intervalsHour['finish_minute'] = $intervalsHour['finish_minute'] > 9 ? $intervalsHour['finish_minute'] : '0'.$intervalsHour['finish_minute'];
									$tempStr = $intervalsHour['start_hour'].'_'.$intervalsHour['start_minute'].'_'.$intervalsHour['finish_hour'].'_'.$intervalsHour['finish_minute'];
									$dataSave = array(
										'intervalhours_booking_id'=>$bookingId,
										'intervalhours_quantity'=>$quantity,
										'intervalhours_booking_time'=>$tempStr,
										'intervalhours_check_in'=>$checkIn,
										'intervalhours_check_out'=>$checkOut,
									);
									// \Zend_debug::dump($dataSave);
									$this->setData($dataSave)->save();
								}
							}
						}
					}
				}
				if(count($hoursInterIds))
				{
					foreach($hoursInterIds as $hoursInterId)
					{
						$this->load($hoursInterId)->delete();
					}
				}
				//exit();
			}
		}
	}
	function getInervalsQty($bookingId,$strDate,$strInterTime)
	{
		$interval = null;
		if(trim($strInterTime) != '')
		{
			$collection = $this->getCollection()
				->addFieldToFilter('intervalhours_booking_id',$bookingId)
				->addFieldToFilter('intervalhours_booking_time',$strInterTime)
				->addFieldToFilter('intervalhours_check_in',array('lteq'=>$strDate))
				->addFieldToFilter('intervalhours_check_out',array('gteq'=>$strDate));
			$collect = $collection->getFirstItem();
			if($collect->getId())
			{
				$interval = $collect;
			}
			else
			{
				$collection = $this->getCollection()
					->addFieldToFilter('intervalhours_booking_id',$bookingId)
					->addFieldToFilter('intervalhours_booking_time',$strInterTime);
				$collection->getSelect()->where('intervalhours_check_in IS NULL');
					$collect = $collection->getFirstItem();
					$interval = $collect;
			}
		}
		return $interval;
	}
	function createIntervals($totalMinute,$hourStart,$minuteStart,$bookingTimeSlot,$bookingTimeBuffer)
	{
		$arrayIntervals = array();
		$i = 0;
		$tempHour = $hourStart;
		$tempMinute = $minuteStart;
		while($totalMinute > 0)
		{
			if($i > 0)
			{
				$tempMinute = $tempMinute + $bookingTimeBuffer;
				if($tempMinute >= 60)
				{
					$mHour = floor($tempMinute / 60);
					$tempMinute = $tempMinute - ($mHour * 60);
					$tempHour += $mHour;
				}
			}
			if($totalMinute < $bookingTimeSlot)
			{
				break;
			}
			$arrayIntervals[$i]['start_hour'] = $tempHour;
			$arrayIntervals[$i]['start_minute'] = $tempMinute;
			$tempMinute = $tempMinute + $bookingTimeSlot;
			if($tempMinute >= 60)
			{
				$mHour = floor($tempMinute / 60);
				$tempMinute = $tempMinute - ($mHour * 60);
				$tempHour += $mHour;
			}
			$arrayIntervals[$i]['finish_hour'] = $tempHour;
			$arrayIntervals[$i]['finish_minute'] = $tempMinute;
			$totalMinute = $totalMinute - ($bookingTimeSlot + $bookingTimeBuffer);
			$i++;
		}
		return $arrayIntervals;
	}
	function deleteIntervalsHours($bookingId)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('intervalhours_booking_id',$bookingId);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}
	}
	/*
	 * Save time slot
	 * @return $this
	 * */
	function  saveTimeSlots($bookingId, $params,$calendarId,$newCheckIn,$newCheckOut,$bkStore = true)
    {
        $totalQty = 0;
        $minPrice = 0;
        $okSave = true;
        $dataItems = array();
        $checkIn = '';
        $checkOut = '';
        $formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date',$bkStore);
        if($newCheckIn != '' && $this->_bkHelperDate->validateBkDate($newCheckIn,$formatDate))
        {
            $checkIn = $this->_bkHelperDate->convertFormatDate($newCheckIn,$bkStore);
        }
        if($newCheckOut != '' && $this->_bkHelperDate->validateBkDate($newCheckOut,$formatDate))
        {
            $checkOut = $this->_bkHelperDate->convertFormatDate($newCheckOut,$bkStore);
        }
        if(count($params))
        {
            $startDate = '';
            $endDate = '';
            $calendarModel = $this->_calendarsFactory->create();
            $calendar = $calendarModel->load($calendarId);
            if($calendar && $calendar->getId())
            {
                $startDate = $calendar->getCalendarStartdate();
                $endDate = $calendar->getCalendarEnddate();
            }
            //get current data
            $delIds = array();
            $currentCollection = $this->getCollection()
                    ->addFieldToSelect(array('intervalhours_id'))
                    ->addFieldToFilter('intervalhours_booking_id',$bookingId);
            if($startDate != '')
            {
                $currentCollection->addFieldToFilter('intervalhours_check_in',$startDate)
                ->addFieldToFilter('intervalhours_check_out',$endDate);
            }
            else
            {
                $currentCollection->addFieldToFilter('intervalhours_check_in', array('null'=>1));
            }
            if(count($currentCollection))
            {
                foreach ($currentCollection as $currentCollect)
                {
                    //$this->setId($currentCollect->getId())->delete();
                    $delIds[] = $currentCollect->getId();
                }
            }
            $maxTime = 0;
            $i = 0;
            foreach ($params as $param)
            {
                $intervalTime = '';
                $param['start_time'] = '';
                if($param['start_time_hour'] != '' && $param['start_time_minute'] != '' )
                {
                    $param['start_time_hour'] = $param['start_time_hour'] < 10 ? '0'.$param['start_time_hour'] : $param['start_time_hour'];
                    //$param['start_time_minute'] = (int)$param['start_time_minute'] == 0 ? '0'.$param['start_time_minute'] : $param['start_time_minute'];
                    $param['start_time'] = $param['start_time_hour'].':'.$param['start_time_minute'];
                }
                $param['end_time'] = '';
                if($param['end_time_hour'] != '' && $param['end_time_minute'] != '' )
                {
                    $param['end_time_hour'] = $param['end_time_hour'] < 10 ? '0'.$param['end_time_hour'] : $param['end_time_hour'];
                    //$param['end_time_minute'] = (int)$param['end_time_minute'] == 0 ? '0'.$param['end_time_minute'] : $param['end_time_minute'];
                    $param['end_time'] = $param['end_time_hour'].':'.$param['end_time_minute'];
                }
                if($param['start_time'] != '' && $param['end_time'] != '')
                {
                    //echo $param['start_time'];
                    $txtServiceStart = strtotime($param['start_time'].":00");
                    $txtServiceEnd = strtotime($param['end_time'].":00");
                    //echo $param['start_time'].":00";
                    //echo $txtServiceStart . ' - ' .$txtServiceEnd;
                    if($param['end_time'] == '0:00' || $txtServiceStart < $txtServiceEnd)
                    {
                        $intervalTime = $param['start_time'].'_'.$param['end_time'];
                        $intervalTime = str_replace(':','_',$intervalTime);
                        //echo $intervalTime;
                    }
                    else
                    {
                        $okSave = false;
                        break;
                    }
                    if($maxTime > $txtServiceStart)
                    {
                        $okSave = false;
                        break;
                    }
                    $maxTime = $txtServiceEnd;
                    if($i == 0)
                    {
                        $minPrice = $param['intervalhours_price'];
                    }
                    elseif($minPrice > $param['intervalhours_price'])
                    {
                        $minPrice = $param['intervalhours_price'];
                    }
                    $i++;
                }
                $param['intervalhours_booking_time'] = $intervalTime;
                $param['intervalhours_booking_id'] = $bookingId;
                $param['intervalhours_check_in'] = $checkIn;
                $param['intervalhours_check_out'] = $checkOut;
                $param['intervalhours_status'] = 1;
                //print_r($param);
                $totalQty += $param['intervalhours_quantity'];
                $dataItems[] = $param;
            }
            if($okSave)
            {
                if(count($dataItems))
                {
                    foreach ($dataItems as $dataItem)
                    {
                        $this->setData($dataItem)->save();
                    }
                }
                if(count($delIds))
                {
                    foreach ($delIds as $delId)
                    {
                        $this->setId($delId)->delete();
                    }
                }
            }
            else
            {
                $totalQty = 0;
            }

        }
        return array('min_price'=>$minPrice,'total_qty'=>$totalQty);
    }
}