<?php

namespace Daytours\RegularServices\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\IntervalsPrice;
use Magebay\Bookingsystem\Helper\RoomPrice;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Registry;
use Daytours\Wishlist\Helper\Data;
use Daytours\RegularServices\Model\Product\Type\Booking\Price;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class ChangePrice extends \Daytours\Bookingsystem\Observer\Frontend\ChangePrice
{

    /**
     * @var Price
     */
    protected $bookingPrice;
    /**
     * @var \Daytours\Bookingsystem\Helper\Data
     */
    private $dataBookingSystem;
    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $redirect;


    public function __construct(
        RequestInterface $request,
        PriceHelper $bkPriceHelper,
        BookingsFactory $bookingFactory,
        RoomsFactory $roomsFactory,
        BkHelperDate $bkHelperDate,
        BkSimplePriceHelper $bkSimplePriceHelper,
        IntervalsPrice $intervalsPrice,
        RoomPrice $roomPrice,
        Http $http,
        Registry $registry,
        Data $helperWishList,
        Price $bookingPrice,
        \Daytours\Bookingsystem\Helper\Data $dataBookingSystem,
        \Magento\Framework\App\Response\Http $redirect
    )
    {
        parent::__construct(
            $request,
            $bkPriceHelper,
            $bookingFactory,
            $roomsFactory,
            $bkHelperDate,
            $bkSimplePriceHelper,
            $intervalsPrice,
            $roomPrice,
            $http,
            $registry,
            $helperWishList
        );

        $this->bookingPrice = $bookingPrice;
        $this->dataBookingSystem = $dataBookingSystem;


        $this->redirect = $redirect;
    }

    public function execute(EventObserver $observer)
    {
        $enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
        if ($enable == 1) {

            //get product Id
            $_product = $observer->getEvent()->getData('product');
            $productId = $_product->getId();


            $basePriceWithOutBookingPrice = ($_product->getSpecialPrice() > 0) ? $_product->getSpecialPrice() : $_product->getPrice();

            //check booking product
            $bookingModel = $this->_bookingFactory->create();
            $booking = $bookingModel->getBooking($productId);
            if ($booking->getId() && $_product->getTypeId() == 'booking') {

                $formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
                $params = $this->_getBkRequest()->getParams();

                $price = 0;
                $qty = isset($params['qty']) ? $params['qty'] : 1;
                //get params
                $checkIn = '';
                $checkOut = '';
                $checkInTwo = '';
                $checkOutTwo = '';
                $arPrice = array();
                $strError = '';
                if ($booking->getBookingType() == 'per_day') {
                    if ($this->_bkHelperDate->validateBkDate($params['check_in'], $formatDate)) {
                        $checkIn = $this->_bkHelperDate->convertFormatDate($params['check_in']);
                    }
                    if (isset($params['check_out']) && $this->_bkHelperDate->validateBkDate($params['check_out'], $formatDate)) {
                        $checkOut = $this->_bkHelperDate->convertFormatDate($params['check_out']);
                    } else {
                        $checkOut = $checkIn;
                    }


                    if (isset($params['isRoundTrip']) && isset($params['check_in_two']) && $params['check_in_two'] != '') {
                        if ($this->_bkHelperDate->validateBkDate($params['check_in_two'], $formatDate)) {
                            $checkInTwo = $this->_bkHelperDate->convertFormatDate($params['check_in_two']);
                        }
                        if (isset($params['check_out_two']) && $this->_bkHelperDate->validateBkDate($params['check_out_two'], $formatDate)) {
                            $checkOutTwo = $this->_bkHelperDate->convertFormatDate($params['check_out_two']);
                        } else {
                            $checkOutTwo = $checkInTwo;
                        }
                    }


                    //if daily
                    $paramAddons = isset($params['addons']) ? $params['addons'] : array();
                    $arAddonPrice = array();
                    if ($booking->getBookingTime() == 1 || $booking->getBookingTime() == 4) {
                        if (isset($params['isRoundTrip']) && $params['isRoundTrip']) {
                            $arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDaysTwoCalendars($booking, $checkIn, $checkOut, $checkInTwo, $checkOutTwo, $qty, 0, $paramAddons);
                        } else {
                            $arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking, $checkIn, $checkOut, $qty, 0, $paramAddons);
                        }
                    } elseif ($booking->getBookingTime() == 2) {
                        /*$fromHour =  $params['from_time_t'] == 1 ? $params['from_time_h'] : ($params['from_time_h'] + 12);
                        $toHour =  $params['to_time_t'] == 1 ? $params['to_time_h'] : ($params['to_time_h'] + 12);
                        $arPrice = $this->_bkSimplePriceHelper->getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$params['from_time_m'],$params['to_time_m'],$qty,0,$paramAddons);*/
                        $serviceStart = isset($params['service_start']) ? $params['service_start'] : '';
                        $serviceEnd = isset($params['service_end']) ? $params['service_end'] : '';
                        $arPrice = $this->_bkSimplePriceHelper->getPricePerTime($booking, $checkIn, $checkOut, $serviceStart, $serviceEnd, $qty, 0, $paramAddons);
                    } elseif ($booking->getBookingTime() == 3) {
                        if (isset($params['isRoundTrip']) && $params['isRoundTrip']) {
                            $intervalsHours = isset($params['intervals_hours']) ? $params['intervals_hours'] : array();
                            $intervalsHoursTwo = isset($params['intervals_hours_two']) ? $params['intervals_hours_two'] : array();
                            $arPrice = $this->_intervalsPrice->getIntervalsHoursPriceTwoCalendar($booking, $checkIn, $checkInTwo, $qty, $intervalsHours, $intervalsHoursTwo, 0, $paramAddons);
                        } else {
                            $intervalsHours = isset($params['intervals_hours']) ? $params['intervals_hours'] : array();
                            $arPrice = $this->_intervalsPrice->getIntervalsHoursPrice($booking, $checkIn, $qty, $intervalsHours, 0, $paramAddons);
                        }

                    } elseif ($booking->getBookingTime() == 5) {
                        $persons = isset($params['number_persons']) ? $params['number_persons'] : array();
                        $arPrice = $this->_bkSimplePriceHelper->getBkTourPrice($booking, $checkIn, $checkOut, $qty, 0, $paramAddons, $persons);
                    }
                } elseif ($booking->getBookingType() == 'hotel') {
                    $roomId = $params['room_id'];
                    $roomModel = $this->_roomsFactory->create();
                    $room = $roomModel->load($roomId);
                    if ($room) {
                        if ($this->_bkHelperDate->validateBkDate($params['room_check_in'], $formatDate)) {
                            $checkIn = $this->_bkHelperDate->convertFormatDate($params['room_check_in']);
                        }
                        if ($this->_bkHelperDate->validateBkDate($params['room_check_out'], $formatDate)) {
                            $checkOut = $this->_bkHelperDate->convertFormatDate($params['room_check_out']);
                        }
                        $paramAddons = isset($params['addons']) ? $params['addons'] : array();
                        $arPrice = $this->_roomPrice->getPriceBetweenDays($room, $checkIn, $checkOut, $qty, 0, $paramAddons);
                    }

                }

                /*Apply discount from catalog rule if exist, compare with special price and replace if is less than special price*/
                $arPrice['total_promo'] = $this->dataBookingSystem->getPriceBetweenSpecialCalendarAndCatalogRule($booking->getId(),$arPrice['total_price'],$arPrice['total_promo']);
                /*---*/

                if (isset($arPrice['total_price'])) {
                    $priceBooking = $arPrice['total_promo'] > 0 ? $arPrice['total_promo'] : $arPrice['total_price'];

                    if( !$this->dataBookingSystem->ifProductIsTransfer($_product->getId()) ){
                        $priceBooking *= $qty;
                    }

                    /****  RANGE PRICE  ****/
                    $priceBooking = $this->dataBookingSystem->getPriceByRange($_product->getId(),$qty,$priceBooking,true);
                    /*----------------------*/

                    /*BASE DOBLE*/
                    $baseDoble = $_product->getRegulares();
                    if( $baseDoble == '1' && $qty == 1){
                        $priceBooking *= 2;
                    }
                    /*----*/

                    $finalProductPrice = 0;
                    $usePriceOption = 0;
                    $useDefaultPrice = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/default_price');
                    if ($usePriceOption == 1 || $useDefaultPrice == 1) {
                        $defaultPrice = $_product->getSpecialPrice() > 0 ? $_product->getSpecialPrice() : $_product->getPrice();
                        $productPrice = $_product->getFinalPrice();
                        if ($usePriceOption == 1 && $useDefaultPrice == 1) {
                            $finalProductPrice = $productPrice;
                        } elseif ($usePriceOption == 1 && $useDefaultPrice == 0) {
                            $finalProductPrice = $productPrice - $defaultPrice;
                        } else {
                            $finalProductPrice = $defaultPrice;
                        }
                    }
                    $finalPrice = $priceBooking + $finalProductPrice;
                    //change price
                    $item = $observer->getQuoteItem();
                    // Ensure we have the parent item, if it has one
                    $item = ($item->getParentItem() ? $item->getParentItem() : $item);
                    // change formula based price
                    $finalPrice = $this->bookingPrice->getFinalBookingPrice($qty, $item->getProduct(), $finalPrice,$basePriceWithOutBookingPrice);


		     //PARTIAL PAYMENTS ----------------------------------------------------------------------------------------------
                    $percentDiscount = 0;//Porcentaje del monto total a pagar.
                    $options = $_product->getProductOptions();
                    if(empty($options["options"])){//En caso de que getProductOptions() falle, vuelvo a pedir lo mismo de esta otra forma.
                        $options = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                    }
                    if (!empty($options["options"])){
                        foreach ($options["options"] as $option) {
                            if ($option["label"] == "Pay Partially") {
                                $percentDiscount = floatval(preg_replace('/\D/', '', $option["value"])) / 100;
                            }
                        }
                    }
                    if($percentDiscount){
                        $finalPrice = $finalPrice * $percentDiscount;
                    }


                    //this price is when people add to cart the first time
                    //We save the price in USD or base currency in order to use i to convert in
                    //app/code/Daytours/Bookingsystem/Observer/Frontend/BkChangeStore.php
                    $item->setPriceToConvert($finalPrice);

                    $finalPrice = $this->_bkPriceHelper->currency($finalPrice,false,false);

                    $item->setCustomPrice($finalPrice);
                    $item->setOriginalCustomPrice($finalPrice);

                    $item->setQty(1);
                    $item->setQtyCustom($qty);
                    // Enable super mode on the product.
                    $item->getProduct()->setIsSuperMode(true);
                }
            }
        }
        return $this;
    }

}
