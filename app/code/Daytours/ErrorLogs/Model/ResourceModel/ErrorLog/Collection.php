<?php

namespace Daytours\ErrorLogs\Model\ResourceModel\ErrorLog;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            "Daytours\ErrorLogs\Model\ErrorLog", 
            "Daytours\ErrorLogs\Model\ResourceModel\ErrorLog"
        );
    }
}
