<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Bookingimages extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('booking_bookingimages', 'bkimage_id');
    }
}