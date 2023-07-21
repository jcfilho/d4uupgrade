<?php
/**
 * Author: Jose Carlos Filho
 * Date:   2020-02-15 07:25
 * Project: daytours4u
 * email: josecarlos.filhov@gmail.com
 **/


namespace Daytours\Bookingsystem\Plugin;

use Daytours\BookingLocked\Api\BookingLockedRepositoryInterface;
use Daytours\BookingLocked\Api\Data;

class BkSimplePriceHelperDaytours
{
    /**
     * @var BookingLockedRepositoryInterface
     */
    private $bookingLockedRepository;

    /**
     * BkSimplePriceHelper constructor.
     * @param BookingLockedRepositoryInterface $bookingLockedRepository
     */
    public function __construct(
        BookingLockedRepositoryInterface $bookingLockedRepository
    )
    {

        $this->bookingLockedRepository = $bookingLockedRepository;
    }


    public function afterGetPriceBetweenDaysTwoCalendars(
        \Magebay\Bookingsystem\Helper\BkSimplePriceHelper $subject,
        $result,
        $booking,
        $checkIn,
        $checkOut,
        $checkInTwo,
        $checkOutTwo,
        $qty = 1,
        $itemId = 0,
        $paramAddons = array(),
        $oldOrderItemId = 0,
        $isRoundTrip = null
    ){

        $calendarNumber = Data\BookingLockedInterface::CALENDAR_TWO;
        $lockedDate = $this->bookingLockedRepository->lockedDateExist($booking->getId(),$checkIn,$calendarNumber);
        if( !$lockedDate ){
            $result['str_error'] = __('Date are not available. Please check again');;
        }
        return $result;
    }
}