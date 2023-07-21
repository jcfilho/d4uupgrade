<?php

namespace Daytours\ErrorLogs\Model\ResourceModel;

class ErrorLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init("error_logs", "id");
    }
}
