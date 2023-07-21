<?php
 
namespace Magebay\Bookingsystem\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Helper\BkHelperDate;
use Magebay\Bookingsystem\Helper\BkSimplePriceHelper;
use Magebay\Bookingsystem\Helper\IntervalsPrice;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\RoomtypesFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;
use Magebay\Bookingsystem\Model\Intervalhours;
use Magebay\Bookingsystem\Helper\BkText;
use Magebay\Bookingsystem\Helper\RoomPrice;

class BkCustomOptions extends AbstractHelper
{
	/**
     * @var PriceHelper
	 * */
	protected $_bkPiceHelper;
    /**
     * @var \Magebay\Bookingsystem\Model\Bookings
    */
	protected $_bookingFactory;
	/**
     * @var \Magebay\Bookingsystem\Helper\BkHelperDate
    */
	protected $_bkHelperDate;
	/** Helper
     *
     * @var \Magebay\Bookingsystem\Helper\BkSimplePriceHelper
    **/
	protected $_bkSimplePriceHelper;
	/** Helper
     *
     * @var \Magebay\Bookingsystem\Helper\IntervalsPrice
    **/
	protected $_intervalsPrice;
	/**
     * @var \Magebay\Bookingsystem\Model\OptionsFactory
    */
	protected $_optionsFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\RoomsFactory
    */
	protected $_roomsFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\RoomtypesFactory
    */
	protected $_roomtypesFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\OptionsdropdownFactory
    */
	protected $_optionsdropdownFactory;
	/**
     * @var \Magebay\Bookingsystem\Model\Intervalhours;
    */
	protected $_intervalsHours;
	/**
     * @var \Magebay\Bookingsystem\Helper\BkText
    */
	protected $_bkText;
	/**
     * @var \Magebay\Bookingsystem\Helper\RoomPrice
    */
	protected $_roomPrice;
	public function __construct(
       Context $context,
	   PriceHelper $priceHelper,
	   BookingsFactory $bookingFactory,
	   BkHelperDate $bkHelperDate,
	   BkSimplePriceHelper $bkSimplePriceHelper,
	   IntervalsPrice $intervalsPrice,
	   OptionsFactory $optionsFactory,
	   RoomsFactory $roomsFactory,
	   RoomtypesFactory $roomtypesFactory,
	   OptionsdropdownFactory $optionsdropdownFactory,
	   Intervalhours $intervalhours,
	   BkText $bkText,
	   RoomPrice $roomPrice
    ) 
	{
		parent::__construct($context);
		$this->_bkPiceHelper = $priceHelper;
		$this->_bookingFactory = $bookingFactory;
		$this->_bkHelperDate = $bkHelperDate;
		$this->_bkSimplePriceHelper = $bkSimplePriceHelper;
		$this->_intervalsPrice = $intervalsPrice;
		$this->_optionsFactory = $optionsFactory;
		$this->_roomsFactory = $roomsFactory;
		$this->_roomtypesFactory = $roomtypesFactory;
		$this->_optionsdropdownFactory = $optionsdropdownFactory;
		$this->_intervalsHours = $intervalhours;
		$this->_bkText = $bkText;
		$this->_roomPrice = $roomPrice;
    }
	function createExtractOptions($product,$params)
	{
		$additionalOptions = array();
		$enable = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/enable');
		$formatDate = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/format_date');
		$typeTime = $this->_bkHelperDate->getFieldSetting('bookingsystem/setting/time_mode');
		$storeId = $this->_bkText->getbkCurrentStore();
		$okAddCart = true;
		$qty = isset($params['qty']) ? $params['qty'] : 1;
		$oldOrderItemId = 0;
		if($enable == 1)
		{
			$bookingModel = $this->_bookingFactory->create();
			//check booking
			$booking = $bookingModel->getBooking($product->getId());
			$oldOrderItemId  = isset($params['bk_order_item_id']) ? $params['bk_order_item_id'] : 0;
			if($booking && $booking->getId() && $booking->getTypeId() == 'booking' && isset($params['check_in']))
			{
				$itemId = isset($params['backend_item_id']) ? $params['backend_item_id'] : 0;
				$bkTypeAddonPrice = __('Night');
				//simple
				if($booking->getBookingType() == 'per_day')
				{
	
					$arPrice = array();
					if($booking->getBookingTime() == 1 || $booking->getBookingTime() == 2 || $booking->getBookingTime() == 4 || $booking->getBookingTime() == 5)
					{
						//validate time
						if(!$this->_bkHelperDate->validateBkDate($params['check_in'],$formatDate) || !$this->_bkHelperDate->validateBkDate($params['check_out'],$formatDate))
						{
							$okAddCart = false;
						}
						else
						{
							$checkIn = $this->_bkHelperDate->convertFormatDate($params['check_in']);
							$checkOut = $this->_bkHelperDate->convertFormatDate($params['check_out']);
							if($booking->getBookingTime() == 1 || $booking->getBookingTime() == 4 || $booking->getBookingTime() == 5)
							{
							    if($booking->getBookingTime() == 5)
                                {
                                    $bkTypeAddonPrice = __('Person');
                                    $persons = isset($params['number_persons']) ? $params['number_persons'] : array();
                                    $arPrice = $this->_bkSimplePriceHelper->getBkTourPrice($booking,$checkIn,$checkOut,$qty,$itemId,array(),$persons,$oldOrderItemId);
                                    $totalTourPrice = $arPrice['total_promo'] > 0 ? $arPrice['total_promo'] : $arPrice['total_price'];
                                    $additionalOptions[] = array(
                                        'label' => __('Start Date'),
                                        'value' => date($formatDate,strtotime($arPrice['check_in'])),
                                    );
                                    $additionalOptions[] = array(
                                        'label' => __('End Data'),
                                        'value' =>  date($formatDate,strtotime($arPrice['check_out'])),
                                    );
                                    if($arPrice['person_price'] > 0)
                                    {
                                        $tourPrice = $totalTourPrice -  $arPrice['person_price'];
                                        $tourPrice = $this->_bkPiceHelper->currency($tourPrice,true,false);
                                        $additionalOptions[] = array(
                                            'label' => __('Tour Price'),
                                            'value' => $tourPrice,
                                        );
                                    }
                                    $extractPrices = isset($arPrice['extract_price']) ? $arPrice['extract_price'] : array();
                                    if(count($persons) && count($extractPrices))
                                    {
                                        foreach ($persons as $keyPer => $person)
                                        {
                                            if((int)$person <= 0)
                                            {
                                                continue;
                                            }
                                            if(array_key_exists($keyPer,$extractPrices))
                                            {
                                                $freePerson = $person > $extractPrices[$keyPer]['free'] ? $extractPrices[$keyPer]['free'] : $person;
                                                $txtPerson = $person . '( ' . __('Free ') . $freePerson .' )';;
                                                /*if($person > $extractPrices[$keyPer]['free'])
                                                {
                                                    $tempPersonPrice = ($person - $extractPrices[$keyPer]['free']) * $extractPrices[$keyPer]['price'];
                                                    $tempPersonPrice = $this->_bkPiceHelper->currency($tempPersonPrice,true,false);
                                                    $txtPerson .=  ', '.__('Price '). $tempPersonPrice .' )';
                                                }
                                                else
                                                {
                                                    $txtPerson .=  ' )';
                                                }*/
                                                $additionalOptions[] = array(
                                                    'label' => $extractPrices[$keyPer]['title'],
                                                    'value' => $txtPerson,
                                                );
                                            }
                                        }
                                    }
                                }
                                else
                                {
                                    if($booking->getBookingTime() == 4)
                                    {
                                        $bkTypeAddonPrice = __('Day');
                                    }
                                    $arPrice = $this->_bkSimplePriceHelper->getPriceBetweenDays($booking,$checkIn,$checkOut,$qty,$itemId,array(),$oldOrderItemId);
                                    $additionalOptions[] = array(
                                        'label' => __('Check In'),
                                        'value' => $params['check_in'],
                                    );
                                    $additionalOptions[] = array(
                                        'label' => __('Check Out'),
                                        'value' => $params['check_out'],
                                    );
                                    $additionalOptions[] = array(
                                        'label' => __('Total Days'),
                                        'value' => $arPrice['total_days'],
                                    );
                                }
								if($arPrice['str_error'] != '')
								{
									$okAddCart = false;
								}
							}
							elseif($booking->getBookingTime() == 2)
							{
                                $additionalOptions[] = array(
                                    'label' => __('Check In'),
                                    'value' => $params['check_in'],
                                );
                                $serviceStart = $this->_getRequest()->getParam('service_start','');
                                $serviceEnd = $this->_getRequest()->getParam('service_end','');
                                $arPrice = $this->_bkSimplePriceHelper->getPricePerTime($booking,$checkIn,$checkOut,$serviceStart,$serviceEnd,$qty,$itemId,array(),$oldOrderItemId);
                                $timeStart = isset($arPrice['service_start']) ? $arPrice['service_start'] : '';
                                $timeEnd = isset($arPrice['service_end']) ? $arPrice['service_end'] : '';
                                $intTimeStart = strtotime($timeStart);
                                $intTimeEnd = strtotime($timeEnd);
                                $textTimeStart = $this->_bkHelperDate->getTextTimeHours($intTimeStart);
                                $textTimeEnd = $this->_bkHelperDate->getTextTimeHours($intTimeEnd);
                                $additionalOptions[] = array(
                                    'label' => __('Service Start'),
                                    'value' => $textTimeStart,
                                );
                                $additionalOptions[] = array(
                                    'label' => __('Service End'),
                                    'value' => $textTimeEnd,
                                );
							}
						}
					}
					elseif($booking->getBookingTime() == 3)
					{
                        $bkTypeAddonPrice = __('Session');
						$intervalsHours = isset($params['intervals_hours']) ? $params['intervals_hours'] : array();
						if(!$this->_bkHelperDate->validateBkDate($params['check_in'],$formatDate) || !count(($intervalsHours)))
						{
							$okAddCart = false;
						}
						else
						{
							$checkIn = $this->_bkHelperDate->convertFormatDate($params['check_in']);
							$arPrice = $this->_intervalsPrice->getIntervalsHoursPrice($booking,$checkIn,$qty,$intervalsHours,$itemId,array(),$oldOrderItemId);
							if($arPrice['str_error'] != '')
							{
								$okAddCart = false;
							}
							else
							{
								$strIntervals = '';
								$i = 0;
								foreach($intervalsHours as $intervalId)
								{
								    $interval = $this->_intervalsHours->load($intervalId);
                                    $intervalsHour = $interval->getIntervalhoursBookingTime();
									$tempIntervals = '';
									$arIntervalsHours = explode('_',$intervalsHour);
									if($typeTime == 1)
                                    {
                                        $typeTime1 = __('AM');
                                        $typeTime2 = __('AM');
                                        if($arIntervalsHours[0] >= 12)
                                        {
                                            $arIntervalsHours[0] = $arIntervalsHours[0] > 12 ? $arIntervalsHours[0] - 12 : $arIntervalsHours[0];
                                            $typeTime1 = __('PM');
                                        }
                                        if($arIntervalsHours[2] >= 12)
                                        {
                                            $arIntervalsHours[2] = $arIntervalsHours[2] > 0 ? $arIntervalsHours[2] - 12 : $arIntervalsHours[2];
                                            $typeTime2 = __('PM');
                                        }
                                        $tempIntervals = $arIntervalsHours[0].':'.$arIntervalsHours[1].' '. $typeTime1;
                                    }
									else
                                    {
                                        $tempIntervals = $arIntervalsHours[0].':'.$arIntervalsHours[1];
                                    }
									if($booking->getBookingShowFinish() == 1)
									{
                                        if($typeTime == 1)
                                        {
                                            $tempIntervals .= ' - '.$arIntervalsHours[2].':'.$arIntervalsHours[3].' '. $typeTime2;
                                        }
                                        else
                                        {
                                            $tempIntervals .= ' - '.$arIntervalsHours[2].':'.$arIntervalsHours[3];
                                        }
									}
                                    if($interval->getIntervalhoursLabel() != '')
                                    {
                                        $tempIntervals .= ' '.$interval->getIntervalhoursLabel();
                                    }
									if($i == 0)
									{
										$strIntervals = $tempIntervals;
									}
									else
									{
										$strIntervals .= ', '.$tempIntervals;
									}
									$i++;
								}
								$additionalOptions[] = array(
									'label' => __('Check In'),
									'value' => $params['check_in'],
								);
								$additionalOptions[] = array(
									'label' => __('Intervals'),
									 'value' => $strIntervals,
									);
							}
							
						}
					}
				}
				//booking for hotel
				elseif($booking->getBookingType() == 'hotel')
				{
					$checkIn = $params['room_check_in'];
					$checkOut = $params['room_check_out'];
					$roomId = $params['room_id'];
					$roomModel = $this->_roomsFactory->create();
					$room = $roomModel->load($roomId);
					if($room)
					{
						if(!$this->_bkHelperDate->validateBkDate($checkIn,$formatDate) || !$this->_bkHelperDate->validateBkDate($checkOut,$formatDate))
						{
							$okAddCart = false;
						}
						//get title room type
						$roomTypeModel = $this->_roomtypesFactory->create();
						$roomType = $roomTypeModel->load($room->getRoomType());
						$roomTitle =  $this->_bkText->showTranslateText($roomType->getRoomtypeTitle(),$roomType->getRoomtypeTitleTransalte());
						$additionalOptions[] = array(
							'label' => __('Room Type'),
							'value' => $roomTitle,
						);
						$additionalOptions[] = array(
							'label' => __('Check In'),
							'value' => $checkIn,
						);
						$additionalOptions[] = array(
							'label' => __('Check Out'),
							'value' => $checkOut,
						);
						$checkIn = $this->_bkHelperDate->convertFormatDate($checkIn);
						$checkOut = $this->_bkHelperDate->convertFormatDate($checkOut);
						$arPrice = $this->_roomPrice->getPriceBetweenDays($room,$checkIn,$checkOut,$qty,$itemId,array(),$oldOrderItemId);
						if($arPrice['str_error'] != '')
						{
							$okAddCart = false;
						}
						$additionalOptions[] = array(
								'label' => __('Total Days'),
								'value' => $arPrice['total_days'],
						);
					}
				}
				if($okAddCart)
				{
					if(isset($params['addons']))
					{
						if(count($params['addons']))
						{
							$addonKeyExit = array();
							foreach($params['addons'] as $key => $addon)
							{
							    $typePriceAddon = '';
								$addonsModel = $this->_optionsFactory->create();
								$addonsOptions = $addonsModel->load($key);
								if($addonsOptions->getOptionPriceType() == 1)
                                {
                                    $typePriceAddon = '/' .$bkTypeAddonPrice;
                                }
								if($addonsOptions->getId())
								{
									$titleAddons = $this->_bkText->showTranslateText($addonsOptions->getOptionTitle(),$addonsOptions->getOptionTitleTranslate());
									if($addonsOptions->getOptionType() == 1)
									{
										$additionalOptions[] = array(
											'label' => $titleAddons,
											'value' => $addon
										);
									}
									elseif($addonsOptions->getOptionType() == 2 || $addonsOptions->getOptionType() == 4)
									{
										$optionSelectModel = $this->_optionsdropdownFactory->create();
										$valueRows = $optionSelectModel->getBkValueOptions($key);
										$strTitleRow = '';
										if(count($valueRows))
										{
											foreach($valueRows as $valueRow)
											{
												if($valueRow->getId() == $addon)
												{
												    $tempTitleRow = $this->_bkText->showTranslateText($valueRow->getDropdownTitle(),$valueRow->getDropdownTitleTranslate());
												    /*if($valueRow->getDropdownPrice())
                                                    {
                                                        $tempTitleRow .= '( '. $this->_bkPiceHelper->currency($valueRow->getDropdownPrice(),true,false) . $typePriceAddon . ' ) ';
                                                        $strTitleRow = $tempTitleRow;
                                                    }*/
                                                    $strTitleRow = $tempTitleRow;
													$additionalOptions[] = array(
														'label' => $titleAddons,
														'value' => $strTitleRow
														);
													break;
												}
											}
										}
									}
									elseif($addonsOptions->getOptionType() == 3 || $addonsOptions->getOptionType() == 5)
									{
										$optionSelectModel = $this->_optionsdropdownFactory->create();
										$valueRows = $optionSelectModel->getBkValueOptions($key);
										$strTitleRow = '';
										if(count($valueRows))
										{
											foreach($valueRows as $valueRow)
											{
												
												if(count($addon))
												{
													foreach($addon as $mAddon)
													{
														if($mAddon == $valueRow->getId())
														{
                                                            $tempTitleRow = $this->_bkText->showTranslateText($valueRow->getDropdownTitle(),$valueRow->getDropdownTitleTranslate());
                                                            /*if($valueRow->getDropdownPrice() > 0)
                                                            {
                                                                $tempTitleRow .= '( '. $this->_bkPiceHelper->currency($valueRow->getDropdownPrice(),true,false) . $typePriceAddon . ' ) ';
                                                            }*/
															if($strTitleRow == '')
															{
															    $strTitleRow .= $tempTitleRow;
															}
															else
															{
																$strTitleRow .= $tempTitleRow;
															}
														}
													}
													
												}
											}
											if($strTitleRow != '')
											{
												$additionalOptions[] = array(
												'label' => $titleAddons,
												'value' => $strTitleRow
												);
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
		return array(
			'status'=>$okAddCart,
			'bk_options'=>$additionalOptions
		);	
	}
	function addBkOptions($product,$params)
	{
		$bkData = $this->createExtractOptions($product,$params);
		if($bkData['status'] == true)
		{
			$additionalOptions = $bkData['bk_options'];
			$product->addCustomOption('additional_options', serialize($additionalOptions));
		}
		else
		{
			throw new \Exception(__('Dates are not available. Please check again!'));
		} 
	}
}