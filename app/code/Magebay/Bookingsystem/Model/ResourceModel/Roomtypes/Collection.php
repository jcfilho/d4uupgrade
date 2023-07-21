<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel\Roomtypes;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Magebay\Bookingsystem\Model\Roomtypes',
            'Magebay\Bookingsystem\Model\ResourceModel\Roomtypes'
        );
    }
}