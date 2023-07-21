<?php
/**
* @package    Magebay_Bookingsystem
* @version    2.0
* @author     Magebay Developer Team <magebay99@gmail.com>
* @website    http://www.productsdesignercanvas.com
* @copyright  Copyright (c) 2009-2016 MAGEBAY.COM. (http://www.magebay.com)
*/
namespace Magebay\Bookingsystem\Model\ResourceModel;
class Act extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('booking_act', 'act_id');
    }
}