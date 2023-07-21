<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel\Bookings;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Magebay\Bookingsystem\Model\Bookings',
            'Magebay\Bookingsystem\Model\ResourceModel\Bookings'
        );
    }
}