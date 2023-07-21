<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Rooms extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('booking_rooms', 'room_id');
    }
}