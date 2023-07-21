<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel;
 
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
class Optionsdropdown extends AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('booking_option_dropdown', 'dropdown_id');
    }
}