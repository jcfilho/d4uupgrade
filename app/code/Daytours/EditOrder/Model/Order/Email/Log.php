<?php

namespace Daytours\EditOrder\Model\Order\Email;

class Log extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Daytours\EditOrder\Model\Order\Email\ResourceModel\Log');
    }
}