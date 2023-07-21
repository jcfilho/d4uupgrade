<?php

namespace Daytours\ErrorLogs\Model;

class ErrorLog extends \Magento\Framework\Model\AbstractModel
{
    public function _construct()
    {
        $this->_init("Daytours\ErrorLogs\Model\ResourceModel\ErrorLog");
    }
}
