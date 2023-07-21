<?php

namespace Daytours\BookingLocked\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb as AbstractDbAlias;

class BookingLocked extends AbstractDbAlias
{

    protected function _construct()
    {
        $this->_init('booking_locked_date','entity_id');
    }

}