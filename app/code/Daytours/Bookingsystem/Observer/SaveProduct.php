<?php

namespace Daytours\Bookingsystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magebay\Bookingsystem\Model\CalendarsFactory;

class SaveProduct implements ObserverInterface
{
	private $calendarsFactory;

	public function __construct(
        CalendarsFactory $calendarsFactory
	) {
        $this->calendarsFactory = $calendarsFactory;
	}

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		/**
		 * Set calendar prices as product price
		 */
		$product = $observer->getProduct();
		$calendarBookingId = $product->getId();
		$model = $this->calendarsFactory->create();
        $collection = $model->getBkCalendars();
        $calendarCollection = $collection->addFieldToFilter('calendar_booking_id',$calendarBookingId);
		foreach($calendarCollection as $calendar){
            $calendar->setData("calendar_price",$product->getPrice());
            $calendar->setData("calendar_promo",$product->getSpecialPrice());
            $calendar->save();
        }
	}
}
