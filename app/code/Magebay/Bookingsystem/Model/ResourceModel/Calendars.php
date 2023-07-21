<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Calendars extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('booking_calendars', 'calendar_id');
    }
}