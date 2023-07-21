<?php

namespace Daytours\BookingLocked\Model\ResourceModel\BookingLocked;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';


    protected function _construct()
    {
        $this->_init(\Daytours\BookingLocked\Model\BookingLocked::class, \Daytours\BookingLocked\Model\ResourceModel\BookingLocked::class);
    }

}