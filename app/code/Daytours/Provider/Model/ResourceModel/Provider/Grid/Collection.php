<?php

namespace Daytours\Provider\Model\ResourceModel\Provider\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';


    protected function _construct()
    {
        $this->_init(\Daytours\Provider\Model\Provider::class, \Daytours\Provider\Model\ResourceModel\Provider::class);
    }

}