<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkOrderHelper;

class RoomPrice extends AbstractHelper
{
	/**
     *
     * @var Magento\Framework\Stdlib\DateTime\Timezone 
    */
	protected $_timezone;
	/**
     *
     * @var Magento\Sales\Model\Order
    */
	protected $_bkCoreOrders;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     *  Model
     *
     * @var \Magebay\Bookingsystem\Model\DiscountsFactory
    */
	protected $_discountsFactory;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\Bookingorders
    */
	protected $_bookingorders;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\OptionsFactory
    */
	protected $_optionsFactory;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\CalendarsFactory
    */
	protected $_calendarsFactory;
	/**
     * Model
     *
     * @var \Magebay\Bookingsystem\Model\BkOrderHelper
    */
	protected $_bkOrderHelper;
	public function __construct(
       Context $context,
	   Timezone $timezone,
	   BkCoreOrders $bkCoreOrders,
	   BkHelperDate $bkHelperDate,
	   DiscountsFactory $discountsFactory,
	   BookingordersFactory $bookingorders,
	   OptionsFactory $optionsFactory,
	   CalendarsFactory $calendarsFactory,
	   BkOrderHelper $bkOrderHelper
    ) 
	{
       parent::__construct($context);
	   $this->_timezone = $timezone;
	   $this->_bkCoreOrders = $bkCoreOrders;
	   $this->_bkHelperDate = $bkHelperDate;
	   $this->_discountsFactory = $discountsFactory;
	   $this->_optionsFactory = $optionsFactory;
	   $this->_bookingorders = $bookingorders;
	   $this->_calendarsFactory = $calendarsFactory;
	   $this->_bkOrderHelper = $bkOrderHelper;
    }
	function getPriceBetweenDays($room,$checkIn,$checkOut,$qty = 1,$itemId = 0,$paramAddons = array(),$orderItemId = 0)
	{
		$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		//convert date to int
		//convert date to int
		//$room = Mage::getModel('bookingsystem/rooms')->getRoom($roomId);
		$intCheckIn = strtotime($checkIn);
		$tempCheckIn = $intCheckIn;
		$intCheckOut = strtotime($checkOut);
		$oneDay = 24 * 60 * 60;
		if($intCheckIn == $intCheckOut)
		{
			$intCheckOut += $oneDay;
		}
		$totalPrice = 0;
		$totalPromo = 0;
		$checkPromo = false;
		// $totalDays = 0;
		$priceService = 0;
		$intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
		$today = date('Y-m-d',$intCoreCurrentTime);
		$intToday = strtotime($today);
		$strError = '';
		if($intCheckIn < $intToday)
		{
			$strError = __('You can not book previous day!');
		}
		$strDay = date('Y-m-d',$intCheckIn);
		$calendarsModel = $this->_calendarsFactory->create(); 
		$fistCalendar = $calendarsModel->getCalendarBetweenDays($room->getId(),$strDay,'hotel');
		//get Min Qty
		$minQty = $fistCalendar->getCalendarQty() > 0 ? $fistCalendar->getCalendarQty() : 1;
		$minimum = $room->getRoomMinimumDay();
		$maximum = $room->getRoomMaximumDay();
		$numberDays = ($intCheckOut - $intCheckIn) / $oneDay;
		$strNote = '';
		//temp total price for discount type last minute
		$tempPriceDiscount = 0;
		$tempPromoDiscount = 0;
		//get data for discount
		
		$priceAnount1 = 0; // price for last minute amount
		$promoAnount1 = 0; // price last minute
		$priceAnount2 = 0; // price for first moment
		$promoAnount2 = 0; // price last first moment
		$priceAnount3 = 0;
		$promoAnount3 = 0;
		//max item for kind of type
		$tempMaxItem1 = 0;
		$tempMaxItem2 = 0;
		$tempMaxItem3 = 0;
		$msgDiscount = '';
		if($numberDays < $minimum && $minimum > 0)
		{
			$strNote = __('Note : Minimum days are %1, You must pay money for %2 Days',$minimum,$minimum);
			//update check out to calculator price
			$intCheckOut = $intCheckIn + ($oneDay * $minimum);
		}
		if($numberDays > $maximum && $maximum > 0)
		{
			$strError = __('Maximum days are %1, please check again',$maximum);
		}
		$totalDays = ($intCheckOut - $intCheckIn) / $oneDay;
		$dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
		$discountModel = $this->_discountsFactory->create();
		$arDiscount1 = $discountModel->getLastMinuteDiscount($room->getId(),'hotel',$dmDays);
		$arDiscount2 =  $discountModel->getFirstMommentDiscount($room->getId(),'hotel',$dmDays);
		$arDiscount3 = $discountModel->getLengthDiscount($room->getId(),'hotel',$totalDays);
		if($intCheckOut < $intCheckIn)
		{
			$strError = __('Dates are not available. Please check again');
		}
		if($strError == '')
		{
			$disableDays = trim($room->getDisableDays());
			$arDisableTextDays = array(
				0=>__('Sunday'),
				1=>__('Monday'),
				2=>__('Tuesday'),
				3=>__('Wednesday'),
				4=>__('Thursday'),
				5=>__('Friday'),
				6=>__('Saturday')
			);
			$strDisableTextDays = '';
			if($disableDays != '')
			{
				$disableDays = explode(',',$disableDays);
				foreach($disableDays as $disableDay)
				{
					if($strDisableTextDays == '')
					{
						$strDisableTextDays = $arDisableTextDays[$disableDay];
					}
					else
					{
						$strDisableTextDays .= ', '.$arDisableTextDays[$disableDay];
					}
					
				}
			}
			else
			{
				$disableDays = array();
			}
			$loop1 = 0;
			while($intCheckIn < $intCheckOut)
			{
				$strDay = date('Y-m-d',$intCheckIn);
				$numberDayOfWeek = date('w',$intCheckIn);
				if(in_array($numberDayOfWeek,$disableDays))
				{
					$strError = __('Dates are not available. System is closed on %1.',$strDisableTextDays);
					break;
				}
				$calendar = $calendarsModel->getCalendarBetweenDays($room->getId(),$strDay,'hotel');
				if(!$calendar->getId())
				{
					$strError = __('Dates are not available. Please check again');
					break;
				}
				$avaliableQty = $minQty;
				if($calendar->getId())
				{
					//total order quantity
					$totalOrder = $this->getOrderTotalQuantity($strDay,$room->getId(),$orderItemId);
					$cartQty = $this->getTotalRoomItemInCart($room->getRoomBookingId(),$room->getId(),$strDay,$itemId);
					$avaliableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty );
					if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block') || $avaliableQty < 0)
					{
						$strError = __('Dates are not available. Please check again');
						break;
					}
				}
				if($minQty > ($avaliableQty + 1))
				{
					$minQty = $avaliableQty + 1;
				}
				$price = $calendar->getCalendarPrice();
				$promo = $calendar->getCalendarPromo();
				$totalPrice += $price;
				//last minute
				if(count($arDiscount1) && $loop1 < $arDiscount1['discount_max_items'])
				{
					$priceAnount1 += $price;
				}
				//first moment
				if(count($arDiscount2) && $loop1 < $arDiscount2['discount_max_items'])
				{
					$priceAnount2 += $price;
				}
				if(count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items'])
				{
					$priceAnount3 += $price;
				}
				if($promo > 0)
				{
					$totalPromo += $promo;
					$checkPromo = true;
					//last minute
					if(count($arDiscount1) && $loop1 < $arDiscount1['discount_max_items'])
					{
						$promoAnount1 += $promo;
					}
					//first moment
					if(count($arDiscount2) && $loop1 < $arDiscount2['discount_max_items'])
					{
						$promoAnount2 += $promo;
					}
					if(count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items'])
					{
						$promoAnount3 += $promo;
					}
				}
				else
				{
					$totalPromo += $price;
					//last minute
					if(count($arDiscount1) && $loop1 < $arDiscount1['discount_max_items'])
					{
						$promoAnount1 += $price;
					}
					//first moment
					if(count($arDiscount2) && $loop1 < $arDiscount2['discount_max_items'])
					{
						$promoAnount2 += $price;
					}
					if(count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items'])
					{
						$promoAnount3 += $price;
					}
				}
				//$totalDays++;
				$intCheckIn += $oneDay;
				$loop1++;
			}
			if(count($arDiscount1) && $arDiscount1['discount_max_items'] == 0)
			{
				$priceAnount1 = $totalPrice;
				$promoAnount1 = $totalPromo;
			}
			if(count($arDiscount2) && $arDiscount2['discount_max_items'] == 0)
			{
				$priceAnount2 = $totalPrice;
				$promoAnount2 = $totalPromo;
			}
			if(count($arDiscount3) && $arDiscount3['discount_max_items'] == 0)
			{
				$priceAnount3 = $totalPrice;
				$promoAnount3 = $totalPromo;
			}
		}
		if($totalDays == 0)
		{
			$strError = __('You have to book a day or more');
		}
		$tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $totalDays) ? $arDiscount1['discount_max_items'] : $totalDays;
		$tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $totalDays) ? $arDiscount2['discount_max_items'] : $totalDays;
		$tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $totalDays) ? $arDiscount3['discount_max_items'] : $totalDays;
		if(count($paramAddons))
		{
			foreach($paramAddons as $keyAdd => $paramAddon)
			{
				$tempParamAddons = array($keyAdd=>$paramAddon);
				$arAddonPrice = $this->getAddonsPrice($tempParamAddons,$room->getId(),'hotel');
				if(count($arAddonPrice))
				{
					if($arAddonPrice['error'] == '')
					{
						if($arAddonPrice['price_type'] == 1)
						{
							$totalPrice += $arAddonPrice['price'] * $totalDays;
						}
						else
						{
							$totalPrice += $arAddonPrice['price'];
						}
						
						if(count($arDiscount1))
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$priceAnount1 += $arAddonPrice['price'] * $tempMaxItem1;
								if($checkPromo)
									$promoAnount1 += $arAddonPrice['price'] * $tempMaxItem1;
							}
							else
							{
								$priceAnount1 += $arAddonPrice['price'];
								if($checkPromo)
									$promoAnount1 += $arAddonPrice['price'];
							}
							
						}
						if(count($arDiscount2))
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$priceAnount2 += $arAddonPrice['price'] * $tempMaxItem2;
								if($checkPromo)
									$promoAnount2 += $arAddonPrice['price'] * $tempMaxItem2;
							}
							else
							{
								$priceAnount2 += $arAddonPrice['price'];
								if($checkPromo)
									$promoAnount2 += $arAddonPrice['price'];
							}
							
						}
						if(count($arDiscount3))
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$priceAnount3 += $arAddonPrice['price'] * $tempMaxItem3;
								if($checkPromo)
									$promoAnount3 += $arAddonPrice['price'] * $tempMaxItem3;
							}
							else
							{
								$priceAnount3 += $arAddonPrice['price'];
								if($checkPromo)
									$promoAnount3 += $arAddonPrice['price'];
							}
							
						}
						if($checkPromo && $totalPromo > 0)
						{
							if($arAddonPrice['price_type'] == 1)
							{
								$totalPromo += $arAddonPrice['price'] * $totalDays;
							}
							else
							{
								$totalPromo += $arAddonPrice['price'];
							}
							
						}
					}
					else
					{
						$strError = $arAddonPrice['error'];
						break;
					}
				}
			}
		}
		$salePrice = 0;
		$salePromo = 0;
		//discount sale. new request from customer.
		if(count($arDiscount1) || count($arDiscount2) || count($arDiscount3))
		{
			$oklastMinute = false;
			$okFirstMoment = false;
			$maxPeriod = 0;
			$maxPeriod2 = 0;
			//for last minute
			if(count($arDiscount1))
			{
				$maxPeriod = $intToday + ($oneDay * $arDiscount1['discount_period']);
				if(($priceAnount1 > 0 && $tempCheckIn < $maxPeriod))
				{
					$oklastMinute = true;
				}
			}
			if(count($arDiscount2))
			{
				$maxPeriod2 = $intToday + ($oneDay * $arDiscount2['discount_period'])  - $oneDay;
				if(($priceAnount2 > 0 && $tempCheckIn > $maxPeriod2))
				{
					$okFirstMoment = true;	
				}
			}
			if($okFirstMoment && $oklastMinute)
			{
				if($arDiscount1['discount_priority'] > $arDiscount2['discount_priority'])
				{
					$okFirstMoment = true;
					$oklastMinute = false;
				}
				else
				{
					$okFirstMoment = false;
					$oklastMinute = true;
				}
			}
			if($oklastMinute)
			{
				$salePrice += $discountModel->getPriceDiscounts($arDiscount1['discount_max_items'],$arDiscount1['discount_amount'],$arDiscount1['discount_amount_type'],$totalPrice,$priceAnount1,$tempMaxItem1);
				$salePromo += $discountModel->getPriceDiscounts($arDiscount1['discount_max_items'],$arDiscount1['discount_amount'],$arDiscount1['discount_amount_type'],$totalPromo,$promoAnount1,$tempMaxItem1);
			}
			elseif($okFirstMoment)
			{
				$salePrice += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPrice,$priceAnount2,$tempMaxItem2);
				$salePromo += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPromo,$promoAnount2,$tempMaxItem2);
			}
			if(count($arDiscount3) && $totalDays >= $arDiscount3['discount_period'])
			{
				$salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPrice,$priceAnount3,$tempMaxItem3);
				if($checkPromo && $totalPromo > 0)
				{
					$salePromo += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPromo,$promoAnount3,$tempMaxItem3);
				}
			}
		}
		$totalSaving = 0;
		if($salePrice > 0)
		{
			if($checkPromo)
			{
				$totalPromo = $totalPromo - $salePromo;
			}
			else
			{
				$salePromo  = $salePrice;
				$totalPromo = $totalPrice - $salePrice;
			}
		}
		else
		{
			if(!$checkPromo)
			{
				$totalPromo = 0;
			}
			else
			{
				$totalSaving = $totalPrice - $totalPromo;
			}
		}
		return array(
			'total_price'=>$totalPrice,
			'total_promo'=>$totalPromo,
			'total_saving'=>$totalSaving,
			'total_days'=>$totalDays,
			'min_qty'=>$minQty,
			'str_error'=>$strError,
			'str_note'=>$strNote,
			'msg_discount'=>$msgDiscount
		);
	}
	/**
	* get Qty of a day from booking order
	* @param string $strDay ,$bookingTime, int $bookingId, 
	* @return int $totalQuantity
	**/
	function getOrderTotalQuantity($strDay,$bookingId,$orderItemId = 0)
	{
		$arSelectOrder = array();
		$arAttributeConditions = array();
		$condition = 'booking_id = '.$bookingId;
		$arrayBooking = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id','bkorder_qt_item_id');
		$totalOrder = 0;
		$orderModel = $this->_bookingorders->create();
		$orders = $orderModel->getCollection()
				->addFieldToSelect($arrayBooking)
				->addFieldToFilter('bkorder_booking_id',$bookingId)
				->addFieldToFilter('bkorder_room_id',1);
		if(count($orders))
		{
			foreach($orders as $order)
			{
				$bkCoreModel = $this->_bkCoreOrders;
				$defaultOrderCollection = $bkCoreModel->getCollection()
					->addFieldToFilter('entity_id',$order->getBkorderOrderId());
				$defaultOrder = $defaultOrderCollection->getFirstItem();
				if($defaultOrder->getId())
				{
					if($defaultOrder->getStatus() == 'canceled' || $defaultOrder->getStatus() == 'closed')
					{
						continue;
					}
				}
				else
				{
					continue;
				}
				if($orderItemId > 0)
				{
					if($order->getBkorderQtItemId() == $orderItemId)
					{
						continue;
					}
				}
				if(strtotime($order->getBkorderCheckIn()) <= strtotime($strDay) && strtotime($order->getBkorderCheckOut()) > strtotime($strDay))
				{
					$totalOrder += $order->getBkorderQty();
				}
			}
		}
		return $totalOrder;
	}
	/**
	* get total booking item in cart
	* @param int $bookingId, $roomId string $strDay has format is Y-m-d,$itemId
	* @return int $total
	**/
	function getTotalRoomItemInCart($bookingId,$roomId,$strDay,$itemId = 0)
	{
		$total = 0;
		$arrayItem = $this->_bkOrderHelper->getRoomArrayItemIncart($bookingId,$roomId,$itemId);
		//convert time to int
		$timeDay = strtotime($strDay);
		foreach($arrayItem as $item)
		{
			$intCheckIn = strtotime($item['check_in']);
			$intCheckOut = strtotime($item['check_out']);
			//if booking time is daily , I do not get total of final day.
			$intCheckOut -= 60*60*24;
			if($intCheckIn <= $timeDay && $timeDay <= $intCheckOut)
			{
				$total += $item['qty'];
			}
		}
		return $total;
	}
	/**
	* get price addons sells
	* @params array $paramAddons
	* @return float $addonsPrice
	**/
	function getAddonsPrice($paramAddons,$bookingId,$bookingType = 'per_day')
	{
		//get addons-sells
		$bkOptionsModel = $this->_optionsFactory->create();
		$addonsSells = $bkOptionsModel->getBkOptionsData($bookingId,$bookingType);
		$price = 0;
		$error = '';
		$optionPriceType = 1;
		if(count($addonsSells))
		{
			foreach($addonsSells as $addonsSell)
			{
				if(array_key_exists($addonsSell['option_id'],$paramAddons))
				{
					if($addonsSell['option_required'] == 1)
					{
						if($addonsSell['option_type'] == 1 || $addonsSell['option_type'] == 2 || $addonsSell['option_type'] == 4)
						{
							if(trim($paramAddons[$addonsSell['option_id']]) == '' || (float)$paramAddons[$addonsSell['option_id']] == 0)
							{
								$error = __('Please Enter %1 value',$addonsSell['option_title']);
								break;
							}
						}
						else
						{
							$tempParamAddonMulties = isset($paramAddons[$addonsSell['option_id']]) ? $paramAddons[$addonsSell['option_id']] : array();
							if(!count($tempParamAddonMulties))
							{
								$error = __('Please Enter %1 value',$addonsSell['option_title']);
								break;
							}
						}
					}
					if($addonsSell['option_type'] == 1)
					{
						if($addonsSell['option_max_number'] > 0 && $paramAddons[$addonsSell['option_id']] > $addonsSell['option_max_number'])
						{
							$error = __('You can not enter value lager %1 at option %2',$addonsSell['option_max_number'],$addonsSell['option_title']);
							break;
						}
					}
					if($addonsSell['option_type'] == 1)
					{
						$price  += (float)$paramAddons[$addonsSell['option_id']] * $addonsSell['option_price'];
					}
					elseif($addonsSell['option_type'] == 2 || $addonsSell['option_type'] == 4)
					{
						$price  += (float)$paramAddons[$addonsSell['option_id']];
					}
					elseif($addonsSell['option_type'] == 3 || $addonsSell['option_type'] == 5)
					{
						$paramAddonMulties = $paramAddons[$addonsSell['option_id']];
						foreach($paramAddonMulties  as $paramAddonMulty)
						{
							$price  += (float)$paramAddonMulty;
						}
					}
					$optionPriceType = (isset($addonsSell['option_price_type']) && (int)$addonsSell['option_price_type'] > 0) ? $addonsSell['option_price_type']  : 1;
				}
			}
		}
		return array(
			'price'=>$price,
			'error'=>$error,
			'price_type'=>$optionPriceType
		);
	}
	
}
 