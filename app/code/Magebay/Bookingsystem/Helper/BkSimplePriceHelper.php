<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Sales\Model\Order as BkCoreOrders;
use Magento\Backend\Model\Auth\Session as BkBackendSession;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Helper\BkOrderHelper;


class BkSimplePriceHelper extends AbstractHelper
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
     *
     * @var Magento\Backend\Model\Auth\Session
    */
	protected $_bkbackendSession;
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
	* @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
     */
	protected $_dropdownFactory;
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
	   BkBackendSession $bkbackendSession,
	   BkHelperDate $bkHelperDate,
	   DiscountsFactory $discountsFactory,
	   BookingordersFactory $bookingorders,
	   OptionsFactory $optionsFactory,
	   OptionsdropdownFactory $optionsdropdownFactory,
	   CalendarsFactory $calendarsFactory,
	   BkOrderHelper $bkOrderHelper
    ) 
	{
       parent::__construct($context);
	   $this->_timezone = $timezone;
	   $this->_bkCoreOrders = $bkCoreOrders;
	   $this->_bkbackendSession = $bkbackendSession;
	   $this->_bkHelperDate = $bkHelperDate;
	   $this->_discountsFactory = $discountsFactory;
	   $this->_optionsFactory = $optionsFactory;
	   $this->_dropdownFactory = $optionsdropdownFactory;
	   $this->_bookingorders = $bookingorders;
	   $this->_calendarsFactory = $calendarsFactory;
	   $this->_bkOrderHelper = $bkOrderHelper;
    }
	function getPriceBetweenDays($booking,$checkIn,$checkOut,$qty = 1,$itemId = 0,$paramAddons = array(),$oldOrderItemId = 0)
	{
		$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		//convert date to int
		$intCheckIn = strtotime($checkIn);
		$tempCheckIn = $intCheckIn;
		$intCheckOut = strtotime($checkOut);
		$oneDay = 24 * 60 * 60;
		if($intCheckIn == $intCheckOut && $booking->getBookingTime() == 1)
		{
			$intCheckOut += $oneDay;
		}
		// minimum day, maximum day
		$minimum = $booking->getBookingMinDays();
		$maximum = $booking->getBookingMaxDays();
		$totalPrice = 0;
		$totalPromo = 0;
		$checkPromo = false;
		
		$strError = '';
		$strNote = '';
		$intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
		$today = date('Y-m-d',$intCoreCurrentTime);
		$intToday = strtotime($today);
		$totalSaving = 0;
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
		//number days
		$numberDays = ($intCheckOut - $intCheckIn) / $oneDay;
		$minimum = $booking->getBookingTime() == 4 ? $minimum - 1 : $minimum;
		if($minimum > 0)
		{
			//$strNote = __('Note : Minimum days are %1, You must pay for %2 Days',$minimum,$minimum);
			//update check out to calculator price
			$intCheckOut = $intCheckIn + ($oneDay * $minimum);
		}
		if($intCheckIn < $intToday)
		{
			$strError = __('You can not book previous day!');
		}
		/*elseif($numberDays > $maximum && $maximum > 0)
		{
			$strError = __('Maximum days are %1, please check again',$maximum);
		}*/
		$totalDays = ($intCheckOut - $intCheckIn) / $oneDay;
		if($intCheckOut < $intCheckIn)
		{
			$strError = __('Dates are not available. Please check again');
		}
		$dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
		$discountModel = $this->_discountsFactory->create();
		$arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
		$arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
		$arDiscount3 = $discountModel->getLengthDiscount($booking->getId(),'per_day',$totalDays);
		$newCheckIn = date('Y-m-d',$intCheckIn);
		$newCheckOut = '';
		if($strError == '')
		{
			$loop1 = 0; // for last minute
			$loop2 = 0; // for first moment
			$disableDays = trim($booking->getDisableDays());
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
			$tempIntCheckOut2 = $booking->getBookingTime() == 4 ? ($intCheckOut + $oneDay) : $intCheckOut;
            $totalDays  = $booking->getBookingTime() == 4 ? ($totalDays + 1) : $totalDays;
			while($intCheckIn < $tempIntCheckOut2)
			{
				$calendarsModel = $this->_calendarsFactory->create();
				$strDay = date('Y-m-d',$intCheckIn);
				$numberDayOfWeek = date('w',$intCheckIn);
				if(in_array($numberDayOfWeek,$disableDays))
				{
					$strError = __('Dates are not available. System is closed on %1.',$strDisableTextDays);
					break;
				}
				$calendar = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType());
				if(!$calendar->getId())
				{
					$strError = __('Dates are not available. Please check again');
					break;
				}
				if($calendar->getId())
				{
					//total order quantity
					$totalOrder = $this->getOrderTotalQuantity($strDay,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
					//get total booking Item in cart
					$cartQty = $this->getTotalItemInCart($booking->getId(),$booking->getBookingTime(),$strDay,$itemId);
					$avaliableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty);
					if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block') || $avaliableQty < 0)
					{
						$strError = __('Dates are not available. Please check again');
						break;
					}
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
				//length if reservations
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
					//length if reservations
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
					//length if reservations
					if(count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items'])
					{
						$promoAnount3 += $price;
					}
				}
				//$totalDays++;
				$intCheckIn += $oneDay;
				$loop1++;
			}
			$newCheckOut = date('Y-m-d',$intCheckOut);
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
		$tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $totalDays) ? $arDiscount1['discount_max_items'] : $totalDays;
		$tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $totalDays) ? $arDiscount2['discount_max_items'] : $totalDays;
		$tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $totalDays) ? $arDiscount3['discount_max_items'] : $totalDays;
		if(count($paramAddons))
		{
			foreach($paramAddons as $keyAdd => $paramAddon)
			{
				$tempparamAddon = array($keyAdd=>$paramAddon);
				$arAddonPrice = $this->getAddonsPrice($tempparamAddon,$booking->getId());
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
				$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;
			}
			elseif($okFirstMoment)
			{
				$salePrice += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPrice,$priceAnount2,$tempMaxItem2);
				$salePromo += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPromo,$promoAnount2,$tempMaxItem2);
				$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;	
			}
			if(count($arDiscount3) && $totalDays >= $arDiscount3['discount_period'])
			{
				// $tempTotalPrice = $totalPrice - $salePrice;
				$salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPrice,$priceAnount3,$tempMaxItem3);
				if($checkPromo && $totalPromo > 0)
				{
					// $tempTotalPromo = $totalPromo - $salePromo;
					$salePromo += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPromo,$promoAnount3,$tempMaxItem3);
				}
				$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;

			}
		}
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
			'str_error'=>$strError,
			'str_note'=>$strNote,
			'msg_discount'=>$msgDiscount,
            'check_in'=>$newCheckIn,
            'check_out'=>$newCheckOut
		);
	}
	/*
	 * get Tour Price
	 * */
	function getBkTourPrice($booking,$checkIn,$checkOut,$qty = 1,$itemId = 0,$paramAddons = array(),$people = array(),$oldOrderItemId = 0)
    {
        $intCheckIn = strtotime($checkIn);
        $tempCheckIn = $intCheckIn;
        $intCheckOut = strtotime($checkOut);
        $oneDay = 60 * 60 * 24;
        if($booking->getBookingMinDays() > 0){
            $intCheckOut = $intCheckIn + ($booking->getBookingMinDays() * $oneDay);
        }
        $strDay = date('Y-m-d',$intCheckIn);
        $totalPrice = 0;
        $totalPromo = 0;
        $calendarsModel = $this->_calendarsFactory->create();
        $calendar = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType());
        $strError = '';
        $checkPromo = false;
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
        $newCheckIn = '';
        $newCheckOut = '';
        $intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
        $today = date('Y-m-d',$intCoreCurrentTime);
        $intToday = strtotime($today);
        $dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
        $totalPeople = 1;
        $extractPrices = array();
        if(!$calendar->getId())
        {
            $strError = __('Dates are not available. Please check again');
        }
        else
        {

            if($calendar->getExtractPersons() != '')
            {
                $extractPrices = json_decode($calendar->getExtractPersons(),true);
            }
            $discountModel = $this->_discountsFactory->create();
            $arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
            $arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
            $arDiscount3 = array();
            $exPrice = 0;
            if(count($people) && count($extractPrices))
            {
                $qty = 0;
                $pd1 = 0;
                $pd2 = 0;
                $pd3 = 0;
                $tempDiscountPrice3 = array();
                foreach ($people as $key => $value)
                {
                    if((int)$value <= 0)
                    {
                        continue;
                    }

                    if(array_key_exists($key,$extractPrices))
                    {
                        $qty += $value;
                        $peoplePrice = $value - $extractPrices[$key]['free'];
                        if($peoplePrice <= 0)
                        {
                            continue;
                        }
                        $totalPeople += $peoplePrice;
                        if(count($arDiscount1))
                        {
                            if($arDiscount1['discount_max_items'] == 0)
                            {
                                $priceAnount1 += $extractPrices[$key]['price'] * $totalPeople;
                                $promoAnount1 += $extractPrices[$key]['price'] * $totalPeople;
                            }
                            else
                            {
                                if($totalPeople < ($arDiscount1['discount_max_items']))
                                {
                                    $priceAnount1 += $extractPrices[$key]['price'] * $totalPeople;
                                    $promoAnount1 += $extractPrices[$key]['price'] * $totalPeople;
                                    $pd1 += $totalPeople;
                                }
                                else
                                {
                                    if($pd1 < ($arDiscount1['discount_max_items']))
                                    {
                                        $priceAnount1 += $extractPrices[$key]['price'] * ($arDiscount1['discount_max_items'] - $pd1);
                                        $promoAnount1 += $extractPrices[$key]['price'] * ($arDiscount1['discount_max_items'] - $pd1);
                                        $pd1 = $arDiscount1['discount_max_items'] - 1;
                                    }
                                }
                            }

                        }
                        //first moment
                        if(count($arDiscount2))
                        {
                            if($arDiscount2['discount_max_items'] == 0)
                            {
                                $priceAnount2 += $extractPrices[$key]['price'] * $totalPeople;
                                $promoAnount2 += $extractPrices[$key]['price'] * $totalPeople;
                            }
                            else
                            {
                                if($totalPeople < ($arDiscount2['discount_max_items']))
                                {
                                    $priceAnount2 += $extractPrices[$key]['price'] * $totalPeople;
                                    $promoAnount2 += $extractPrices[$key]['price'] * $totalPeople;
                                    $pd2 += $totalPeople;
                                }
                                else
                                {
                                    if($pd2 < ($arDiscount2['discount_max_items'] - 1))
                                    {
                                        $priceAnount2 += $extractPrices[$key]['price'] * ($arDiscount2['discount_max_items'] - $pd2);
                                        $promoAnount2 += $extractPrices[$key]['price'] * ($arDiscount2['discount_max_items'] - $pd2);
                                        $pd2 = $arDiscount2['discount_max_items'] - 1;

                                    }

                                }
                            }
                        }
                        //length if reservations
                        for ($mk = 0; $mk < $peoplePrice; $mk++)
                        {
                            $tempDiscountPrice3[] = $extractPrices[$key]['price'];
                        }
                        $exPrice += $peoplePrice * $extractPrices[$key]['price'];
                    }
                }
                //$totalPeople++;
                $arDiscount3 = $discountModel->getLengthDiscount($booking->getId(),'per_day',$totalPeople);
                if(count($arDiscount3) && count($tempDiscountPrice3))
                {
                    foreach ($tempDiscountPrice3 as $mkk => $vkk)
                    {
                        if($arDiscount3['discount_max_items'] > 0 && $mkk >= $arDiscount3['discount_max_items'] - 1)
                        {
                            break;
                        }
                        $priceAnount3 += $vkk;
                        $promoAnount3 += $vkk;
                    }
                }
            }
            //total order quantity
            $totalOrder = $this->getOrderTotalQuantity($strDay,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
            //get total booking Item in cart
            $cartQty = $this->getTotalItemInCart($booking->getId(),$booking->getBookingTime(),$strDay,$itemId);
            $avaliableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty);
            $numberDays = 0;
            if($booking->getBookingTourType() == 2)
            {
                $newCheckIn = $calendar->getCalendarStartdate();
                $newCheckOut = $calendar->getCalendarEnddate();
            }
            else
            {
                $newCheckIn = $checkIn;
                $newCheckOut = date('Y-m-d',$intCheckOut);
                $numberDays = ($intCheckOut - $intCheckIn) / $oneDay;
            }
            $tempCheckIn = strtotime($newCheckIn);
            if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block' || strtotime($newCheckIn) <= $intToday) || $avaliableQty < 0)
            {
                if($avaliableQty < 0)
                {
                    //$strError = __('maximum persons is %1',($calendar->getCalendarQty() - ($totalOrder + $cartQty)));
                    $strError = __('Tour is not available in the days. Please try again with other days. ');
                }
                else
                {
                    $strError = __('Dates are not available. Please check again');
                }
            }
            else
            {
                $price = $calendar->getCalendarPrice();
                $promo = $calendar->getCalendarPromo();
                if($numberDays > 0)
                {
                    $price *= $numberDays;
                    $promo *= $numberDays;
                }
                $totalPrice = $price + $exPrice;
                $totalPromo = $promo;
                //if extract price
                if(count($arDiscount1))
                {
                    $priceAnount1 += $price;
                }
                if(count($arDiscount2))
                {
                    $priceAnount2 += $price;
                }
                $arDiscount3 = $discountModel->getLengthDiscount($booking->getId(),'per_day',1);
                if(count($arDiscount3))
                {
                    $priceAnount3 += $price;
                }
                if($totalPromo > 0)
                {
                    $checkPromo = true;
                    $totalPromo += $exPrice;
                    if(count($arDiscount1))
                    {
                        $promoAnount1 = $promo;
                    }
                    if(count($arDiscount2))
                    {
                        $promoAnount2 = $promo;
                    }
                    if(count($arDiscount3))
                    {
                        $promoAnount3 = $promo;
                    }
                }
            }
        }
        $tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $totalPeople) ? ($arDiscount1['discount_max_items']) : $totalPeople;
        $tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $totalPeople) ? ($arDiscount2['discount_max_items']) : $totalPeople;
        $tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $totalPeople) ? ($arDiscount3['discount_max_items']) : $totalPeople;
        if(count($paramAddons))
        {

            foreach($paramAddons as $keyAdd => $paramAddon)
            {
                $tempparamAddon = array($keyAdd=>$paramAddon);
                $arAddonPrice = $this->getAddonsPrice($tempparamAddon,$booking->getId());
                if(count($arAddonPrice))
                {
                    if($arAddonPrice['error'] == '')
                    {
                        if($arAddonPrice['price_type'] == 1)
                        {
                            $totalPrice += $arAddonPrice['price'] * $totalPeople;
                            //echo $totalPeople;
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
                                $totalPromo += $arAddonPrice['price'] * $totalPeople;
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
                $tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;
            }
            elseif($okFirstMoment)
            {
                $salePrice += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPrice,$priceAnount2,$tempMaxItem2);
                $salePromo += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPromo,$promoAnount2,$tempMaxItem2);
                $tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;
            }
            if(count($arDiscount3) && $totalPeople >= ($arDiscount3['discount_period'] - 1))
            {

                // $tempTotalPrice = $totalPrice - $salePrice;
                echo $priceAnount3;
                $salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPrice,$priceAnount3,$tempMaxItem3);
                if($checkPromo && $totalPromo > 0)
                {
                    // $tempTotalPromo = $totalPromo - $salePromo;
                    $salePromo += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPromo,$promoAnount3,$tempMaxItem3);
                }
                $tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;

            }
        }
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
            'person_price'=>$exPrice,
            'total_saving'=>0,
            'total_days'=>0,
            'str_error'=>$strError,
            'str_note'=>'',
            'msg_discount'=>'',
            'check_in'=>$newCheckIn,
            'check_out'=>$newCheckOut,
            'extract_price'=>$extractPrices
        );
    }
	/** get price from check in to check out
	* @params object $booking, string $checkIn,$checkOut (format Y-m-d), int $fromHour,$fromType,$toHour,$toType array $serviceIds 
	* @return array $prices
	**/
	function getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$fromMinute,$toMinute,$qty = 1,$itemId = 0,$paramAddons = array(),$oldOrderItemId = 0)
	{
		$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		$intCheckIn = strtotime($checkIn);
		$tempCheckIn = $intCheckIn;
		$intCheckOut = strtotime($checkOut);
		$totalPrice = 0;
		$totalPromo = 0;
		$totalHours = 0;
		$totalDays = 0;
		$oneDay = 60*60*24;
		$overFree = 1;
		$checkPromo = false;
		$serviceStart = explode(',',$booking->getBookingServiceStart());
		$hourStart = ($serviceStart[2] == 2 && $serviceStart[0] != 12) ? ($serviceStart[0] + 12) : $serviceStart[0];
		$serviceEnd = explode(',',$booking->getBookingServiceEnd());
		$hourEnd = ($serviceEnd[2] == 2 && $serviceEnd[0] != 12) ? ($serviceEnd[0] + 12) : $serviceEnd[0];
		$textStart = $serviceStart[0].' : ';
		$textStart .= $serviceStart[1] > 10 ? $serviceStart[1].' : ' : '0'.$serviceStart[1].' : ';
		$textStart .= $serviceStart[2] == 1 ? __('AM') : __('PM');
		$textEnd = $serviceEnd[0].' : ';
		$textEnd .= $serviceEnd[1] > 10 ? $serviceEnd[1].' : ' : '0'.$serviceEnd[1].' : ';
		$textEnd .= $serviceEnd[2] == 1 ? __('AM') : __('PM');
		//convert time to int
		$fromMinute = (int)$fromMinute;
		$toMinute = (int)$toMinute;
		$fromMinute = $fromMinute > 9 ? $fromMinute : '0'.$fromMinute;
		$toMinute = $toMinute > 9 ? $toMinute : '0'.$toMinute;
		$intFromHour = strtotime("$fromHour:$fromMinute:00");
		$intToHour = strtotime("$toHour:$toMinute:00");
		$tempMinuteStart = $serviceStart[1] > 9 ? $serviceStart[1] : '0'.$serviceStart[1];
		$tempMinuteEnd = $serviceEnd[1] > 9 ? $serviceEnd[1] : '0'.$serviceEnd[1];
		$intHourStart = strtotime("$hourStart:$tempMinuteStart:00");
		$intHourEnd = strtotime("$hourEnd:$tempMinuteEnd:00");
		//check error time
		$strError = '';
		$strNote = '';
		$minimum = $booking->getBookingMinDays();
		$maximum = $booking->getBookingMaxDays();
		$numberDays = ($intCheckOut + $oneDay - $intCheckIn) / $oneDay;
		// get max min hour
		$miniHours = $booking->getBookingMinHours();
		$maxHours = $booking->getBookingMaxHours();
		$noHours = 0;
		$intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
		$today = date('Y-m-d',$intCoreCurrentTime);
		$intToday = strtotime($today);
		if($numberDays == 1)
		{
			$noHours = $toHour - $fromHour;
		}
		// if 2 days
		elseif($numberDays - 2 == 0)
		{
			$noHours = $hourEnd - $fromHour;
			$noHours += $toHour - $hourStart;
		}
		else
		{
			$noHours = $hourEnd - $fromHour;
			$noHours += ($hourEnd - $hourStart) * ($numberDays - 2);
			$noHours += $toHour - $hourStart;
		}
		//discount price
		//get data for discount
		$priceAnount1 = 0; // price for last minute amount
		$promoAnount1 = 0; // price last minute
		$priceAnount2 = 0; // price for first moment
		$promoAnount2 = 0; // price last first moment
		$priceAnount3 = 0;
		$promoAnount3 = 0;
		$arDiscount3 = array();
		$msgDiscount = '';
		//max item for kind of type
		$tempMaxItem1 = 0;
		$tempMaxItem2 = 0;
		$tempMaxItem3 = 0;
		// I only check max day or min day when min hour and max hour are correct
		if($minimum > 0)
		{
			//$strNote = __('Note : Minimum day is %1, You must pay for %2 Days',$minimum,$minimum);
			//update check out to calculator price
			$intCheckOut = $intCheckIn;
		}
		if($intCheckIn < $intToday)
		{
			$strError = __('You can not book previous day!');
		}
		elseif($intFromHour < $intHourStart)
		{
			$strError = __('Please check time, service start at %1 ',$textStart);
		}
		elseif($intHourEnd < $intToHour)
		{
			$strError = __('Please check time, service finish at %1 ',$textEnd);
		}
		elseif($numberDays > $maximum && $maximum > 0 && $noHours >= $miniHours && $noHours <= $maxHours)
		{
			//$strError = __('Maximum day is %1, please check again',$maximum);
		}
		$dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
		if($intCheckOut < $intCheckIn)
		{
			$strError = __('Dates are not available. Please check again');
		}
		$discountModel = $this->_discountsFactory->create();
		$arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
		$arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
		$newCheckIn = date('Y-m-d',$intCheckIn);
		$newCheckOut = date('Y-m-d',$intCheckOut);
		if($strError == '')
		{
			$disableDays = trim($booking->getDisableDays());
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
			//get temp total hours
			$tempIntCheckIn = $intCheckIn;
			$tempIntTotalHours = 0;
			if($intCheckIn == $intCheckOut)
			{
				$tempIntTotalHours = $toHour - $fromHour;
			}
			else
			{
				$kmk = 0;
				while($tempIntCheckIn <= $intCheckOut)
				{
					$numberDayOfWeek = date('w',$tempIntCheckIn);
					if(in_array($numberDayOfWeek,$disableDays))
					{
						$strError = __('Dates are not available. System is closed on %1.',$strDisableTextDays);
						break;
					}
					if($kmk == 0)
					{
						// $tempIntTotalHours += $hourEnd - $fromHour;
						if($intFromHour < $intToHour)
						{
							
							$tempIntTotalHours += $toHour - $fromHour;
						}
						else
						{
							$tempIntTotalHours += 24 - $fromHour + $toHour;
						}
					}
					elseif($tempIntCheckIn == $intCheckOut)
					{
						if($toHour > $hourStart)
						{
							$tempIntTotalHours += $hourEnd - $hourStart;
						}
					}
					else
					{
						// $tempIntTotalHours += $hourEnd - $hourStart;
						if($intHourEnd >= $intHourStart)
						{
							$tempIntTotalHours += $hourEnd - $hourStart;
						}
						else
						{
							$tempIntTotalHours += (24 - $fromHour) + $toHour;
						}
					}
					$kmk++;
					$tempIntCheckIn += $oneDay;
				}
			}
			$arDiscount3 = $discountModel->getLengthDiscount($booking->getId(),'per_day',$tempIntTotalHours);
			//count discount
			$countDiscount = 0;
			$finalDayPrice = 0;
			$finalDayPromo = 0;
			$calendarsModel = $this->_calendarsFactory->create();
			if($intCheckIn == $intCheckOut)
			{
				$numberDayOfWeek = date('w',$intCheckIn);
				if($toHour - $fromHour <= 0)
				{
					$strError = __('Please check time, if you only book a day, You have to choose service finish time larger than service start time.');
				}
				elseif(in_array($numberDayOfWeek,$disableDays))
				{
					
					$strError = __('Dates are not available. System is closed on %1.',$strDisableTextDays);
				}
				else
				{
					$strDay = date('Y-m-d',$intCheckIn);
					
					$calendar = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType());
					if(!$calendar->getId())
					{
						$strError = __('Dates are not available. Please check again');
					}
					if($calendar->getId())
					{
						//total order quantity
						$totalOrder = $this->getOrderTotalQuantity($strDay,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
						//get total booking Item in cart
						$cartQty = $this->getTotalItemInCart($booking->getId(),$booking->getBookingTime(),$strDay,$itemId);
						$avaliableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty);
						if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block') || $avaliableQty < 0)
						{
							$strError = __('Dates are not available. Please check again');
						}
					}
					if($strError == '')
					{
						$price = $calendar->getCalendarPrice();
						$promo = $calendar->getCalendarPromo();
						$totalPrice += $price * ($toHour - $fromHour);
						$loop1 = 0;
						$tempFromHour = $fromHour;
						while($tempFromHour < $toHour)
						{
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
							//length of reservations
							if(count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items'])
							{
								$priceAnount3 += $price;
							}
							$loop1++;
							$tempFromHour++;
						}
						if($promo > 0)
						{
							$totalPromo += $promo * ($toHour - $fromHour);
							$checkPromo = true;
							$loop2 = 0;
							$tempFromHour2 = $fromHour;
							while($tempFromHour2 < $toHour)
							{
								//last minute
								if(count($arDiscount1) && $loop2 < $arDiscount1['discount_max_items'])
								{
									$promoAnount1 += $promo;
								}
								//first moment
								if(count($arDiscount2) && $loop2 < $arDiscount2['discount_max_items'])
								{
									$promoAnount2 += $promo;
								}
								//first moment
								if(count($arDiscount3) && $loop2 < $arDiscount3['discount_max_items'])
								{
									$promoAnount3 += $promo;
								}
								$loop2++;
								$tempFromHour2++;
							}
						}
						else
						{
							$totalPromo += $price * ($toHour - $fromHour);
							$loop3 = 0;
							$tempFromHour3 = $fromHour;
							while($tempFromHour3 < $toHour)
							{
								//last minute
								if(count($arDiscount1) && $loop3 < $arDiscount1['discount_max_items'])
								{
									$promoAnount1 += $price;
								}
								//first moment
								if(count($arDiscount2) && $loop3 < $arDiscount2['discount_max_items'])
								{
									$promoAnount2 += $price;
								}
								if(count($arDiscount3) && $loop3 < $arDiscount3['discount_max_items'])
								{
									$promoAnount3 += $price;
								}
								$loop3++;
								$tempFromHour3++;
							}
						}
						$totalHours = $toHour - $fromHour;
						$totalDays = 1;
						//asign for final price
						$finalDayPrice = $price;
						$finalDayPromo = $promo;
						$countDiscount = $loop1;
					}
				}
				$tempMaxItem1 = (isset($arDiscount1['discount_max_items']) &&  $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $totalHours) ? $arDiscount1['discount_max_items'] : $totalHours;
				$tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $totalHours) ? $arDiscount2['discount_max_items'] : $totalHours;
				$tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $totalHours) ? $arDiscount3['discount_max_items'] : $totalHours;
				if(count($paramAddons))
				{
					foreach($paramAddons as $keyAd1 => $paramAddon)
					{
						$tempParamAddons = array($keyAd1=>$paramAddon);
						$arAddonPrice = $this->getAddonsPrice($tempParamAddons,$booking->getId());
						if(count($arAddonPrice))
						{
							if($arAddonPrice['error'] == '')
							{
								if($arAddonPrice['price_type'] == 1)
								{
									$totalPrice += $arAddonPrice['price'] * $totalHours;
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
										$totalPromo += $arAddonPrice['price'] * $totalHours;
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
			}
			else
			{
				//get data from booking
				$i = 0;
				$loop1 = 0;
				$loop2 = 0;
				$loop3 = 0;
				while($intCheckIn <= $intCheckOut)
				{
					$strDay = date('Y-m-d',$intCheckIn);
					$numberDayOfWeek = date('w',$intCheckIn);
					if(in_array($numberDayOfWeek,$disableDays))
					{
						$strError = __('Dates are not available. System is closed on %1.',$strDisableTextDays);
						break;
					}
					$calendar = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType());
					if(!$calendar->getId())
					{
						$strError = __('Dates are not available. Please check again');
					}
					if($calendar->getId())
					{
						//total order quantity
						$totalOrder = $this->getOrderTotalQuantity($strDay,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
						//get total booking Item in cart
						$cartQty = $this->getTotalItemInCart($booking->getId(),$booking->getBookingTime(),$strDay,$itemId);
						$avaliableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty);
						if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block') || $avaliableQty < 0)
						{
							$strError = __('Dates are not available. Please check again');
							break;
						}
					}
					$price = $calendar->getCalendarPrice();
					$promo = $calendar->getCalendarPromo();
					$tempTotalhours = 0;
					//over fee night 
					$feeNight = 0;
					if($i == 0)
					{
						// $tempTotalhours = $hourEnd - $fromHour;
						if($intFromHour < $intToHour)
						{
							$tempTotalhours = $toHour - $fromHour;
							//echo $tempTotalhours;
						}
						else
                        {
                            $tempTotalhours = 24 - $fromHour + $toHour;
                        }
						$feeNight = $booking->getBookingFeeNight();
					}
					//last day
					elseif($intCheckIn == $intCheckOut)
					{
						if($toHour > $hourStart)
						{
							$tempTotalhours = $toHour - $hourStart;
							$finalDayPrice = $price;
							$finalDayPromo = $promo;
						}
					}
					//middle day
					else
					{
						// $tempTotalhours = $hourEnd - $hourStart;
						if($intHourEnd >= $intHourStart)
						{
							$tempTotalhours = $hourEnd - $hourStart;
						}
						else
						{
							$tempTotalhours += 24 - $fromHour + $toHour;
						}
						$feeNight = $booking->getBookingFeeNight();
					}
					$totalPrice += $price * $tempTotalhours + $feeNight;
					//discount price 
					if((count($arDiscount1) && $loop1 < $arDiscount1['discount_max_items']) || (count($arDiscount2) && $loop1 < $arDiscount2['discount_max_items']) || (count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items']))
					{
						for($tempLoop = 0; $tempLoop < $tempTotalhours; $tempLoop++)
						{
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
							//first moment
							if(count($arDiscount3) && $loop1 < $arDiscount3['discount_max_items'])
							{
								$priceAnount3 += $price;
							}
							$loop1++;
						}
					}
					if($promo > 0)
					{
						$totalPromo += $promo * $tempTotalhours + $feeNight;
						$checkPromo = true;
						//discount for promo price
						if((count($arDiscount1) && $loop2 < $arDiscount1['discount_max_items']) || (count($arDiscount2) && $loop2 < $arDiscount2['discount_max_items']) || (count($arDiscount3) && $loop2 < $arDiscount3['discount_max_items']))
						{
							for($tempLoop = 0; $tempLoop < $tempTotalhours; $tempLoop++)
							{
								//last minute
								if(count($arDiscount1) && $loop2 < $arDiscount1['discount_max_items'])
								{
									$promoAnount1 += $promo;
								}
								//first moment
								if(count($arDiscount2) && $loop2 < $arDiscount2['discount_max_items'])
								{
									$promoAnount2 += $promo;
								}
								if(count($arDiscount3) && $loop2 < $arDiscount3['discount_max_items'])
								{
									$promoAnount3 += $promo;
								}
								$loop2++;
							}
						}
					}
					else
					{
						$totalPromo += $price * $tempTotalhours + $feeNight;
						//discount for promo price
						if((count($arDiscount1) && $loop3 < $arDiscount1['discount_max_items']) || (count($arDiscount2) && $loop3 < $arDiscount2['discount_max_items']) || (count($arDiscount3) && $loop3 < $arDiscount3['discount_max_items']))
						{
							for($tempLoop = 0; $tempLoop < $tempTotalhours; $tempLoop++)
							{
								//last minute
								if(count($arDiscount1) && $loop3 < $arDiscount1['discount_max_items'])
								{
									$promoAnount1 += $price;
								}
								//first moment
								if(count($arDiscount2) && $loop3 < $arDiscount2['discount_max_items'])
								{
									$promoAnount2 += $price;
								}
								if(count($arDiscount3) && $loop3 < $arDiscount3['discount_max_items'])
								{
									$promoAnount3 += $price;
								}
								$loop3++;
							}
						}
					}
					//echo $tempTotalhours;
					$totalHours +=  $tempTotalhours;
					$totalDays++;
					$intCheckIn += $oneDay;
					$i++;
				}
				$tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $totalHours) ? $arDiscount1['discount_max_items'] : $totalHours;
				$tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $totalHours) ? $arDiscount2['discount_max_items'] : $totalHours;
				$tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $totalHours) ? $arDiscount3['discount_max_items'] : $totalHours;
				if(count($paramAddons))
				{
					foreach($paramAddons as $keyAd2 => $paramAddon)
					{
						$tempParamAddons = array($keyAd2=>$paramAddon);
						$arAddonPrice = $this->getAddonsPrice($tempParamAddons,$booking->getId());
						if(count($arAddonPrice))
						{
							if($arAddonPrice['error'] == '')
							{
								if($arAddonPrice['price_type'] == 1)
								{
									$totalPrice += $arAddonPrice['price'] * $totalHours;
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
											$promoAnount3 += $arAddonPrice['price'] * $tempMaxItem3 ;
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
										$totalPromo += $arAddonPrice['price'] * $totalHours;
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
			// if customer book small than minimum hour. I add note and get price for minimum hour. I get price of final day
			if($totalHours < $miniHours && $miniHours > 0)
			{
				$strNote = __('Note : Minimum Hours is %1 You must pay for %s hours',$miniHours,$miniHours);
				//add money for hours
				$addHours = $miniHours - $totalHours;
				$totalPrice += $addHours * $finalDayPrice;
				$totalPromo += $addHours * $finalDayPromo;
			}
			//if customer book lager than maximum hours add error
			if($totalHours > $maxHours && $maxHours > 0)
			{
				$strError = __('Maximum Hours are %1, please check again!',$maxHours);
			}
			$servicePrice = 0;
			$servicePromo = 0;
			$salePrice = 0;
			$salePromo = 0;
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
				if(count($arDiscount3) && $totalHours >= $arDiscount3['discount_period'])
				{
					$tempTotalPrice = $totalPrice - $salePrice;
					$salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$tempTotalPrice,$priceAnount3,$tempMaxItem3);
					if($checkPromo && $totalPromo > 0)
					{
						$tempTotalPromo = $totalPromo - $salePromo;
						$salePromo += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$tempTotalPromo,$promoAnount3,$tempMaxItem3);
					}
					$tempFinalDiscount = $checkPromo ? $salePromo : $salePrice;
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
		}
		return array(
			'total_price'=>$totalPrice,
			'total_promo'=>$totalPromo,
			'total_days'=>$totalDays,
			'total_hours'=>$totalHours,
			'str_error'=>$strError,
			'str_note'=>$strNote,
			'msg_discount'=>$msgDiscount,
            'check_in'=>$newCheckIn,
            'check_out'=>$newCheckOut
		);
	}
	/**
	 * get Price Per time
	 * */
	function getPricePerTime($booking,$checkIn,$checkOut,$timeStart,$timeEnd,$qty = 1,$itemId = 0,$paramAddons = array(),$oldOrderItemId = 0)
    {
        $calendarsModel = $this->_calendarsFactory->create();
        $intCheckIn = strtotime($checkIn);
        $strDay = $checkIn;
        $numberDayOfWeek = date('w',$intCheckIn);
        $strError = '';
        $intStartTime = strtotime($timeStart.':00');
        $intEndTime = strtotime($timeEnd.':00');
        $disableDays = trim($booking->getDisableDays());
        $totalPrice = 0;
        $totalPromo = 0;
        $defaultLength = $booking->getBookingMinHours();
        $fixedLength = $booking->getBookingMaxHours();
        $amountTime = $booking->getBookingFeeNight();
        if($fixedLength > 0)
        {
            $intEndTime =  $intStartTime + ($fixedLength * $amountTime * 60);
            $timeEnd = date('H:i',$intEndTime);
        }
        if($defaultLength > 0 && $fixedLength == 0)
        {
            $amountTime *= $defaultLength;
        }
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
        if($intStartTime >= $intEndTime)
        {
            $strError =  __('Time is not available, Please check again!');
        }
        elseif(in_array($numberDayOfWeek,$disableDays))
        {

            $strError = __('Dates are not available. System is closed on %1.',$strDisableTextDays);
        }
        else
        {
            $calendar = $calendarsModel->getCalendarBetweenDays($booking->getId(),$strDay,'per_day');
            if(!$calendar->getId())
            {
                $strError = __('Dates are not available. Please check again');
            }
            if($calendar->getId())
            {
                $priceAnount1 = 0; // price for last minute amount
                $promoAnount1 = 0; // price last minute
                $priceAnount2 = 0; // price for first moment
                $promoAnount2 = 0; // price last first moment
                $priceAnount3 = 0;
                $promoAnount3 = 0;
                $oneDay = 60*60*24;
                $intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
                $today = date('Y-m-d',$intCoreCurrentTime);
                $intToday = strtotime($today);
                $dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
                $discountModel = $this->_discountsFactory->create();
                $arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
                $arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
                $numberTime = ($intEndTime - $intStartTime) / ($amountTime * 60);
                $arDiscount3 = $discountModel->getLengthDiscount($booking->getId(),'per_day',$numberTime);
                //total order quantity
                $totalOrder = $this->getOrderTotalQuantity($strDay,$booking->getId(),$booking->getBookingTime(),$oldOrderItemId);
                //get total booking Item in cart
                $cartQty = $this->getTotalItemInCart($booking->getId(),$booking->getBookingTime(),$strDay,$itemId);
                $availableQty = $calendar->getCalendarQty() -  ($qty + $totalOrder + $cartQty);
                if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block') || $availableQty < 0)
                {
                    $strError = __('Dates are not available. Please check again');
                }
                if($strError == '') {
                    $price = $calendar->getCalendarPrice();
                    $promo = $calendar->getCalendarPromo();
                    $totalPrice = $price * $numberTime;
                    $checkPromo = false;
                    if ($promo > 0) {
                        $totalPromo = $numberTime * $promo;
                        $promoAnount1 = $totalPromo;
                        $promoAnount2 = $totalPromo;
                        $checkPromo = true;
                    }
                    if($totalPrice > 0)
                    {
                        $maxDiscount1  = (count($arDiscount1) && $arDiscount1['discount_max_items'] > 0) ? $arDiscount1['discount_max_items'] : $numberTime;
                        $maxDiscount2  = (count($arDiscount2) && $arDiscount2['discount_max_items'] > 0) ? $arDiscount2['discount_max_items'] : $numberTime;
                        $maxDiscount3  = (count($arDiscount3) && $arDiscount3['discount_max_items'] > 0) ? $arDiscount3['discount_max_items'] : $numberTime;
                        for($i = 0; $i < $numberTime; $i++)
                        {

                            if(count($arDiscount1) && $i < $maxDiscount1)
                            {
                                $priceAnount1 += $price;
                                $promoAnount1 += $promo > 0 ? $promo : $price;
                            }
                            if(count($arDiscount2) && $i < $maxDiscount2)
                            {
                                $priceAnount2 += $price;
                                $promoAnount2 += $promo > 0 ? $promo : $price;
                            }
                            if(count($arDiscount3) && $i < $maxDiscount3)
                            {
                                $priceAnount3 += $price;
                                $promoAnount3 += $promo > 0 ? $promo : $price;
                            }
                        }
                        if(count($paramAddons))
                        {
                            foreach($paramAddons as $keyAd2 => $paramAddon)
                            {
                                $tempParamAddons = array($keyAd2=>$paramAddon);
                                $arAddonPrice = $this->getAddonsPrice($tempParamAddons,$booking->getId());
                                if(count($arAddonPrice))
                                {
                                    if($arAddonPrice['error'] == '')
                                    {
                                        if($arAddonPrice['price_type'] == 1)
                                        {
                                            $totalPrice += $arAddonPrice['price'] * $numberTime;
                                        }
                                        else
                                        {
                                            $totalPrice += $arAddonPrice['price'];
                                        }
                                        if(count($arDiscount1))
                                        {
                                            if($arAddonPrice['price_type'] == 1)
                                            {
                                                $priceAnount1 += $arAddonPrice['price'] * $maxDiscount1;
                                                if($checkPromo)
                                                    $promoAnount1 += $arAddonPrice['price'] * $maxDiscount1;
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
                                                $priceAnount2 += $arAddonPrice['price'] * $maxDiscount2;
                                                if($checkPromo)
                                                    $promoAnount2 += $arAddonPrice['price'] * $maxDiscount2;
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
                                                $priceAnount3 += $arAddonPrice['price'] * $maxDiscount3;
                                                if($checkPromo)
                                                    $promoAnount3 += $arAddonPrice['price'] * $maxDiscount3;
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
                                                $totalPromo += $arAddonPrice['price'] * $numberTime;
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
                        $okLastMinute = false;
                        $okFirstMoment = false;
                        if($priceAnount1 > 0)
                        {
                            $okLastMinute = true;
                        }
                        if($priceAnount2 > 0)
                        {
                            $okFirstMoment = true;
                        }
                        if($okLastMinute && $okFirstMoment)
                        {
                            $okFirstMoment = false;
                            if($arDiscount2['discount_priority'] < $arDiscount1['discount_priority'])
                            {
                                $okFirstMoment = true;
                                $okLastMinute = false;
                            }
                        }
                        if($okLastMinute)
                        {
                            $salePrice += $discountModel->getPriceDiscounts($arDiscount1['discount_max_items'],$arDiscount1['discount_amount'],$arDiscount1['discount_amount_type'],$totalPrice,$priceAnount1,$maxDiscount1);
                            $salePromo += $discountModel->getPriceDiscounts($arDiscount1['discount_max_items'],$arDiscount1['discount_amount'],$arDiscount1['discount_amount_type'],$totalPromo,$promoAnount1,$maxDiscount1);
                        }
                        elseif($okFirstMoment)
                        {
                            $salePrice += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPrice,$priceAnount2,$maxDiscount2);
                            $salePromo += $discountModel->getPriceDiscounts($arDiscount2['discount_max_items'],$arDiscount2['discount_amount'],$arDiscount2['discount_amount_type'],$totalPromo,$promoAnount2,$maxDiscount2);
                        }
                        if(count($arDiscount3) && $priceAnount3 > 0)
                        {
                            $tempTotalPrice = $totalPrice - $salePrice;
                            $salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$tempTotalPrice,$priceAnount3,$maxDiscount3);
                            if($checkPromo && $totalPromo > 0)
                            {
                                $tempTotalPromo = $totalPromo - $salePromo;
                                $salePromo += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$tempTotalPromo,$promoAnount3,$maxDiscount3);
                            }
                        }
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
                    }
                }
            }
        }
        return array(
            'total_price'=>$totalPrice,
            'total_promo'=>$totalPromo,
            'total_days'=>0,
            'total_hours'=>0,
            'str_error'=>$strError,
            'str_note'=>'',
            'msg_discount'=>'',
            'service_start'=>$timeStart,
            'service_end'=>$timeEnd,
            'number_times'=>$amountTime
        );
    }
	/**
	* get Qty of a day from booking order
	* @param string $strDay ,$bookingTime, int $bookingId, 
	* @return int $totalQuantity
	**/
	function getOrderTotalQuantity($strDay,$bookingId,$bookingTime = 1,$oldOrderItemId = 0)
	{
		// $arraySelect = array('check_in','check_out','qty');
		$arSelectOrder = array();
		$arAttributeConditions = array();
		$condition = 'booking_id = '.$bookingId;
		$arrayBooking = array('bkorder_check_in','bkorder_check_out','bkorder_qty','bkorder_order_id','bkorder_qt_item_id');
		$totalOrder = 0;
		$orderModel = $this->_bookingorders->create();
		$orders = $orderModel->getCollection()
				->addFieldToSelect($arrayBooking)
				->addFieldToFilter('bkorder_booking_id',$bookingId)
				->addFieldToFilter('bkorder_room_id',0);
		if(count($orders))
		{
			foreach($orders as $order)
			{
				$bkCoreModel = $this->_bkCoreOrders;
				$defaultOrderCollection = $bkCoreModel->getCollection()
					->addFieldToFilter('entity_id',$order->getBkorderOrderId());
				// $defaultOrder = $bkCoreModel->load($order->getBkorderOrderId());
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
				if($oldOrderItemId > 0)
				{
					if($order->getBkorderQtItemId() == $oldOrderItemId)
					{
						continue;
					}
				}
				if($bookingTime == 1)
				{
					if(strtotime($order->getBkorderCheckIn()) <= strtotime($strDay) && strtotime($order->getBkorderCheckOut()) > strtotime($strDay))
					{
						$totalOrder += $order->getBkorderQty();
					}
				}
				else
				{
					if(strtotime($order->getBkorderCheckIn()) <= strtotime($strDay) && strtotime($order->getBkorderCheckOut()) >= strtotime($strDay))
					{
						$totalOrder += $order->getBkorderQty();
					}
				}
				
			}
		}
		return $totalOrder;
	}
	/**
	* get total booking item in cart
	* @param $booking item, string $strDay has format is Y-m-d,$itemId
	* @return int $total
	**/
	function getTotalItemInCart($bookingId,$bookingTime,$strDay,$itemId = 0)
	{
		$total = 0;
		$isBackend = false;
		if($this->_bkbackendSession->isLoggedIn())
		{
			$isBackend = true;
		}
		$arrayItem = $this->_bkOrderHelper->getArrayItemIncart($bookingId,$itemId,$isBackend);
		//convert time to int
		$timeDay = strtotime($strDay);
		foreach($arrayItem as $item)
		{
			$intCheckIn = strtotime($item['check_in']);
			$intCheckOut = strtotime($item['check_out']);
			//if booking time is daily , I do not get total of final day.
			if($bookingTime == 1)
			{
				$intCheckOut -= 60*60*24;
			}
			if($bookingTime == 5)
            {
                $tempQty = $item['qty'];
                $persons = isset($item['number_persons']) ? $item['number_persons'] : array();
                if(count($persons))
                {
                    $tempQty = 0;
                    foreach ($persons as $person)
                    {
                        if((int)$person > 0)
                        {
                            $tempQty += $person;
                        }
                    }
                }
                $total += $tempQty;
            }
            else
            {
                if($intCheckIn <= $timeDay && $timeDay <= $intCheckOut)
                {
                    $total += $item['qty'];
                }
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
            $optionValueModel = $this->_dropdownFactory->create();
			foreach($addonsSells as $addonsSell)
			{
				if(array_key_exists($addonsSell['option_id'],$paramAddons))
				{
					if($addonsSell['option_required'] == 1)
					{
						if($addonsSell['option_type'] == 1 || $addonsSell['option_type'] == 2 || $addonsSell['option_type'] == 4)
						{
							if(trim($paramAddons[$addonsSell['option_id']]) == '')
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
					    $optionsValueItems = $optionValueModel->getBkValueOptions($addonsSell['option_id'],array('dropdown_price'));
					    foreach ($optionsValueItems as $optionsValueItem)
                        {
                            if($optionsValueItem->getId() == $paramAddons[$addonsSell['option_id']])
                            {
                                $price  += (float)$optionsValueItem->getDropdownPrice();
                                break;
                            }
                        }

					}
					elseif($addonsSell['option_type'] == 3 || $addonsSell['option_type'] == 5)
					{
						$paramAddonMulties = $paramAddons[$addonsSell['option_id']];
                        $optionsValueItems = $optionValueModel->getBkValueOptions($addonsSell['option_id'],array('dropdown_price'));
                        foreach ($optionsValueItems as $optionsValueItem)
                        {
                            if(in_array($optionsValueItem->getId(),$paramAddonMulties))
                            {
                                $price  += (float)$optionsValueItem->getDropdownPrice();
                            }
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
 