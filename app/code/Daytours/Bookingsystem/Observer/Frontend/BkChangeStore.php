<?php

namespace Daytours\Bookingsystem\Observer\Frontend;

use Magebay\Bookingsystem\Model\Bookings;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Cart as BkCoreCart;
use Magento\Framework\Exception\LocalizedException;
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
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\Json\Helper\Data as JsonHelperData;
use \Magento\Framework\Locale\Format;

class BkChangeStore extends \Magebay\Bookingsystem\Observer\Frontend\BkChangeStore
{
	/**
	* @var RequestInterface;
	**/
	protected $_request;
	/**
	* @var BkCoreCart;
	**/
	protected $_bkCoreCart;
	/**
	* @var PriceHelper;
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
	* @var BookingsFactory;
	**/
	
	protected $_bookingFactory;
	/**
     * @var RoomsFactory
    */
	protected $_roomsFactory;
	/**
	* @var BkHelperDate;
	**/
	protected $_bkHelperDate;
	/**
	* @var \Daytours\Bookingsystem\Helper\BkCustomOptions;
	**/
	protected $_bkCustomOptions;
	/**
	* @var BkSimplePriceHelper;
	**/
	protected $_bkSimplePriceHelper;
	/**
	* @var \Daytours\Bookingsystem\Helper\IntervalsPrice;
	**/
	protected $_intervalsPrice;
	/**
	* @var RoomPrice;
	**/
	protected $_roomPrice;
    /**
     * @var JsonHelperData
     */
    private $jsonHelper;
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var Format
     */
    private $format;

    /**
     * BkChangeStore constructor.
     * @param RequestInterface $request
     * @param BkCoreCart $bkCoreCart
     * @param PriceHelper $bkPriceHelper
     * @param DirectoryHelper $directoryHelper
     * @param ManagerInterface $messageManager
     * @param BookingsFactory $bookingFactory
     * @param RoomsFactory $roomsFactory
     * @param BkHelperDate $bkHelperDate
     * @param BkCustomOptions $bkCustomOptions
     * @param BkSimplePriceHelper $bkSimplePriceHelper
     * @param IntervalsPrice $intervalsPrice
     * @param RoomPrice $roomPrice
     * @param JsonHelperData $jsonHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param StoreManagerInterface $_storeManager
     * @param Format $format
     */
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
		RoomPrice $roomPrice,
        JsonHelperData $jsonHelper,
        PriceCurrencyInterface $priceCurrency,
        StoreManagerInterface $_storeManager,
        Format $format
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
        $this->jsonHelper = $jsonHelper;
        $this->priceCurrency = $priceCurrency;
        $this->_storeManager = $_storeManager;
        $this->format = $format;
    }

    /**
     * @param EventObserver $observer
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {

        /**
         * @var Item $item
         * @var Bookings $bookingModel
         */
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
                        $checkInTwo = '';
                        if( isset($customOptionsRequest['check_in_two']) && $customOptionsRequest['check_in_two'] != '' ){
                            $checkInTwo = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_in_two']);
                        }
						//vaildate item's time
						if(isset($customOptionsRequest['check_out']))
						{
							$checkOut = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out']);
						}
						else
						{
							$checkOut = $checkIn;
						}

                        if(isset($customOptionsRequest['check_out_two']) && $customOptionsRequest['check_out_two'] != '')
                        {
                            $checkOutTwo = $this->_bkHelperDate->convertFormatDate($customOptionsRequest['check_out_two']);
                        }
                        else
                        {
                            $checkOutTwo = $checkInTwo;
                        }


						if($fullActionName == 'stores_store_switch')
						{
							$customOptionsRequest['qty'] = 0;
                            if( isset($customOptionsRequest['isRoundTrip']) && $customOptionsRequest['isRoundTrip'] == 1 ){
                                $dataBkOptions = $this->_bkCustomOptions->createExtractOptionsByTransfer($_product,$customOptionsRequest);
                            }else{
                                $dataBkOptions = $this->_bkCustomOptions->createExtractOptions($_product,$customOptionsRequest);
                            }

							if($dataBkOptions['status'] == true)
							{
								$additionalOptions = $dataBkOptions['bk_options'];
								if(count($additionalOptions))
								{
									$item->addOption([
									'code' => 'additional_options',
									'value' => serialize($additionalOptions)
                                    ]);
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
                                    $serviceStart = isset($customOptionsRequest['service_start']) ? $customOptionsRequest['service_start'] : '';
                                    $serviceEnd = isset($customOptionsRequest['service_end']) ? $customOptionsRequest['service_end'] : '';
                                    $arPrice = $this->_bkSimplePriceHelper->getPricePerTime($booking,$checkIn,$checkOut,$serviceStart,$serviceEnd,$qty,$item->getId(),$paramAddons);
								}
								elseif($booking->getBookingTime() == 3)
								{
                                    if( isset($customOptionsRequest['isRoundTrip']) && $customOptionsRequest['isRoundTrip'] ) {
                                        $intervalsHours = isset($customOptionsRequest['intervals_hours']) ? $customOptionsRequest['intervals_hours'] : array();
                                        $intervalsHoursTwo = isset($customOptionsRequest['intervals_hours_two']) ? $customOptionsRequest['intervals_hours_two'] : array();
                                        $arPrice = $this->_intervalsPrice->getIntervalsHoursPriceTwoCalendar($booking,$checkIn,$checkInTwo,$qty,$intervalsHours,$intervalsHoursTwo,0,$paramAddons,0,$customOptionsRequest['isRoundTrip']);
                                    }else{
                                        $intervalsHours = isset($customOptionsRequest['intervals_hours']) ? $customOptionsRequest['intervals_hours'] : array();
                                        $arPrice = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$item->getId(),$paramAddons);
                                    }

								}

							}


							if($fullActionName == 'checkout_cart_updatePost' || $fullActionName == $checkOutAction)
							{
								if(!count($arPrice) || (count($arPrice) && $arPrice['str_error'] != ''))
								{
									$bkMsgError = __('We can\'t update shoping cart. Please check again');
									if($fullActionName == $checkOutAction)
									{
										$bkMsgError = __('There are problems with the dates, please verify if your product(s) are properly configured.');
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
                                        $priceToConvert = $item->getPriceToConvert();
										$priceBooking = $this->_directoryHelper->currencyConvert($priceToConvert,$baseCurrenCode,$newCurrency);
									}else{
                                        $priceToConvert = $item->getPriceToConvert();
                                        $priceBooking = $priceToConvert;
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

                                $newAdditionalOptions = $this->_recalculatePricesInMultipliersAndChildren($item);
                                $item->getOptionByCode('additional_options')->setValue($this->jsonHelper->jsonEncode($newAdditionalOptions));
							}
						}
					}
				}
				$carts->getQuote()->save();
			}
		}
    }

    /**
     * @param $item
     * @return array
     */
    private function _recalculatePricesInMultipliersAndChildren($item){

        /** @var Item $item */

        $currentCurrency = $item->getQuote()->getData('quote_currency_code');
        $codeStore = $item->getQuote()->getStore()->getData('code');
        $newCurrency = $this->_getBkRequest()->getParam('currency');

        $result = [];
	    $customOptions = $item->getProduct()->getCustomOptions();
	    if( isset( $customOptions['additional_options'] ) ){
            $additionalOptions = $customOptions['additional_options']->getData('value');
            if( !empty($additionalOptions) ){
                $customOptionsProduct = $this->jsonHelper->jsonDecode($additionalOptions);
                $formatPrice = $this->format->getPriceFormat();
                foreach ($customOptionsProduct as $itemOption ){
                    if($itemOption['label'] == 'regular_services'){
                        if( !empty($itemOption['value']) ){
                            $regularServiceValues = $this->jsonHelper->jsonDecode($itemOption['value']);

                            $newValuesToRegularService = [];

                            foreach ($regularServiceValues as $itemRegularService){
                                $label = $itemRegularService['label'];
//                            $priceRecovered = preg_replace("/[^0-9\.]/", '', $itemRegularService['price']);
                                $priceRecovered = $this->_getPriceFromRegularService($itemRegularService['price'],$formatPrice);

                                $currencyRate = $this->_storeManager->getStore()->getBaseCurrency()->getRate($currentCurrency);
                                $priceRecovered = $priceRecovered / $currencyRate;
                                $priceConverted = $this->priceCurrency->convert($priceRecovered,$codeStore,$newCurrency);
                                $price = $this->priceCurrency->format(
                                    $priceConverted,
                                    false,
                                    $this->priceCurrency::DEFAULT_PRECISION,
                                    $codeStore,
                                    $newCurrency);

                                $qty = $itemRegularService['qty'];

                                $newValuesToRegularService[] =
                                    [
                                        'label' => $label,
                                        'price' => $price,
                                        'qty' => $qty
                                    ];
                            }

                            $result[] = [
                                'label' => 'regular_services',
                                'value' => $this->jsonHelper->jsonEncode($newValuesToRegularService)
                            ];
                        }
                    }
                    else{
                        $result [] = $itemOption;
                    }
                }
            }
        }

	    return $result;
    }
    
    private function _getPriceFromRegularService($price,$formatPrice){

        preg_match_all('/\d+/', $price, $matches);
        print_r($matches[0]);
        $result = '';
        if( !empty($matches) ){
            $matches = $matches[0];

            foreach ($matches as $key => $number){
                if( $key == (count($matches) -1) ){
                    if( $formatPrice['precision'] > 0 ){
                        $result .= '.' . $number;
                    }else{
                        $result .= $number;
                    }
                }else{
                     $result .= $number;
                }
            }

        }

        return $result;

    }

	function _getBkRequest()
	{
		return $this->_request;
	}
}
