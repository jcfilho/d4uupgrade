<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Model\ResourceModel;
class Reviews extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('multivendor_reviews', 'id');
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_beforeDelete($object);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_beforeSave($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_afterSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        return parent::_afterLoad($object);
    }
}