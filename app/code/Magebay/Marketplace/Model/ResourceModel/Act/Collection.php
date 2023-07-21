<?php
/**
* @package    Magebay_Marketplace
* @version    2.0
* @author     Magebay Developer Team <magebay99@gmail.com>
* @website    https://www.magebay.com/magento-multi-vendor-marketplace-extension
* @copyright  Copyright (c) 2009-2016 MAGEBAY.COM. (http://www.magebay.com)
*/
namespace Magebay\Marketplace\Model\ResourceModel\Act;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebay\Marketplace\Model\Act', 'Magebay\Marketplace\Model\ResourceModel\Act');
    }
}