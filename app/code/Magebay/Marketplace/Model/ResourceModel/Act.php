<?php
/**
* @package    Magebay_Marketplace
* @version    2.0
* @author     Magebay Developer Team <magebay99@gmail.com>
* @website    https://www.magebay.com/magento-multi-vendor-marketplace-extension
* @copyright  Copyright (c) 2009-2016 MAGEBAY.COM. (http://www.magebay.com)
*/
namespace Magebay\Marketplace\Model\ResourceModel;

class Act extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('magebay_act', 'act_id');
    }
}