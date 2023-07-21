<?php
/**
 * @Author      : Kien
 * @package     Marketplace
 * @copyright   Copyright (c) 2016 MAGEBAY (http://www.magebay.com)
 * @terms  http://www.magebay.com/terms
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magebay\Marketplace\Model\ResourceModel;
class Sellers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get tablename from config
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('multivendor_user', 'id');
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
		/* daivid : add releated products */	
		//$oldIds = $this->lookupStoreIds($object->getId());
		$oldIds = [];
		$linksData = $object->getData('relatedproducts_links');
        if (is_array($linksData)) {
            $oldIds = $this->lookupRelatedProductIds($object->getData('user_id'));
            $this->_updateLinks($object, array_keys($linksData), $oldIds, 'multivendor_product', 'product_id', $linksData);
        } 
		/* daivid */
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
	/* david extra function */
	protected function _updateLinks(
        \Magento\Framework\Model\AbstractModel $object,
        Array $newRelatedIds,
        Array $oldRelatedIds,
        $tableName,
        $field,
        $rowData = []
    ) {
        $table = $this->getTable($tableName);
        $insert = $newRelatedIds;
        $delete = $oldRelatedIds;
		
		$insert1 = array_diff($insert,$delete);
		$delete1 = array_diff($delete,$insert);
        if ($delete1) {
            $where = ['user_id = ?' => (int)$object->getData('user_id'), $field.' IN (?)' => $delete1];
            $this->getConnection()->delete($table, $where);
        } 
        if ($insert1) {
            $data = [];
            foreach ($insert1 as $id) {
                $id = (int)$id;
                $data[] = array_merge(['user_id' => (int)$object->getData('user_id'), $field => $id],
                    (isset($rowData[$id]) && is_array($rowData[$id])) ? $rowData[$id] : []
                );
            }
            $this->getConnection()->insertMultiple($table, $data);
        }
    }
	protected function _lookupIds($user_id, $tableName, $field)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()->from(
            $this->getTable($tableName),
            $field
        )->where(
            'user_id = ?',
            (int)$user_id
        );
        return $adapter->fetchCol($select);
    }
	public function lookupStoreIds($user_id)
    {
        return $this->_lookupIds($user_id, 'multivendor_product', 'store_id');
    }
	public function lookupRelatedProductIds($user_id)
    {
        return $this->_lookupIds($user_id, 'multivendor_product', 'product_id');
    } 
}