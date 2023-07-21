<?php

namespace Magebay\Bookingsystem\Model\Product\Copier;

use Magento\Framework\App\RequestInterface;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magebay\Bookingsystem\Model\BookingimagesFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\OptionsdropdownFactory;

class Plugin
{
	protected $_request;
	protected $_bookingFactory;
	protected $_facilitiesFactory;
	protected $_optionsFactory;
	protected $_discountsFactory;
	protected $_calendarsFactory;
	protected $_intervalhoursFactory;
	protected $_bookingimagesFactory;
	protected $_roomsFactory;
	protected $_optionsdropdownFactory;
	function __construct
	(
		RequestInterface $_request,
		BookingsFactory $bookingFactory,
		FacilitiesFactory $facilitiesFactory,
		OptionsFactory $optionsFactory,
		DiscountsFactory $discountsFactory,
		CalendarsFactory $calendarsFactory,
		IntervalhoursFactory $intervalhoursFactory,
		BookingimagesFactory $bookingimagesFactory,
		RoomsFactory $roomsFactory,
		OptionsdropdownFactory $optionsdropdownFactory
	)
	{
		$this->_request = $_request;
		$this->_bookingFactory = $bookingFactory;
		$this->_facilitiesFactory = $facilitiesFactory;
        $this->_optionsFactory = $optionsFactory;
        $this->_discountsFactory = $discountsFactory;
        $this->_calendarsFactory = $calendarsFactory;
        $this->_intervalhoursFactory = $intervalhoursFactory;
        $this->_bookingimagesFactory = $bookingimagesFactory;
        $this->_roomsFactory = $roomsFactory;
        $this->_optionsdropdownFactory = $optionsdropdownFactory;
		
	}
    public function afterCopy($subject, $result)
    {
		
		$params = $this->getBkRequest()->getPost();
		if($result->getTypeId() == 'booking')
		{
			$newBookingId = $result->getId();
			$bookingId = isset($params['product']['current_product_id']) ? (int)$params['product']['current_product_id'] : 0;
			if($bookingId > 0)
			{
				$bookingModel = $this->_bookingFactory->create();
				$collection = $bookingModel->getCollection()
						->addFieldToFilter('booking_product_id',$bookingId);
				$booking = $collection->getFirstItem();
				if($booking && $booking->getId())
				{
					$bkData = $booking->getData();
					$bkData['booking_product_id'] = $newBookingId;
					unset($bkData['booking_id']);
					$bookingModel->setData($bkData)->save();
					$facilitiesModel = $this->_facilitiesFactory->create();
					$optionsModel = $this->_optionsFactory->create();
					$discountsModel = $this->_discountsFactory->create();
					$calendarsModel = $this->_calendarsFactory->create();
					$intervalsHoursModel = $this->_intervalhoursFactory->create();
					$optionValuesModel = $this->_optionsdropdownFactory->create();
					if($bkData['booking_type'] == 'hotel')
					{
						
						//save table rooms
						$roomModel = $this->_roomsFactory->create();
						$rooms = $roomModel->getCollection()
								->addFieldToFilter('room_booking_id',$bookingId);
						$bookingimagesModel = $this->_bookingimagesFactory->create();
						if(count($rooms))
						{
							foreach($rooms as $room)
							{
								$tempRoomData = $room->getData();
								$tempRoomData['room_booking_id'] = $newBookingId;
								unset($tempRoomData['room_id']);
								$newRoomObject = $roomModel->setData($tempRoomData)->save();
								$newRoomId = $newRoomObject->getId();
								//save price
								//get price from current product
								$currentPrices = $calendarsModel->getCollection()
										->addFieldToFilter('calendar_booking_id',$room->getId())
										->addFieldToFilter('calendar_booking_type','hotel');
								if(count($currentPrices))
								{
									foreach($currentPrices as $currentPrice)
									{
										$priceData = $currentPrice->getData();
										$priceData['calendar_booking_id'] = $newRoomId;
										unset($priceData['calendar_id']);
										$calendarsModel->setData($priceData)->save();
									}
								}
								//save room facilities
								$dataRoomFacilities = array();
								$currentFacilities = $facilitiesModel->getBkFacilitiesById($room->getId(),array('facility_id'),array('facility_booking_type'=>'room'));
								if(count($currentFacilities))
								{
									$dataRoomFacilities = array();
									foreach($currentFacilities as $currentFacility)
									{
										$dataRoomFacilities[] = $currentFacility->getId();
									}
								}
								$facilitiesModel->saveBkFacilities($dataRoomFacilities,$newRoomId,'room');
								//save new discounts
								//get old discount
								$currentDiscounts = $discountsModel->getBkDiscounts($room->getId(),'hotel');
								if(count($currentDiscounts))
								{
									foreach($currentDiscounts as $currentDiscount)
									{
										$arDiscountData = $currentDiscount->getData();
										unset($arDiscountData['discount_id']);
										$arDiscountData['discount_booking_id'] = $newRoomId;
										$discountsModel->setData($arDiscountData)->save();
									}
								}
								//current options 
								$currentOptions = $optionsModel->getBkOptions($room->getId(),'hotel');
								if(count($currentOptions))
								{
									foreach($currentOptions as $currentOption)
									{
										$optionId = $currentOption->getId();
										$arDataOption = $currentOption->getData();
										unset($arDataOption['option_id']);
										$arDataOption['option_booking_id'] = $newRoomId;
										$optionsModel->setData($arDataOption)->save();
										if($currentOption->getOptionType() == 2)
										{
											$newOptionId = $optionsModel->getId();
											$optionValues = $optionValuesModel->getCollection()
													->addFieldToFilter('dropdown_option_id',$optionId);
											if(count($optionValues))
											{
												foreach($optionValues as $optionValue)
												{
													$aroptionValueData = $optionValue->getData();
													unset($aroptionValueData['dropdown_id']);
													$aroptionValueData['dropdown_option_id'] = $newOptionId;
													$optionValuesModel->setData($aroptionValueData)->save();
												}
											}
										}
									}
								}
								// images
								$roomImages = $bookingimagesModel->getCollection()
										->addFieldToFilter('bkimage_data_id',$room->getId())
										->addFieldToFilter('bkimage_type','room');
								if(count($roomImages))
								{
									foreach($roomImages as $roomImage)
									{
										$arDataImage = $roomImage->getData();
										unset($arDataImage['bkimage_id']);
										$arDataImage['bkimage_data_id'] = $newRoomId;
										$bookingimagesModel->setData($arDataImage)->save();
									}
								}
							}
						}
						//save hotel facilities
						$currentHotelFacilities = $facilitiesModel->getBkFacilitiesById($bookingId,array('facility_id'),array('facility_booking_type'=>'hotel'));
						if(count($currentHotelFacilities))
						{
							$dataHotelFacilities = array();
							foreach($currentHotelFacilities as $currentHotelFacility)
							{
								$dataHotelFacilities[] = $currentHotelFacility->getId();
							}
							$facilitiesModel->saveBkFacilities($dataHotelFacilities,$newBookingId,'hotel');
						}
					}
					else
					{
						$currentPrices = $calendarsModel->getCollection()
										->addFieldToFilter('calendar_booking_id',$bookingId)
										->addFieldToFilter('calendar_booking_type','per_day');
						if(count($currentPrices))
						{
							foreach($currentPrices as $currentPrice)
							{
								$priceData = $currentPrice->getData();
								$priceData['calendar_booking_id'] = $newBookingId;
								unset($priceData['calendar_id']);
								$calendarsModel->setData($priceData)->save();
							}
						}
						//save rent discount
						//get old discount
						$currentDiscounts = $discountsModel->getBkDiscounts($bookingId,'per_day');
						if(count($currentDiscounts))
						{
							foreach($currentDiscounts as $currentDiscount)
							{
								$arDiscountData = $currentDiscount->getData();
								unset($arDiscountData['discount_id']);
								$arDiscountData['discount_booking_id'] = $newBookingId;
								$discountsModel->setData($arDiscountData)->save();
							}
						}
						//save options
						$currentOptions = $optionsModel->getBkOptions($bookingId,'per_day');
						if(count($currentOptions))
						{
							foreach($currentOptions as $currentOption)
							{
								$optionId = $currentOption->getId();
								$arDataOption = $currentOption->getData();
								unset($arDataOption['option_id']);
								$arDataOption['option_booking_id'] = $newBookingId;
								$optionsModel->setData($arDataOption)->save();
								if($currentOption->getOptionType() == 2)
								{
									$newOptionId = $optionsModel->getId();
									$optionValues = $optionValuesModel->getCollection()
											->addFieldToFilter('dropdown_option_id',$optionId);
									if(count($optionValues))
									{
										foreach($optionValues as $optionValue)
										{
											$aroptionValueData = $optionValue->getData();
											unset($aroptionValueData['dropdown_id']);
											$aroptionValueData['dropdown_option_id'] = $newOptionId;
											$optionValuesModel->setData($aroptionValueData)->save();
										}
									}
								}
							}
						}
						//save intervals hours
						if($bkData['booking_time'] == 3)
						{
							//get íntervals
							$intervalhours = $intervalsHoursModel->getCollection()
								->addFieldToFilter('intervalhours_booking_id',$bookingId);
							if(count($intervalhours))
							{
								foreach($intervalhours as $intervalhour)
								{
									$arDataInterval = $intervalhour->getData();
									$arDataInterval['intervalhours_booking_id'] = $newBookingId;
									unset($arDataInterval['intervalhours_id']);
									if($arDataInterval['intervalhours_check_in'] == '0000-00-00')
									{
										$arDataInterval['intervalhours_check_in'] = NULL;
									}
									if($arDataInterval['intervalhours_check_out'] == '0000-00-00')
									{
										$arDataInterval['intervalhours_check_out'] = NULL;
									}
									$intervalsHoursModel->setData($arDataInterval)->save();
								}
							}
						}
						$dataFacilities = array();
						$currentFacilities = $facilitiesModel->getBkFacilitiesById($bookingId,array('facility_id'),array('facility_booking_type'=>'per_day'));
						if(count($currentFacilities))
						{
							$dataFacilities = array();
							foreach($currentFacilities as $currentFacility)
							{
								$dataFacilities[] = $currentFacility->getId();
							}
							$facilitiesModel->saveBkFacilities($dataFacilities,$newBookingId,'per_day');
						}
					}
					
				}
			}
		}
		return $result; 
    }    
	function getBkRequest()
	{
		return $this->_request;
	}
}