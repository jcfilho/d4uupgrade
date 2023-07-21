<?php
 
namespace Magebay\Bookingsystem\Model\ResourceModel\Optionsdropdown;
 
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Magebay\Bookingsystem\Model\Optionsdropdown',
            'Magebay\Bookingsystem\Model\ResourceModel\Optionsdropdown'
        );
    }
}