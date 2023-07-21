<?php

namespace Daytours\EditOrder\Model\Order\Email\ResourceModel\Log;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Daytours\EditOrder\Model\Order\Email\Log',
            'Daytours\EditOrder\Model\Order\Email\ResourceModel\log'
        );
    }
}