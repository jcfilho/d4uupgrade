<?php

namespace Onetree\ImportDataFixtures\Model\Block\ResourceModel;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;

/**
 * Class Block
 * @package Onetree\ImportDataFixtures\Model\Block\ResourceModel
 */
class Block extends \Magento\Cms\Model\ResourceModel\Block
{
    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Magento\Cms\Model\Block|AbstractModel $object
     * @return Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $entityMetadata = $this->metadataPool->getMetadata(BlockInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = \Magento\Framework\Model\ResourceModel\Db\AbstractDb::_getLoadSelect($field, $value, $object);

        if ($object->getStoreId()) {
            $stores = $object->getStoreId();
            if (is_array($stores)) {
                $stores = array_unique($stores);
            } else {
                $stores = [(int)$object->getStoreId()];
            }

            $select->join(
                ['cbs' => $this->getTable('cms_block_store')],
                $this->getMainTable() . '.' . $linkField . ' = cbs.' . $linkField,
                ['store_id']
            )
                ->where('is_active = ?', 1)
                ->where('cbs.store_id in (?)', $stores)
                ->order('store_id DESC')
                ->limit(1);
        }

        return $select;
    }
}
