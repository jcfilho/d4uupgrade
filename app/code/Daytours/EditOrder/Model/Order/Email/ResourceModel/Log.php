<?php

namespace Daytours\EditOrder\Model\Order\Email\ResourceModel;

class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('editorder_email_log', 'entity_id');
    }
}