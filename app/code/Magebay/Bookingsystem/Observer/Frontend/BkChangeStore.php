<?php

namespace Magebay\Bookingsystem\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Cart as BkCoreCart;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Message\ManagerInterface;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkCustomOptions;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\IntervalsPrice;
use Magebay\Bookingsystem\Helper\RoomPrice;

class BkChangeStore implements ObserverInterface
{
	/**
	* @var Magento\Framework\App\RequestInterface;
	**/
	protected $_request;
	/**
	* @var BkCoreCart;
	**/
	protected $_bkCoreCart;
	/**
	* @var Magento\Framework\Pricing\Helper\Data;
	**/
	protected $_bkPriceHelper;
	/**
	* @var DirectoryHelper
	**/
	protected $_directoryHelper;
	/**
	* @var DirectoryHelper
	**/
	protected $_messageManager;
	/**
	* @var Magebay\Bookingsystem\Model\BookingsFactory;
	**/
	
	protected $_bookingFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\RoomsFactory
    */
	protected $_roomsFactory;
	/**
	* @var Magebay\Bookingsystem\Helper\BkHelperDate;
	**/
	protected $_bkHelperDate;
	/**
	* @var Magebay\Bookingsystem\Helper\BkCustomOptions;
	**/
	protected $_bkCustomOptions;
	/**
	* @var Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
	**/
	protected $_bkSimplePriceHelper;
	/**
	* @var Magebay\Bookingsystem\Helper\IntervalsPrice;
	**/
	protected $_intervalsPrice;
	/**
	* @var Magebay\Bookingsystem\Helper\RoomPrice;
	**/
	protected $_roomPrice;
	
	public function __construct(
		RequestInterface $request,
		BkCoreCart $bkCoreCart,
		PriceHelper $bkPriceHelper,
		DirectoryHelper $directoryHelper,
		ManagerInterface $messageManager,
		BookingsFactory $bookingFactory,
		RoomsFactory $roomsFactory,
		BkHelperDate $bkHelperDate,
		BkCustomOptions $bkCustomOptions,
		BkSimplePriceHelper $bkSimplePriceHelper,
		IntervalsPrice $intervalsPrice,
		RoomPrice $roomPrice
		
	)
    {
        $this->_request = $request;
		$this->_bkCoreCart = $bkCoreCart;
		$this->_bkPriceHelper = $bkPriceHelper;
		$this->_directoryHelper = $directoryHelper;
		$this->_messageManager = $messageManager;
		$this->_bookingFactory = $bookingFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_bkCustomOptions = $bkCustomOptions;
		$this->_bkSimplePriceHelper = $bkSimplePriceHelper;
		$this->_intervalsPrice = $intervalsPrice;
		$this->_roomPrice = $roomPrice;
    }
    public function execute(EventObserver $observer)
    {
		$tempParams = $this->_getBkRequest()->getParams();
		$enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
		$checkOutAction = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/checkout_action');
		$fullActionName = $observer->getRequest()->getFullActionName();
		$arrayAction = array('stores_store_switch','directory_currency_switch','checkout_cart_updatePost',$checkOutAction);
		if($enable == 1 && in_array($fullActionName,$arrayAction))
		{
			$carts = $this->_bkCoreCart;
			if ($carts->getQuote()->getItemsCount()) 
			{
				foreach ($carts->getQuote()->getAllItems() as $item) {
					$_product = $item->getProduct();
					if($_product->getTypeId() != 'booking')
					{
						break;
					}
					$bookingModel = $this->_bookingFactory->create();
					$booking = $bookingModel->getBooking($_product->getId());
					if($booking && $booking->getId())
					{
						$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
						$customOptionsRequest = $_customOptions['info_buyRequest'];
						$checkIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in']);
						//vaildate item's time
						if(isset($customOptionsRequest['check_out']))
						{
							$checkOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out']);
						}
						else
						{
							$checkOut = $checkIn;
						}
						if($fullActionName == 'stores_store_switch')
						{
							$customOptionsRequest['qty'] = 0;
							$dataBkOptions = $this->_bkCustomOptions->createExtractOptions($_product,$customOptionsRequest);
							if($dataBkOptions['status'] == true)
							{
								$additionalOptions = $dataBkOptions['bk_options'];
								if(count($additionalOptions))
								{
									$item->addOption(array(
									'code' => 'additional_options',
									'value' => serialize($additionalOptions)
									));
								}
							}
						}
						elseif($fullActionName == 'directory_currency_switch' || $fullActionName == 'checkout_cart_updatePost' || $fullActionName == $checkOutAction)
						{
							$qty = $item->getQty();
							if($fullActionName == 'checkout_cart_updatePost')
							{
								if(isset($tempParams['cart'][$item->getId()]['qty']))
								{
									$qty = $tempParams['cart'][$item->getId()]['qty'];
								}
							}
							$arPrice = array();
							if($booking->getBookingType() == 'per_day')
							{
								$paramAddons = isset($customOptionsRequest['addons']) ? $customOptionsRequest['addons'] : array();
								$arAddonPrice = array();
								if($booking->getBookingTime() == 1 || $booking->getBookingTime() == 4)
								{
									$arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$qty,$item->getId(),$paramAddons);
								}
								elseif($booking->getBookingTime() == 2)
								{
									/*$fromHour =  $customOptionsRequest['from_time_t'] == 1 ? $customOptionsRequest['from_time_h'] : ($customOptionsRequest['from_time_h'] + 12);
									$toHour =  $customOptionsRequest['to_time_t'] == 1 ? $customOptionsRequest['to_time_h'] : ($customOptionsRequest['to_time_h'] + 12);
									$arPrice = $this->_bkSimplePriceHelper->getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$customOptionsRequest['from_time_m'],$customOptionsRequest['to_time_m'],$qty,$item->getId(),$paramAddons);
								    */
                                    $serviceStart = isset($customOptionsRequest['service_start']) ? $customOptionsRequest['service_start'] : '';
                                    $serviceEnd = isset($customOptionsRequest['service_end']) ? $customOptionsRequest['service_end'] : '';
                                    $arPrice = $this->_bkSimplePriceHelper->getPricePerTime($booking,$checkIn,$checkOut,$serviceStart,$serviceEnd,$qty,$item->getId(),$paramAddons);
								}
								elseif($booking->getBookingTime() == 3)
								{
									$intervalsHours = isset($customOptionsRequest['intervals_hours']) ? $customOptionsRequest['intervals_hours'] : array();
									$arPrice = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$item->getId(),$paramAddons);
								}
                                elseif ($booking->getBookingTime() == 5)
                                {
                                    $persons = isset($customOptionsRequest['number_persons']) ? $customOptionsRequest['number_persons'] : array();
                                    $arPrice = $this->_bkSimplePriceHelper->getBkTourPrice($booking,$checkIn,$checkOut,$qty,$item->getId(),$paramAddons,$persons);
                                }
							}
							elseif($booking->getBookingType() == 'hotel')
							{
								$roomId = $customOptionsRequest['room_id'];
								$roomModel = $this->_roomsFactory->create();
								$room = $roomModel->load($roomId);
								if($room)
								{
									$checkIn = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['room_check_in']);
									$checkOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['room_check_out']);
									$paramAddons = isset($customOptionsRequest['addons']) ? $customOptionsRequest['addons'] : array();
									$arPrice = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,$item->getId(),$paramAddons);
								}
							}
							if($fullActionName == 'checkout_cart_updatePost' || $fullActionName == $checkOutAction)
							{
								if(!count($arPrice) || (count($arPrice) && $arPrice['str_error'] != ''))
								{
									$bkMsgError = __('We can\'t update shoping cart. Please check again');
									if($fullActionName == $checkOutAction)
									{
										$bkMsgError = __('You can\'t Checkout. Please check again');
									}
									$strUrl = $observer->getRequest()->getDistroBaseUrl().'checkout/cart'; 
									$controllerAction = $observer->getControllerAction();
									$this->_messageManager->addError($bkMsgError);
									$controllerAction->getResponse()->setRedirect($strUrl);
									$controllerAction->getResponse()->sendResponse();
									die();
								}
							}
							else
							{
								$priceBooking = 0;
								if(isset($arPrice['total_price']))
								{
									$priceBooking = $arPrice['total_promo'] > 0 ? $arPrice['total_promo'] : $arPrice['total_price'];
									$newCurrency = (string)$this->_getBkRequest()->getParam('currency');
									$baseCurrenCode = $this->_directoryHelper->getBaseCurrencyCode();
									if($newCurrency != $baseCurrenCode)
									{
										$priceBooking = $this->_directoryHelper->currencyConvert($priceBooking,$baseCurrenCode,$newCurrency); 
									}
								}
								$finalProductPrice = 0;
								$usePriceOptions = 0;
								$useDefaultPrice = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/default_price');
								if($usePriceOptions == 1 || $useDefaultPrice == 1)
								{
									//get price default
									$defaultPrice = $_product->getSpecialPrice() > 0 ?  $_product->getSpecialPrice() :  $_product->getPrice();
									$defaultPrice = $this->_bkPriceHelper->currency($defaultPrice,false,false);
									$productPrice = $_product->getFinalPrice();
									$productPrice = $this->_bkPriceHelper->currency($productPrice,false,false);
									//$finalProductPrice = $productPrice - $defaultPrice;
									if($usePriceOptions == 1 && $useDefaultPrice == 1)
									{
										$finalProductPrice = $productPrice;
									}
									elseif($usePriceOptions == 1 && $useDefaultPrice == 0)
									{
										$finalProductPrice = $productPrice - $defaultPrice;
									}
									else
									{
										$finalProductPrice = $defaultPrice;
									}
								}
								$finalPrice = $priceBooking + $finalProductPrice;
								$item->setCustomPrice($finalPrice);
								$item->setOriginalCustomPrice($finalPrice);
								$item->getProduct()->setIsSuperMode(true);
							}
						}
						
					}
				}
				$carts->getQuote()->save();
			}
		}
    }
	function _getBkRequest()
	{
		return $this->_request;
	}
}
