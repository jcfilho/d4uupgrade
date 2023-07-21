<?php

namespace Magebay\Bookingsystem\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\IntervalsPrice;
use Magebay\Bookingsystem\Helper\RoomPrice;


class QuoteAddItem implements ObserverInterface
{
	/**
	* @var Magento\Framework\App\RequestInterface;
	**/
	protected $_request;
	/**
	* @var Magento\Framework\Pricing\Helper\Data;
	**/
	protected $_bkPriceHelper;
	/**
	* @var Magebay\Bookingsystem\Model\BookingsFactory;
	**/
	protected $_bookingFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\RoomsFactory
    */
	protected $_roomsFactory;
	/**
	* @var Magebay\Bookingsystem\Helper\BkHelperDate;;
	**/
	protected $_bkHelperDate;
	/**
	* @var Magebay\Bookingsystem\Helper\BkSimplePriceHelper;;
	**/
	protected $_bkSimplePriceHelper;
	/**
	* @var Magebay\Bookingsystem\Helper\IntervalsPrice;;
	**/
	protected $_intervalsPrice;
	
	public function __construct(
				RequestInterface $request,
				PriceHelper $bkPriceHelper,
				BookingsFactory $bookingFactory,
				RoomsFactory $roomsFactory,
				BkHelperDate $bkHelperDate,
				BkSimplePriceHelper $bkSimplePriceHelper,
				IntervalsPrice $intervalsPrice,
				RoomPrice $roomPrice
			)
    {
        $this->_request = $request;
        $this->_bkPriceHelper = $bkPriceHelper;
        $this->_bookingFactory = $bookingFactory;
		$this->_roomsFactory = $roomsFactory;
        $this->_bkHelperDate = $bkHelperDate;
        $this->_bkSimplePriceHelper = $bkSimplePriceHelper;
        $this->_intervalsPrice = $intervalsPrice;
        $this->_roomPrice = $roomPrice;
	}
    public function execute(EventObserver $observer)
    {
		$enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
		if($enable == 1)
		{
			$item = $observer->getQuoteItem();
			// Ensure we have the parent item, if it has one
			$item = ($item->getParentItem() ? $item->getParentItem() : $item);
			$buyInfor = $item->getBuyRequest();
			$_product = $item->getProduct();
			if($_product->getTypeId() == 'booking')
			{
				$finalPrice = 0;
				//if add order in admin
				if(isset($buyInfor['bk_create_order_final_price']))
				{
					$finalPrice = $buyInfor['bk_create_order_final_price'];
				}
				else
				{
					//get product Id
					$productId = $_product->getId();
					//check booking product
					$bookingModel = $this->_bookingFactory->create();
					$booking = $bookingModel->getBooking($productId);
					if($booking->getId())
					{
						$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
						$params = $buyInfor;
						$price = 0;
						$qty = isset($params['qty']) ? $params['qty'] : 1;
						//get params
						$checkIn = '';
						$checkOut = '';
						$arPrice = array();
						$strError = '';
						if($booking->getBookingType() == 'per_day')
						{
							if($this->_bkHelperDate->validateBkDate($params['check_in'],$formatDate))
							{
								$checkIn = $this->_bkHelperDate->convertFormatDate($params['check_in']);
							}
							if(isset($params['check_out']) && $this->_bkHelperDate->validateBkDate($params['check_out'],$formatDate))
							{
								$checkOut = $this->_bkHelperDate->convertFormatDate($params['check_out']);
							}
							else
							{
								$checkOut = $checkIn;
							}
							//if daily
							$paramAddons = isset($params['addons']) ? $params['addons'] : array();
							$arAddonPrice = array();
							if($booking->getBookingTime() == 1)
							{
								$arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$qty,0,$paramAddons);
							}
							elseif($booking->getBookingTime() == 2)
							{
								$fromHour =  $params['from_time_t'] == 1 ? $params['from_time_h'] : ($params['from_time_h'] + 12);
								$toHour =  $params['to_time_t'] == 1 ? $params['to_time_h'] : ($params['to_time_h'] + 12);
								$arPrice = $this->_bkSimplePriceHelper->getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$params['from_time_m'],$params['to_time_m'],$qty,0,$paramAddons);
							}
							elseif($booking->getBookingTime() == 3)
							{
								$intervalsHours = isset($params['intervals_hours']) ? $params['intervals_hours'] : array();
								$arPrice = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,0,$paramAddons);
							}
						}
						elseif($booking->getBookingType() == 'hotel')
						{
							$roomId = $params['room_id'];
							$roomModel = $this->_roomsFactory->create();
							$room = $roomModel->load($roomId);
							if($room)
							{
								if($this->_bkHelperDate->validateBkDate($params['room_check_in'],$formatDate))
								{
									$checkIn = $this->_bkHelperDate->convertFormatDate($params['room_check_in']);
								}
								if($this->_bkHelperDate->validateBkDate($params['room_check_out'],$formatDate))
								{
									$checkOut = $this->_bkHelperDate->convertFormatDate($params['room_check_out']);
								}
								$paramAddons = isset($params['addons']) ? $params['addons'] : array();
								$arPrice = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,0,$paramAddons);
							}
							
						}
						if(isset($arPrice['total_price']))
						{
							$priceBooking = $arPrice['total_promo'] > 0 ? $arPrice['total_promo'] : $arPrice['total_price'];
							$priceBooking = $this->_bkPriceHelper->currency($priceBooking,false,false);
							$finalPrice = $priceBooking;
							$finalProductPrice = 0;
							$usePriceOption = 0;
							$useDefaultPrice = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/default_price');
							if($usePriceOption == 1 || $useDefaultPrice == 1)
							{
								$defaultPrice = $_product->getSpecialPrice() > 0 ?  $_product->getSpecialPrice() :  $_product->getPrice();
								$defaultPrice = $this->_bkPriceHelper->currency($defaultPrice,false,false);
								$productPrice = $_product->getFinalPrice();
								$productPrice = $this->_bkPriceHelper->currency($productPrice,false,false);
								//get price default
								if($usePriceOption == 1 && $useDefaultPrice == 1)
								{
									$finalProductPrice = $productPrice;
								}
								elseif($usePriceOption == 1 && $useDefaultPrice == 0)
								{
									$finalProductPrice = $productPrice - $defaultPrice;
								}
								else
								{
									$finalProductPrice = $defaultPrice;
								}
							}
							$finalPrice = $priceBooking + $finalProductPrice;
						}
					}
				}
				$item->setCustomPrice($finalPrice);
				$item->setOriginalCustomPrice($finalPrice);
				// Enable super mode on the product.
				$item->getProduct()->setIsSuperMode(true);
			}
			
		}
		return $this;
    }
	function _getBkRequest()
	{
		return $this->_request;
	}
}
