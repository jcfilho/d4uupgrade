<?php

namespace Daytours\Bookingsystem\Observer\Frontend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Cart as BkCoreCart;
use Magento\Quote\Model\Quote\Item\OptionFactory as QuoteItemOptionFactory;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\BkCustomOptions;

class BkQuoteManagement extends \Magebay\Bookingsystem\Observer\Frontend\BkQuoteManagement
{
	/**
	* @var BkCoreCart;
	**/
	protected $_bkCoreCart;
	/**
	* @var QuoteItemOptionFactory;
	**/
	protected $_quoteItemOptionFactory;
	/**
	* @var Magebay\Bookingsystem\Model\BookingsFactory;
	**/
	protected $_bookingFactory;
	/**
	* @var Magebay\Bookingsystem\Model\Bookingorders;
	**/
	protected $_bookingorders;
	/**
	* @var Magebay\Bookingsystem\Helper\BkHelperDate;
	**/
	protected $_bkHelperDate;
	/**
	* @var \Daytours\Bookingsystem\Helper\BkSimplePriceHelper;
	**/
	protected $_bkSimplePriceHelper;
	/**
	* @var Magebay\Bookingsystem\Helper\BkCustomOptions;
	**/
	protected $_bkCustomOptions;
	public function __construct(
		BkCoreCart $bkCoreCart,
		QuoteItemOptionFactory $quoteItemOptionFactory,
		BookingsFactory $bookingFactory,
		BookingordersFactory $bookingorders,
		BkHelperDate $bkHelperDate,
		BkSimplePriceHelper $bkSimplePriceHelper,
		BkCustomOptions $bkCustomOptions
	)
    {
		$this->_bkCoreCart = $bkCoreCart;
		$this->_quoteItemOptionFactory = $quoteItemOptionFactory;
		$this->_bookingFactory = $bookingFactory;
        $this->_bookingorders = $bookingorders;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_bkSimplePriceHelper = $bkSimplePriceHelper;
		$this->_bkCustomOptions = $bkCustomOptions;
        parent::__construct(
            $bkCoreCart,
            $quoteItemOptionFactory,
            $bookingFactory,
            $bookingorders,
            $bkHelperDate,
            $bkSimplePriceHelper,
            $bkCustomOptions
        );
    }
	public function execute(EventObserver $observer)
    {
		$enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
		if($enable == 1)
		{
			//get order
			$order = $observer->getOrder();
			$customerName = '';
			if($order->getShippingAddress())
			{
				$shiipingAddress = $order->getShippingAddress();
				$customerName = $shiipingAddress->getFirstname().' '.$shiipingAddress->getMiddlename().' '.$shiipingAddress->getLastname();
			}
			elseif($order->getBillingAddress())
			{
				$bildingAddress = $order->getBillingAddress();
				$customerName = $bildingAddress->getFirstname().' '.$bildingAddress->getMiddlename().' '.$bildingAddress->getLastname();
			}
			$items = $order->getAllVisibleItems();
			if(count($items))
			{
				foreach($items as $item)
				{
					$_product = $item->getProduct();
					if($_product->getTypeId() == 'booking')
					{
						$dataSave = array();
						//booking simple
						$checkIn = '';
						$checkOut = '';
                        $checkInTwo = '';
                        $checkOutTwo = '';
						$serviceStart = '';
						$serviceEnd = '';
						$totalDays = 0;
						$totalHours = 0;
						//special for intervals hours type
						$interQty = 0;
                        $interQtyTwo = 0;
						$strInterTime = '';
                        $strInterTimeTwo = '';
						$requestOptions = $item->getProductOptionByCode('info_buyRequest');
						// $quantity = $requestOptions['qty'];
						$quantity = $item->getQtyOrdered();
                        $quantityTwo = $item->getQtyOrdered();
						$paramAddons = isset($requestOptions['addons']) ? $requestOptions['addons'] : array();
						$arPrice = array();
						$arPrice['str_error'] = '';
						$bookingModel = $this->_bookingFactory->create();
						$booking = $bookingModel->getBooking($_product->getId());
						if($booking && $booking->getBookingType() == 'per_day')
						{
							$checkIn = $this->_bkHelperDate->convertFormatDate($requestOptions['check_in']);
                            $checkInTwo = '';
                            if(isset($requestOptions['check_in_two']) && $requestOptions['check_in_two'] != ''){
                                $checkInTwo = $this->_bkHelperDate->convertFormatDate($requestOptions['check_in_two']);
                            }

							if(isset($requestOptions['check_out']))
							{
								$checkOut = $this->_bkHelperDate->convertFormatDate($requestOptions['check_out']);
							}
							else
							{
								$checkOut = $checkIn;
							}

                            if(isset($requestOptions['check_out_two']) && $requestOptions['check_out_two'] != '')
                            {
                                $checkOutTwo = $this->_bkHelperDate->convertFormatDate($requestOptions['check_out_two']);
                            }
                            else
                            {
                                $checkOutTwo = $checkInTwo;
                            }

							if($booking->getBookingTime() == 1 || $booking->getBookingTime() == 4 || $booking->getBookingTime() == 5)
							{
							    if($booking->getBookingTime() == 5)
                                {
                                    $persons = isset($requestOptions['number_persons']) ? $requestOptions['number_persons'] : array();
                                    $arPrice = $this->_bkSimplePriceHelper->getBkTourPrice($booking,$checkIn,$checkOut,$quantity,$item->getItemId(),$paramAddons,$persons);
                                    $extractPrices = isset($arPrice['extract_price']) ? $arPrice['extract_price'] : array();
                                    $arPersons = array();
                                    if(count($persons) && count($extractPrices))
                                    {
                                        foreach ($persons as $keyPer => $person)
                                        {
                                            if((int)$person <= 0)
                                            {
                                                continue;
                                            }
                                            $quantity += $person;
                                            if(array_key_exists($keyPer,$extractPrices))
                                            {
                                                $freePerson = $person > $extractPrices[$keyPer]['free'] ? $extractPrices[$keyPer]['free'] : $person;
                                                $txtPerson = $person . '( ' . __('Free ') . $freePerson .' )';;
                                                $arPersons[] = array(
                                                    'label' => $extractPrices[$keyPer]['title'],
                                                    'value' => $txtPerson,
                                                );
                                            }
                                        }
                                    }
                                    if(count($arPersons))
                                    {
                                        $strInterTime = json_encode($arPersons);
                                    }
                                }
                                else
                                {
                                    if(isset($requestOptions['check_out_two']) && $requestOptions['check_out_two'] != ''){
                                        $arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDaysTwoCalendars($booking,$checkIn,$checkOut,$checkInTwo,$checkOutTwo,$quantity,$item->getItemId(),$paramAddons,0,$requestOptions['isRoundTrip']);
                                    }else{
                                        $arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$quantity,$item->getItemId(),$paramAddons);
                                    }

                                }
							}
							elseif($booking->getBookingTime() == 2)
							{
								/*$serviceStart = $requestOptions['from_time_h'];
								$serviceStart .= ','.$requestOptions['from_time_m'];
								$serviceStart .= ','.$requestOptions['from_time_t'];
								$serviceEnd = $requestOptions['to_time_h'];
								$serviceEnd .= ','.$requestOptions['to_time_m'];
								$serviceEnd .= ','.$requestOptions['to_time_t'];
								$fromHour =  $requestOptions['from_time_t'] == 1 ? $requestOptions['from_time_h'] : ($requestOptions['from_time_h'] + 12);
								$toHour =  $requestOptions['to_time_t'] == 1 ? $requestOptions['to_time_h'] : ($requestOptions['to_time_h'] + 12);
								$arPrice = $this->_bkSimplePriceHelper->getHourPriceBetweenDays($booking,$checkIn,$checkOut,$fromHour,$toHour,$requestOptions['from_time_m'],$requestOptions['to_time_m'],$quantity,$item->getItemId(),$paramAddons);*/
                                $serviceStart = isset($requestOptions['service_start']) ? $requestOptions['service_start'] : '';
                                $serviceEnd = isset($requestOptions['service_end']) ? $requestOptions['service_end'] : '';
                                $arPrice = $this->_bkSimplePriceHelper->getPricePerTime($booking,$checkIn,$checkOut,$serviceStart,$serviceEnd,$quantity,$item->getItemId(),$paramAddons);
                                $serviceStart = isset($arPrice['service_start']) ? $arPrice['service_start'] : '';
                                $serviceEnd = isset($arPrice['service_end']) ? $arPrice['service_end'] : '';
                            }
							elseif($booking->getBookingTime() == 3)
							{

								$intervalsHours = $requestOptions['intervals_hours'];
                                if(count($intervalsHours))
								{
                                    $quantity *= count($intervalsHours);
                                    $interQty = $quantity;
									$strInterTime = implode(',',$requestOptions['intervals_hours']);
								}

                                $intervalsHoursTwo = '';
                                if( isset($requestOptions['intervals_hours_two']) && $requestOptions['intervals_hours_two'] != '' ){
                                    $intervalsHoursTwo = $requestOptions['intervals_hours_two'];
                                    if(count($intervalsHoursTwo))
                                    {
                                        $quantityTwo *= count($intervalsHoursTwo);
                                        $interQtyTwo = $quantityTwo;
                                        $strInterTimeTwo = implode(',',$requestOptions['intervals_hours_two']);
                                    }
                                }
								
							}
							if(count($arPrice) && $arPrice['str_error'] == '')
							{
								$totalDays = isset($arPrice['total_days']) ? $arPrice['total_days'] : 0;
								$totalHours = isset($arPrice['total_hours']) ? $arPrice['total_hours'] : 0;
							}
							$dataSave = array(
								'bkorder_check_in'=>$checkIn,
								'bkorder_check_out'=>$checkOut,
                                'bkorder_check_in_two'=>$checkInTwo,
                                'bkorder_check_out_two'=>$checkOutTwo,
								'bkorder_qty'=>$quantity,
                                'bkorder_qty_two'=>$quantityTwo,
								'bkorder_customer'=>$customerName,
								'bkorder_booking_id'=>$_product->getId(),
								'bkorder_order_id'=>$order->getId(),
								'bkorder_room_id'=>0,
								'bkorder_service_start'=>$serviceStart,
								'bkorder_service_end'=>$serviceEnd,
								'bkorder_total_days'=>$totalDays,
								'bkorder_total_hours'=>$totalHours,
								'bkorder_qt_item_id'=>$item->getItemId(),
								'bkorder_quantity_interval'=>$interQty,
								'bkorder_interval_time'=>$strInterTime,
							);

                            if( isset($requestOptions['intervals_hours_two']) && $requestOptions['intervals_hours_two'] != '' ){
                                $dataSave['bkorder_quantity_interval_two'] = $interQtyTwo;
                                $dataSave['bkorder_interval_time_two'] = $strInterTimeTwo;
                                $dataSave['bkorder_qty_two'] = $quantityTwo;
                            }
						}
						else
						{
							$checkIn = $this->_bkHelperDate->convertFormatDate($requestOptions['room_check_in']);
							$checkOut =  $this->_bkHelperDate->convertFormatDate($requestOptions['room_check_out']);
							$totalDays = (strtotime($checkOut) - strtotime($checkIn)) / (24 * 60 * 60);
							$dataSave = array(
								'bkorder_check_in'=>$checkIn,
								'bkorder_check_out'=>$checkOut,
								'bkorder_qty'=>$quantity,
								'bkorder_customer'=>$customerName,
								'bkorder_booking_id'=>$requestOptions['room_id'],
								'bkorder_order_id'=>$order->getId(),
								'bkorder_room_id'=>1,
								'bkorder_service_start'=>$serviceStart,
								'bkorder_service_end'=>$serviceEnd,
								'bkorder_total_days'=>$totalDays,
								'bkorder_total_hours'=>$totalHours,
								'bkorder_qt_item_id'=>$item->getItemId(),
								'bkorder_quantity_interval'=>$interQty,
								'bkorder_interval_time'=>$strInterTime,
							);
						}
						//convet quote item to order item
						/* $quoteItemOptionModel = $this->_quoteItemOptionFactory->create();
						$quoteItemOptionCollection = $quoteItemOptionModel->getCollection()
								->addFieldToFilter('item_id',$item->getQuoteItemId())
								->addFieldToFilter('code','additional_options');
						$quoteItemOption = $quoteItemOptionCollection->getFirstItem();
						if($quoteItemOption->getId())
						{
							$additionalOptions['info_buyRequest'] = $requestOptions;
							$additionalOptions['additional_options'] = unserialize($quoteItemOption->getValue());
							$item->setProductOptions($additionalOptions);
							$item->save();
						} */
						//additional_options
						if(isset($requestOptions['is_bk_back_end']) && $requestOptions['is_bk_back_end'] == 1)
						{
							$requestOptions['backend_item_id'] = $item->getQuoteItemId();
						}
						//$aradditionalOptions = $this->_bkCustomOptions->createExtractOptions($_product,$requestOptions);
						//delete old order 
						$isDeleteOldOrder = false;
						$oldBkOrderItem = 0;
						if(isset($requestOptions['is_bk_back_end']) && (isset($requestOptions['bk_order_item_id']) && $requestOptions['bk_order_item_id'] != $item->getItemId()))
						{
							$oldBkOrderItem = $requestOptions['bk_order_item_id'];
							$isDeleteOldOrder = true;
						}
						$requestOptions['bk_order_item_id'] = $item->getItemId();
						//$additionalOptions['info_buyRequest'] = $requestOptions;
						//$additionalOptions['additional_options'] = $aradditionalOptions['bk_options'];
						//$item->setProductOptions($additionalOptions);
						//$item->save();
						if(count($dataSave))
						{
							$bkOrderModel = $this->_bookingorders->create();
							$bkOrderModel->setData($dataSave)->save();
							//delete old if edit item if backend
							if(isset($isDeleteOldOrder))
							{
								$tempCollection = $bkOrderModel->getCollection()
										->addFieldToFilter('bkorder_qt_item_id',$oldBkOrderItem);
								if(count($tempCollection))
								{
									$tempOldBOrder = $tempCollection->getFirstItem();
									if($tempOldBOrder && $tempOldBOrder->getId())
									{
										$bkOrderModel->setId($tempOldBOrder->getId())->delete();
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
}
