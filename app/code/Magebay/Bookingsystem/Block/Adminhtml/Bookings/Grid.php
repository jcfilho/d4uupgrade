<?php
 
namespace Magebay\Bookingsystem\Block\Adminhtml\Bookings;

use Magento\Framework\App\ResourceConnection;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;

class Grid extends WidgetGrid
{
	/**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		array $data = []
	) 
	{
		$this->_resource = $resource;
		parent::__construct($context, $backendHelper, $data);
	}
	protected function _prepareCollection()
	{
		$storeId = $this->_request->getParam('store',0);
		if ($this->getCollection()) {
			$collection = $this->getCollection();
			$collection->addAttributeToSelect('name');
			$collection->addAttributeToSelect('status');
			$collection->addAttributeToFilter('type_id','booking');
			if($storeId > 0)
			{
				$collection->addStoreFilter($storeId);
			}
			$tableBookingSystem = $this->_resource->getTableName('booking_systems');
			$collection->getSelect()->joinLeft(array('booking_system'=>$tableBookingSystem),'e.entity_id = booking_system.booking_product_id',array('booking_type'));
			$collection->getSelect()->where('booking_system.booking_id IS NOT NULL');
			$collection->getSelect()->where('booking_system.store_id=?',$storeId);
			//$this->setCollection($collection);
            $this->_preparePage();
            $columnId = $this->getParam($this->getVarNameSort(), $this->_defaultSort);
            $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);
            $filter = $this->getParam($this->getVarNameFilter(), null);
			
            if (is_null($filter)) {
                $filter = $this->_defaultFilter;
            }
			
            if (is_string($filter)) {
                $data = $this->_backendHelper->prepareFilterString($filter);
                $data = array_merge($data, (array)$this->getRequest()->getPost($this->getVarNameFilter()));
				if(isset($data['booking_type']))
				{
					$this->getCollection()->getSelect()->where("booking_system.booking_type = '{$data['booking_type']}'");
					unset($data['booking_type']);
				}
				if(isset($data['booking_location']))
				{
					if($data['booking_location'] != '')
					{
						$strSubWhere = "booking_system.booking_address LIKE '%{$data['booking_location']}%'";
						$strSubWhere .= " OR booking_system.booking_city LIKE '%{$data['booking_location']}%'";
						$strSubWhere .= " OR booking_system.booking_country LIKE '%{$data['booking_location']}%'";
						$this->getCollection()->getSelect()->where($strSubWhere);
					}
					unset($data['booking_location']);
				}
                $this->_setFilterValues($data);
            } elseif ($filter && is_array($filter)) {
                $this->_setFilterValues($filter);
            } elseif (0 !== sizeof($this->_defaultFilter)) {
                $this->_setFilterValues($this->_defaultFilter);
            }
            if ($this->getColumn($columnId) && $this->getColumn($columnId)->getIndex()) {
                $dir = strtolower($dir) == 'desc' ? 'desc' : 'asc';
                $this->getColumn($columnId)->setDir($dir);
                $this->_setCollectionOrder($this->getColumn($columnId));
            }
        }

        return $this;
	}
}