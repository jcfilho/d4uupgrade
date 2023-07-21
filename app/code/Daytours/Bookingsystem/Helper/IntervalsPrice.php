<?php 

namespace Daytours\Bookingsystem\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Backend\Model\Auth\Session as BkBackendSession;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkOrderHelper;

class IntervalsPrice extends \Magebay\Bookingsystem\Helper\IntervalsPrice
{
	/**
     *
     * @var /Magento\Framework\Stdlib\DateTime\Timezone
    */
	protected $_timezone;
	/**
     *
     * @var Magento\Backend\Model\Auth\Session
    */
	protected $_bkbackendSession;
	/**
	* @var \Magento\Catalog\Model\Calendars
	**/
	protected $_calendarsFactory;
	/**
	* @var \Magento\Catalog\Model\OptionsFactory
	**/
	protected $_optionsFactory;
    /**
     * @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
     */
    protected $_dropdownFactory;
	/**
     *  Model
     *
     * @var \Magebay\Bookingsystem\Model\DiscountsFactory
    */
	protected $_discountsFactory;
	/**
     *  Model
     *
     * @var \Magebay\Bookingsystem\Model\IntervalhoursFactory
    */
	protected $_intervalhoursFactory;
	/**
     * Helper Date
     *
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/**
     * Bk Order Helper
     *
     * @var \Magebay\Bookingsystem\Helper\BkOrderHelper
    */
	protected $_bkOrderHelper;
    /**
     * @var \Daytours\Wishlist\Helper\Data
     */
    private $helperWishList;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $http;

    public function __construct(
       Context $context,
	   Timezone $timezone,
	   BkBackendSession $bkbackendSession,
       CalendarsFactory $calendarsFactory,
       OptionsFactory $optionsFactory,
       OptionsdropdownFactory $optionsdropdownFactory,
	   DiscountsFactory $discountsFactory,
	   BkHelperDate $bkHelperDate,
	   BkOrderHelper $bkOrderHelper,
	   IntervalhoursFactory $intervalhoursFactory,
       \Daytours\Wishlist\Helper\Data $helperWishList,
       \Magento\Framework\App\Request\Http $http
    ) 
	{
       parent::__construct(
           $context,
           $timezone,
           $bkbackendSession,
           $calendarsFactory,
           $optionsFactory,
           $optionsdropdownFactory,
           $discountsFactory,
           $bkHelperDate,
           $bkOrderHelper,
           $intervalhoursFactory
       );
	   $this->_timezone = $timezone;
	   $this->_bkbackendSession = $bkbackendSession;
	   $this->_calendarsFactory = $calendarsFactory;
	   $this->_optionsFactory = $optionsFactory;
	   $this->_dropdownFactory = $optionsdropdownFactory;
	   $this->_discountsFactory = $discountsFactory;
	   $this->_bkHelperDate = $bkHelperDate;
	   $this->_bkOrderHelper = $bkOrderHelper;
	   $this->_intervalhoursFactory = $intervalhoursFactory;
        $this->helperWishList = $helperWishList;
        $this->http = $http;
    }

    /** get price from check in to check out
     * @params object $booking, string $checkIn,$checkOut (format Y-m-d), array $serviceIds int $itemId when edit item in cart
     * @return array $prices
     **/
    function getIntervalsHoursPrice($booking,$checkIn,$qty = 1,$intervalsHours,$itemId = 0,$paramAddons = array(),$oldOrderItemId = 0)
    {
        $formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
        //convert date to int
        $intCheckIn = strtotime($checkIn);
        $tempCheckIn = $intCheckIn;
        $oneDay = 24 * 60 * 60;
        $minimum = $booking->getBookingMinDays();
        $maximum = $booking->getBookingMaxDays();
        $totalPrice = 0;
        $totalPromo = 0;
        $checkPromo = false;
        $totalDays = 1;
        $strError = '';
        $strNote = '';
        $totalItem = 0;
        $intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
        $today = date('Y-m-d',$intCoreCurrentTime);
        $intToday = strtotime($today);
        $totalSaving = 0;
        $priceAnount1 = 0; // price for last minute amount
        $promoAnount1 = 0; // price last minute
        $priceAnount2 = 0; // price for first moment
        $promoAnount2 = 0; // price last first moment
        $arDiscount  = array();
        $arDiscount1 = array();
        $arDiscount2 = array();
        $arDiscount3 = array();
        $msgDiscount = '';
        $numberIntervals = 0;
        $loop1 = 0;
        //max item for kind of type
        $tempMaxItem1 = 0;
        $tempMaxItem2 = 0;
        $tempMaxItem3 = 0;
        $isBackend = false;
        if($this->_bkbackendSession->isLoggedIn())
        {
            $isBackend = true;
        }
        if($intCheckIn < $intToday)
        {
            $strError = __('You can not book previous day!');
        }
        else
        {
            $dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
            $priceAnount1 = 0; // price for last minute amount
            $promoAnount1 = 0; // price last minute
            $priceAnount2 = 0; // price for first moment
            $promoAnount2 = 0; // price last first moment
            $priceAnount3 = 0;
            $promoAnount3 = 0;
            $discountModel = $this->_discountsFactory->create();
            $arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
            $arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
            $arDiscount3 =  $discountModel->getLengthDiscount($booking->getId(),'per_day',count($intervalsHours));
            $intervalModel = $this->_intervalhoursFactory->create();
            $calendarModel = $this->_calendarsFactory->create();
            foreach($intervalsHours as $intervalsHour)
            {
                //$interVal = $intervalModel->getInervalsQty($booking->getId(),$checkIn,$intervalsHour);
                $intervalItem = $intervalModel->load($intervalsHour);
                $interTotal = 0;
                if($intervalItem && $intervalItem->getId())
                {
                    $interTotal = $intervalItem->getIntervalhoursQuantity();
                }
                //get quantity from order
                $interOrdertotal = $this->_bkOrderHelper->getOrderIntervalsTotal($booking->getId(),$checkIn,$intervalsHour,$oldOrderItemId);
                //get total qty in $cart
                if(!$interOrdertotal)
                {
                    $interOrdertotal = 0;
                }
                $totalQtyInCart = $this->_bkOrderHelper->getTotalInterItemInCart($booking->getId(),$checkIn,$intervalsHour,$itemId,$isBackend);
                $interTotal = $interTotal - ($interOrdertotal + $totalQtyInCart);
                if($interTotal < $qty)
                {
                    $strError = __('Hour interval is not available, Please check again');
                    break;
                }
                $strDay = date('Y-m-d',$intCheckIn);
                //$arPrice = $this->getPriceOfDay($booking->getId(),$strDay,$booking->getBookingType());
                $calendar = $calendarModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType());
                if(!$calendar->getId())
                {
                    $strError = __('Dates are not available. Please check again');
                    break;
                }
                if($calendar->getId())
                {
                    if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block'))
                    {
                        $strError = __('Dates are not available. Please check again');
                        break;
                    }
                }
                $price = $calendar->getCalendarPrice();
                $promo = 0;
                $totalItem++;
                if($booking->getBookingTypeIntevals() != 1)
                {
                    $tempInterDate = $checkIn;
                    if($this->checkDefaultPrice($checkIn))
                    {
                        $tempInterDate = '';
                    }
                    if($intervalItem && $intervalItem->getId())
                    {
                        $price = $intervalItem->getIntervalhoursPrice();
                        $promo = $promo = ($intervalItem->getIntervalhoursSpecialPrice() != null && $intervalItem->getIntervalhoursSpecialPrice() > 0) ? $intervalItem->getIntervalhoursSpecialPrice() : 0;;
                    }

                }
                $totalPrice += $price;

                if($promo > 0)
                {
                    $totalPromo += $promo;
                    $checkPromo = true;

                }

                $loop1++;
                $numberIntervals++;
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
        $tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $numberIntervals) ? $arDiscount1['discount_max_items'] : $numberIntervals;
        $tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $numberIntervals) ? $arDiscount2['discount_max_items'] : $numberIntervals;
        $tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $numberIntervals) ? $arDiscount3['discount_max_items'] : $numberIntervals;
        if(count($paramAddons))
        {
            foreach($paramAddons as $keyAdd => $paramAddon)
            {
                $tempParamAddons = array($keyAdd=>$paramAddon);
                $arAddonPrice = $this->getAddonsPrice($tempParamAddons,$booking->getId());
                if(count($arAddonPrice))
                {
                    if($arAddonPrice['error'] == '')
                    {
                        if($arAddonPrice['price_type'] == 1)
                        {
                            $totalPrice += $arAddonPrice['price'] * $numberIntervals;
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
                                $totalPromo += $arAddonPrice['price'] * $numberIntervals;
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
        //sale price
        $salePrice = 0;
        $salePromo = 0;
        $totalSaving = 0;
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
            if($oklastMinute && $okFirstMoment)
            {
                if($arDiscount1['discount_priority'] > $arDiscount2['discount_priority'])
                {
                    $okFirstMoment = true;
                    $oklastMinute = false;

                }
                else
                {
                    $oklastMinute = true;
                    $okFirstMoment = false;
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
            if(count($arDiscount3) && $numberIntervals >= $arDiscount3['discount_period'])
            {
                $salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPrice,$priceAnount3,$tempMaxItem3);
                if($checkPromo && $totalPromo > 0)
                {
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
            'total_items'=>$totalItem
        );
    }

    function getIntervalsHoursPriceTwoCalendar($booking,$checkIn,$checkInTwo,$qty = 1,$intervalsHours,$intervalsHoursTwo,$itemId = 0,$paramAddons = array(),$oldOrderItemId = 0,$isRoundTrip = null)
    {
        $params = $this->_getRequest()->getParams();

        //if comes from checkout
        if( !is_null($isRoundTrip) ){
            $params['isRoundTrip'] = $isRoundTrip;
        }
        //----

        $calendaNumberOne = \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_DEFAULT;
        $calendaNumberTwo = \Daytours\Bookingsystem\Block\Transfer::CALENDAR_NUMBER_BY_SECOND;
        $formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
        //convert date to int
        $intCheckIn = strtotime($checkIn);
        $intCheckInTwo = strtotime($checkInTwo);
        $tempCheckIn = $intCheckIn;
        $tempCheckInTwo = $intCheckInTwo;
        $oneDay = 24 * 60 * 60;
        $minimum = $booking->getBookingMinDays();
        $maximum = $booking->getBookingMaxDays();
        $totalPrice = 0;
        $totalPromo = 0;
        $checkPromo = false;
        $totalDays = 1;
        $strError = '';
        $strNote = '';
        $totalItem = 0;
        $intCoreCurrentTime = $this->_timezone->scopeTimeStamp();
        $today = date('Y-m-d',$intCoreCurrentTime);
        $intToday = strtotime($today);
        $totalSaving = 0;
        $priceAnount1 = 0; // price for last minute amount
        $promoAnount1 = 0; // price last minute
        $priceAnount2 = 0; // price for first moment
        $promoAnount2 = 0; // price last first moment
        $arDiscount  = array();
        $arDiscount1 = array();
        $arDiscount2 = array();
        $arDiscount3 = array();
        $msgDiscount = '';
        $numberIntervals = 0;
        $loop1 = 0;
        //max item for kind of type
        $tempMaxItem1 = 0;
        $tempMaxItem2 = 0;
        $tempMaxItem3 = 0;
        $isBackend = false;
        if($this->_bkbackendSession->isLoggedIn())
        {
            $isBackend = true;
        }
        if($intCheckIn < $intToday)
        {
            $strError = __('You can not book previous day!');
        }
        else
        {
            $dmDays = ($intCheckIn + $oneDay - $intToday) / $oneDay;
            $priceAnount1 = 0; // price for last minute amount
            $promoAnount1 = 0; // price last minute
            $priceAnount2 = 0; // price for first moment
            $promoAnount2 = 0; // price last first moment
            $priceAnount3 = 0;
            $promoAnount3 = 0;
            $discountModel = $this->_discountsFactory->create();
            $arDiscount1 = $discountModel->getLastMinuteDiscount($booking->getId(),'per_day',$dmDays);
            $arDiscount2 =  $discountModel->getFirstMommentDiscount($booking->getId(),'per_day',$dmDays);
            $arDiscount3 =  $discountModel->getLengthDiscount($booking->getId(),'per_day',count($intervalsHours));
            $intervalModel = $this->_intervalhoursFactory->create();
            $calendarModel = $this->_calendarsFactory->create();
            foreach($intervalsHours as $intervalsHour)
            {
                //$interVal = $intervalModel->getInervalsQty($booking->getId(),$checkIn,$intervalsHour);
                $intervalItem = $intervalModel->load($intervalsHour);
                $interTotal = 0;
                if($intervalItem && $intervalItem->getId())
                {
                    $interTotal = $intervalItem->getIntervalhoursQuantity();
                }
                //get quantity from order
                $interOrdertotal = $this->_bkOrderHelper->getOrderIntervalsTotal($booking->getId(),$checkIn,$intervalsHour,$oldOrderItemId);
                //get total qty in $cart
                if(!$interOrdertotal)
                {
                    $interOrdertotal = 0;
                }
                $totalQtyInCart = $this->_bkOrderHelper->getTotalInterItemInCart($booking->getId(),$checkIn,$intervalsHour,$itemId,$isBackend);
                $interTotal = $interTotal - ($interOrdertotal + $totalQtyInCart);
                if($interTotal < $qty)
                {
                    $strError = __('Hour interval is not available, Please check again');
                    break;
                }
                $strDay = date('Y-m-d',$intCheckIn);
                //$arPrice = $this->getPriceOfDay($booking->getId(),$strDay,$booking->getBookingType());
                $calendar = $calendarModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType(),$calendaNumberOne);
                if(!$calendar->getId())
                {
                    $strError = __('Dates are not available. Please check again');
                    break;
                }
                if($calendar->getId())
                {
                    if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block'))
                    {
                        $strError = __('Dates are not available. Please check again');
                        break;
                    }
                }
                $price = $calendar->getCalendarPrice();
                //$promo = $calendar->getCalendarPromo();
                $promo = 0;
                $totalItem++;
                if($booking->getBookingTypeIntevals() != 1)
                {
                    $tempInterDate = $checkIn;
                    if($this->checkDefaultPrice($checkIn))
                    {
                        $tempInterDate = '';
                    }
                    if($intervalItem && $intervalItem->getId())
                    {
                        $price = $intervalItem->getIntervalhoursPrice();
                        $promo = ($intervalItem->getIntervalhoursSpecialPrice() != null && $intervalItem->getIntervalhoursSpecialPrice() > 0) ? $intervalItem->getIntervalhoursSpecialPrice() : 0;
                    }

                }
                $totalPrice += $price;
                if($promo > 0)
                {
                    $totalPromo += $promo;
                    $checkPromo = true;
                }
                else
                {
                    //$totalPromo += $price;
                }
                $loop1++;
                $numberIntervals++;
            }

            /*
             * Intervals hour calendar two
             * */
            if( $params['isRoundTrip'] ){
                foreach($intervalsHoursTwo as $intervalsHour)
                {
                    //$interVal = $intervalModel->getInervalsQty($booking->getId(),$checkIn,$intervalsHour);
                    $intervalItem = $intervalModel->load($intervalsHour);
                    $interTotal = 0;
                    if($intervalItem && $intervalItem->getId())
                    {
                        $interTotal = $intervalItem->getIntervalhoursQuantity();
                    }
                    //get quantity from order
                    $interOrdertotal = $this->_bkOrderHelper->getOrderIntervalsTotalTwo($booking->getId(),$checkInTwo,$intervalsHoursTwo,$oldOrderItemId);
                    //get total qty in $cart
                    if(!$interOrdertotal)
                    {
                        $interOrdertotal = 0;
                    }
                    $totalQtyInCart = $this->_bkOrderHelper->getTotalInterItemInCartTwo($booking->getId(),$checkInTwo,$intervalsHoursTwo,$itemId,$isBackend);
                    $interTotal = $interTotal - ($interOrdertotal + $totalQtyInCart);
                    if($interTotal < $qty)
                    {
                        $strError = __('Hour interval is not available, Please check again');
                        break;
                    }
                    $strDay = date('Y-m-d',$intCheckInTwo);
                    //$arPrice = $this->getPriceOfDay($booking->getId(),$strDay,$booking->getBookingType());
                    $calendar = $calendarModel->getCalendarBetweenDays($booking->getId(),$strDay,$booking->getBookingType(),$calendaNumberTwo);
                    if(!$calendar->getId())
                    {
                        $strError = __('Dates are not available. Please check again');
                        break;
                    }
                    if($calendar->getId())
                    {
                        if(($calendar->getCalendarStatus() == 'unavailable' || $calendar->getCalendarStatus() == 'block'))
                        {
                            $strError = __('Dates are not available. Please check again');
                            break;
                        }
                    }
                    $priceTwo = $calendar->getCalendarPrice();
                    $pricePromoCalendarTwo = 0;
                    $totalItem++;
                    if($booking->getBookingTypeIntevals() != 1)
                    {
                        $tempInterDate = $checkInTwo;
                        if($this->checkDefaultPrice($checkInTwo))
                        {
                            $tempInterDate = '';
                        }
                        if($intervalItem && $intervalItem->getId())
                        {
                            $priceTwo = $intervalItem->getIntervalhoursPrice();
                            $pricePromoCalendarTwo = ($intervalItem->getIntervalhoursSpecialPrice() != null && $intervalItem->getIntervalhoursSpecialPrice() > 0) ? $intervalItem->getIntervalhoursSpecialPrice() : 0;
                        }

                    }


                    if( $pricePromoCalendarTwo && $pricePromoCalendarTwo > 0 ){
                        if( $totalPromo > 0 ){
                            //exist promo on the first calendar
                            $totalPromo += $pricePromoCalendarTwo;
                        }else{
                            // doesn't exist promo on the first calendar, so, we have to equal $promo to $price (first calendar)
                            $totalPromo = $price;
                            $totalPromo += $pricePromoCalendarTwo;
                        }
                    }else if($totalPromo > 0){
                        //if exist promo on first calendar only
                        $totalPromo += $priceTwo;
                    }
                    $totalPrice += $priceTwo;

                    if($pricePromoCalendarTwo > 0)
                    {
                        //$totalPromo += $promo;
                        $checkPromo = true;
                    }
                    else
                    {
                        //$totalPromo += $price;
                    }
                    $loop1++;
                    $numberIntervals++;
                }
            }
            /*------------------*/

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
        $tempMaxItem1 = (isset($arDiscount1['discount_max_items']) && $arDiscount1['discount_max_items'] > 0 && $arDiscount1['discount_max_items'] < $numberIntervals) ? $arDiscount1['discount_max_items'] : $numberIntervals;
        $tempMaxItem2 = (isset($arDiscount2['discount_max_items']) && $arDiscount2['discount_max_items'] > 0 && $arDiscount2['discount_max_items'] < $numberIntervals) ? $arDiscount2['discount_max_items'] : $numberIntervals;
        $tempMaxItem3 = (isset($arDiscount3['discount_max_items']) && $arDiscount3['discount_max_items'] > 0 && $arDiscount3['discount_max_items'] < $numberIntervals) ? $arDiscount3['discount_max_items'] : $numberIntervals;
        if(count($paramAddons))
        {
            foreach($paramAddons as $keyAdd => $paramAddon)
            {
                $tempParamAddons = array($keyAdd=>$paramAddon);
                $arAddonPrice = $this->getAddonsPrice($tempParamAddons,$booking->getId());
                if(count($arAddonPrice))
                {
                    if($arAddonPrice['error'] == '')
                    {
                        if($arAddonPrice['price_type'] == 1)
                        {
                            $totalPrice += $arAddonPrice['price'] * $numberIntervals;
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
                                $totalPromo += $arAddonPrice['price'] * $numberIntervals;
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
        //sale price
        $salePrice = 0;
        $salePromo = 0;
        $totalSaving = 0;
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
            if($oklastMinute && $okFirstMoment)
            {
                if($arDiscount1['discount_priority'] > $arDiscount2['discount_priority'])
                {
                    $okFirstMoment = true;
                    $oklastMinute = false;

                }
                else
                {
                    $oklastMinute = true;
                    $okFirstMoment = false;
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
            if(count($arDiscount3) && $numberIntervals >= $arDiscount3['discount_period'])
            {
                $salePrice += $discountModel->getPriceDiscounts($arDiscount3['discount_max_items'],$arDiscount3['discount_amount'],$arDiscount3['discount_amount_type'],$totalPrice,$priceAnount3,$tempMaxItem3);
                if($checkPromo && $totalPromo > 0)
                {
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
            'total_items'=>$totalItem
        );
    }
}