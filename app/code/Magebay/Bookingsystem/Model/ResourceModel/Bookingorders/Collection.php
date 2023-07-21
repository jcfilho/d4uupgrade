<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel\Bookingorders;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Magebay\Bookingsystem\Model\Bookingorders',
            'Magebay\Bookingsystem\Model\ResourceModel\Bookingorders'
        );
    }
}