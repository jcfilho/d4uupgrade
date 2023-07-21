<?php
 
namespace Magebay\Bookingsystem\Model\System\Config;
 
use Magento\Framework\Option\ArrayInterface;
 
class BookingType implements ArrayInterface
{
    const RENT  = 'per_day';
    const HOTEL = 'hotel';
    const ROOM = 'room';
 
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            self::RENT => __('Rent'),
            self::HOTEL => __('Reservation'),
            self::ROOM => __('Room (Reservation)')
        ];
        return $options;
    }
}