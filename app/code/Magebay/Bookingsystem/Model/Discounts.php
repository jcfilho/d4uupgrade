<?php
 
namespace Magebay\Bookingsystem\Model;
 
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Data\Collection\AbstractDb;
class Discounts extends AbstractModel
{
    protected  $_customerSession;
	public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->_customerSession = $customerSession;
    }
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Magebay\Bookingsystem\Model\ResourceModel\Discounts');
    }
	/* 
	* get options by bookingId
	* @param int $bookingId
	* @return array $data
	*/
	function getBkDiscounts($bookingId,$bookingType = 'per_day',$fieldSelect = array('*'),$filedSort = 'discount_priority', $sortOrder = 'ASC')
	{
		$collection = $this->getCollection()
			->addFieldToSelect($fieldSelect)
			->addFieldToFilter('discount_booking_id',$bookingId)
			->addFieldToFilter('discount_booking_type',$bookingType)
			->setOrder($filedSort,$sortOrder);
		return $collection;
	}
	function getBkDiscountItems($bookingId,$formatDate,$intToday,$symboy,$bookingType = 'per_day',$filedSort = 'discount_period',$sortBy = 'ASC')
	{
		$arLastminute = array();
		$arFirstMoment = array();
		$arLengthDiscount = array();
		$collection = $this->getBkDiscounts($bookingId,$bookingType,array('*'),$filedSort,$sortBy);
		if(count($collection))
		{
			$discounts = $collection->getData();
			foreach($discounts as $discount)
			{
                if($discount['discount_group'] != '')
                {
                    $arrayDiscountGroup = explode(',',$discount['discount_group']);
                    $groupId = $this->getBkCustomerGroup();
                    if(!in_array($groupId,$arrayDiscountGroup))
                    {
                        continue;
                    }
                }
				$extractDay = $intToday + ($discount['discount_period'] * 60 * 60 * 24);
				$discount['date_condition'] = date($formatDate,$extractDay);
				$discount['type_amount_text'] = $discount['discount_amount'].'%';
				if($discount['discount_amount_type'] == 2)
				{
					$discount['type_amount_text'] = $symboy;
					$discount['type_amount_text'] .= $discount['discount_amount'];
				}
				if($discount['discount_type'] == 1)
				{
					if(count($arLastminute))
					{
						$okSamePeriod = false;
						foreach($arLastminute as $kMinute => $minute)
						{
							if($minute['discount_period'] == $discount['discount_period'])
							{
								if($minute['discount_priority'] > $discount['discount_priority'])
								{
									$arLastminute[$kMinute] = $discount;
								}
								$okSamePeriod = true;
								break;
							}
						}
						if(!$okSamePeriod)
						{
							$arLastminute[] = $discount;
						}
					}
					else
					{
						$arLastminute[] = $discount;
					}
				}
				elseif($discount['discount_type'] == 2)
				{
					$discount['date_condition'] = date($formatDate,($extractDay - 60 * 60 * 24));
					if(count($arFirstMoment))
					{
						$okSameMoment = false;
						foreach($arFirstMoment as $kMoment => $moment)
						{
							if($moment['discount_period'] == $discount['discount_period'])
							{
								if($moment['discount_priority'] > $discount['discount_priority'])
								{
									$arFirstMoment[$kMoment] = $discount;
								}
								$okSameMoment = true;
								break;
							}
						}
						if(!$okSameMoment)
						{
							$arFirstMoment[] = $discount;
						}
					}
					else
					{
						$arFirstMoment[] = $discount;
					}
				}
				elseif($discount['discount_type'] == 3)
				{
					if(count($arLengthDiscount))
					{
						$okSameLength = false;
						foreach($arLengthDiscount as $kl => $lengthDiscount)
						{
							if($lengthDiscount['discount_period'] == $discount['discount_period'])
							{
								if($lengthDiscount['discount_priority'] > $discount['discount_priority'])
								{
									$arLengthDiscount[$kl] = $discount;
								}
								$okSameLength = true;
								break;
							}
						}
						if(!$okSameLength)
						{
							$arLengthDiscount[] = $discount;
						}
					}
					else
					{
						$arLengthDiscount[] = $discount;
					}
				}
			}
		}
		//marge discount
		$finalArDiscounts = array();
		if(count($arLastminute))
		{
			foreach($arLastminute as $lastminute)
			{
				$finalArDiscounts[] = $lastminute;
			}
		}
		if(count($arFirstMoment))
		{
			foreach($arFirstMoment as $firstMoment)
			{
				$finalArDiscounts[] = $firstMoment;
			}
		}
		if(count($arLengthDiscount))
		{
			foreach($arLengthDiscount as $lengthDiscount)
			{
				$finalArDiscounts[] = $lengthDiscount;
			}
		}
		//sort array by 
		if(count($finalArDiscounts))
		{
			for($i = 0; $i < count($finalArDiscounts) - 1; $i++)
			{
				for($j = count($finalArDiscounts) - 1; $j > $i; $j--)
				{
					$tempDis1 = $finalArDiscounts[$i];
					$tempDis2 = $finalArDiscounts[$j];
					if($tempDis2['discount_priority'] < $tempDis1['discount_priority'])
					{
						$tempDisTg = $finalArDiscounts[$i];
						$finalArDiscounts[$i] = $finalArDiscounts[$j];
						$finalArDiscounts[$j] = $tempDisTg;
					}
				}
			}
		}
		return $finalArDiscounts;
	}
	function getLastMinuteDiscount($bookingId,$bookingType,$intervalsDays)
	{
		$arrayDiscount = array();
		$collection = $this->getBkDiscounts($bookingId,$bookingType,array('*'),'discount_period','DESC');
		$collection->addFieldToFilter('discount_type',1);
		$discounts = $collection->getData();
		if(count($discounts))
		{
			foreach($discounts as $discount)
			{
                if($discount['discount_group'] != '')
                {
                    $arrayDiscountGroup = explode(',',$discount['discount_group']);
                    $groupId = $this->getBkCustomerGroup(); // get cusomer group
                    if(!in_array($groupId,$arrayDiscountGroup))
                    {
                        continue;
                    }
                }
				if($discount['discount_period'] >= $intervalsDays)
				{
				    if(count($arrayDiscount) && $arrayDiscount['discount_priority'] < $discount['discount_priority'])
                    {
                        continue;
                    }
					$arrayDiscount = $discount;
				}
			}
		}
		return $arrayDiscount;
	}
	/**
	* get kind of discount has type is first momment
	* @params int $bokingId, $intervalsDays string $bookingType,
	**/
	function getFirstMommentDiscount($bookingId,$bookingType,$intervalsDays)
	{
		$arrayDiscount = array();
		// $discounts = Mage::getModel('bookingsystem/discount')->getDiscounts($bookingId,$bookingType,2,'period','ASC');
		$collection = $this->getBkDiscounts($bookingId,$bookingType,array('*'),'discount_period','ASC');
		$collection->addFieldToFilter('discount_type',2);
		$discounts = $collection->getData();
		if(count($discounts))
		{

			foreach($discounts as $discount)
			{
                if($discount['discount_group'] != '')
                {
                    $arrayDiscountGroup = explode(',',$discount['discount_group']);
                    $groupId = $this->getBkCustomerGroup(); // get cusomer group
                    if(!in_array($groupId,$arrayDiscountGroup))
                    {
                        continue;
                    }
                }
				if($discount['discount_period'] < $intervalsDays)
				{
                    if(count($arrayDiscount) && $arrayDiscount['discount_priority'] < $discount['discount_priority'])
                    {
                        continue;
                    }
					$arrayDiscount = $discount;
				}
			}
		}
		return $arrayDiscount;
	}
	/**
	* get kind of discount has type is first momment
	* @params int $bokingId, $intervalsDays string $bookingType,
	**/
	function getLengthDiscount($bookingId,$bookingType,$maxItems)
	{
		$arrayDiscount = array();
		// $discounts = Mage::getModel('bookingsystem/discount')->getDiscounts($bookingId,$bookingType,3,'period','ASC');
		$collection = $this->getBkDiscounts($bookingId,$bookingType,array('*'),'discount_period','ASC');
		$collection->addFieldToFilter('discount_type',3);
		$discounts = $collection->getData();
		if(count($discounts))
		{
			foreach($discounts as $discount)
			{
			    if($discount['discount_group'] != '')
                {
                    $arrayDiscountGroup = explode(',',$discount['discount_group']);
                    $groupId = $this->getBkCustomerGroup(); // get cusomer group
                    if(!in_array($groupId,$arrayDiscountGroup))
                    {
                        continue;
                    }
                }
				if($discount['discount_period'] <= $maxItems)
				{
                    if(count($arrayDiscount) && $arrayDiscount['discount_priority'] < $discount['discount_priority'])
                    {
                        continue;
                    }
					$arrayDiscount = $discount;
				}
			}
		}
		return $arrayDiscount;
	}
	/*
	* save data options
	* @param array $inputs, int $bookingId
	**/
	function saveBkDiscounts($params,$bookingId,$bookingType = 'per_day')
	{
		//get All Discounts
		$collection = $this->getBkDiscounts($bookingId,$bookingType,array('discount_id'));
		$discountIds = array();
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$discountIds[$collect->getId()] = $collect->getId();
			}
		}
		if(count($params))
		{
			foreach($params as $param)
			{
				$param['discount_booking_id'] = $bookingId;
				$param['discount_booking_type'] = $bookingType;
				if(isset($param['discount_group']) && count($param['discount_group']) > 0)
                {
                    $param['discount_group'] = implode(',',$param['discount_group']);
                }
                else
                {
                    $param['discount_group'] = '';
                }
				if(array_key_exists($param['discount_id'],$discountIds))
				{
					unset($discountIds[$param['discount_id']]);
				}
				if($param['discount_id'] == 0)
				{
					unset($param['discount_id']);
				}
				$this->setData($param)->save();
			}
		}
		//delete
		if(count($discountIds))
		{
			foreach($discountIds as $discountId)
			{
				$this->setId($discountId)->delete();
			}
		}
	}
	function getPriceDiscounts($maxItem,$amount,$amountType,$totalPrice,$pricePercent,$nuberDiscoutItems)
	{
		$salePrice = 0;
		if($maxItem == 0)
		{
			if($amountType == 1)
			{
				$salePrice = ($totalPrice * $amount) / 100;
				
			}
			else
			{
				$salePrice = $amount * $nuberDiscoutItems;
			}
		}
		else
		{
			if($amountType == 1)
			{
				$salePrice = ($pricePercent * $amount) / 100;
				
			}
			else
			{
				$salePrice = $amount * $nuberDiscoutItems;
			}
		}
		return $salePrice;
	}
	function deleteDiscounts($bookingId,$bookingType)
	{
		$collection = $this->getCollection()
			->addFieldToFilter('discount_booking_id',$bookingId)
			->addFieldToFilter('discount_booking_type',$bookingType);
		if(count($collection))
		{
			foreach($collection as $collect)
			{
				$this->setId($collect->getId())->delete();
			}
		}
	}
	/*
	 * Check Cusomer Group
	 * */
	private function getBkCustomerGroup()
    {
        $groupId = -1;
        if($this->_customerSession->isLoggedIn())
        {
            $groupId = $this->_customerSession->getCustomer()->getGroupId();
        }
        return $groupId;
    }
}