<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel\Bookingimages;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Magebay\Bookingsystem\Model\Bookingimages',
            'Magebay\Bookingsystem\Model\ResourceModel\Bookingimages'
        );
    }
}