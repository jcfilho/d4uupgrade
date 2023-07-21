<?php 
/**
* @package    Magebay_Marketplace
* @version    2.0
* @author     Magebay Developer Team <magebay99@gmail.com>
* @website    https://www.magebay.com/magento-multi-vendor-marketplace-extension
* @copyright  Copyright (c) 2009-2016 MAGEBAY.COM. (http://www.magebay.com)
*/
namespace Magebay\Marketplace\Model;
class Act extends \Magento\Framework\Model\AbstractModel {
    /**
     * Initialize resource model
     * @return void
     */
    public function _construct() {
        $this->_init('Magebay\Marketplace\Model\ResourceModel\Act');
    }
}