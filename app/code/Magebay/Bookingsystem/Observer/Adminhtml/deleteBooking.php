<?php

namespace Magebay\Bookingsystem\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magebay\Bookingsystem\Model\BookingsFactory;
use Magebay\Bookingsystem\Model\FacilitiesFactory;
use Magebay\Bookingsystem\Model\OptionsFactory;
use Magebay\Bookingsystem\Model\DiscountsFactory;
use Magebay\Bookingsystem\Model\CalendarsFactory;
use Magebay\Bookingsystem\Model\IntervalhoursFactory;
use Magebay\Bookingsystem\Model\BookingimagesFactory;
use Magebay\Bookingsystem\Model\RoomsFactory;
use Magebay\Bookingsystem\Model\BookingordersFactory;

class deleteBooking implements ObserverInterface
{
	protected $_bookingFactory;
	protected $_facilitiesFactory;
	protected $_optionsFactory;
	protected $_discountsFactory;
	protected $_calendarsFactory;
	protected $_intervalhoursFactory;
	protected $_bookingimagesFactory;
	protected $_roomsFactory;
	protected $_bookingordersFactory;
	
	public function __construct(
				BookingsFactory $bookingsFactory,
				FacilitiesFactory $facilitiesFactory,
				OptionsFactory $optionsFactory,
				DiscountsFactory $discountsFactory,
				CalendarsFactory $calendarsFactory,
				IntervalhoursFactory $intervalhoursFactory,
				BookingimagesFactory $bookingimagesFactory,
				RoomsFactory $roomsFactory,
				BookingordersFactory $bookingordersFactory
			)
    {
        $this->_bookingFactory = $bookingsFactory;
        $this->_facilitiesFactory = $facilitiesFactory;
        $this->_optionsFactory = $optionsFactory;
        $this->_discountsFactory = $discountsFactory;
        $this->_calendarsFactory = $calendarsFactory;
        $this->_intervalhoursFactory = $intervalhoursFactory;
        $this->_bookingimagesFactory = $bookingimagesFactory;
        $this->_roomsFactory = $roomsFactory;
        $this->_bookingordersFactory = $bookingordersFactory;
    }
    public function execute(EventObserver $observer)
    {
		
		$_product = $observer->getProduct();
		$productId = $_product->getId();
		$bookingModel = $this->_bookingFactory->create();
		$facilitiesModel = $this->_facilitiesFactory->create();
		$optionsModel = $this->_optionsFactory->create();
		$discountsModel = $this->_discountsFactory->create();
		$calendarsModel = $this->_calendarsFactory->create();
		$intervalsHoursModel = $this->_intervalhoursFactory->create();
		$bookingimagesModel = $this->_bookingimagesFactory->create();
		$roomsModel = $this->_roomsFactory->create();
		$bookingordersModel = $this->_bookingordersFactory->create();
		//get Booking Type
		$collection = $bookingModel->getCollection()
				->addFieldToFilter('booking_product_id',$productId);
		$booking = $collection->getFirstItem();
		if($booking)
		{
			$bookingType = $booking->getBookingType();
			//delete facilities
			$facilitiesModel->deleteBookingFromFacilities($productId,$bookingType);
			//delete options
			$optionsModel->deleteAddonOptions($productId,$bookingType);
			//delete discounts
			$discountsModel->deleteDiscounts($productId,$bookingType);
			//delete calendars 
			$calendarsModel->deleteCalendars($productId,$bookingType);
			if($bookingType == 'per_day')
			{
				$intervalsHoursModel->deleteIntervalsHours($productId);
				$bookingordersModel->deleteBkOrders($productId,0);
			}
			else
			{
				$rooms = $roomsModel->getCollection()
					->addFieldToFilter('room_booking_id',$productId);
				if(count($rooms))
				{
					foreach($rooms as $room)
					{
						$facilitiesModel->deleteBookingFromFacilities($room->getId(),'room');
						//delete options
						$optionsModel->deleteAddonOptions($room->getId(),'hotel');
						//delete discounts
						$discountsModel->deleteDiscounts($room->getId(),'hotel');
						//delete calendars
						$calendarsModel->deleteCalendars($room->getId(),'hotel');
						$bookingimagesModel->deleteBkImages($room->getId(),'room');
						$bookingordersModel->deleteBkOrders($room->getId(),1);
					}
				}
				$roomsModel->deleteBkRooms($productId);
				$facilitiesModel->deleteBookingFromFacilities($productId,'hotel');
			}
			$bookingModel->deleteBkBooking($productId);
		}
		
    }
}
